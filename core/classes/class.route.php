<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class route extends coreObj{

    public  $route  = '',
            $routes = array();

    /**
     * Constructor
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @todo    Remove this->route, as it's not a route, it's the current URL... :/
     *
     * @return  void
     */
    public function __construct() {
        $this->route = $_GET;
    }

    /**
     * Processes the action of a URL based on cached routes
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $url    string  URL to process against the cached routes
     *
     * @return      bool
     */
    function processURL( $url ) {
        $objPlugin  = coreObj::getPlugins();
        $objCache   = coreObj::getCache();
        (cmsDEBUG ? memoryUsage('Routes: Began Processing URL') : '');

        // Run A hook
        $objPlugin->hook('CMS_ROUTE_START');

        // Append a forward slash to the incoming url if there isn't one
        // TODO: (Should be solved elsewhere)
        if( strpos( $url, '/' ) !== 0 ) {
            $url = '/' . $url;
        }

        // Grab the Routes from the cache
        (cmsDEBUG ? memoryUsage('Routes: Grabbing Cache') : '');
        $routes = $objCache->load('routes');

        // If we have no routes to use, then we need to stop here
        if(!count($routes)){
            (cmsDEBUG ? memoryUsage('Routes: Could not load Cache... :(') : '');
            $this->throwHTTP(500);
            return false;
        }

        // Loop through our routes and find the most appropriate match
        (cmsDEBUG ? memoryUsage('Routes: Finding Appropriate Route') : '');
        foreach( $routes as $label => $route ) {

            (cmsDEBUG ? memoryUsage('Routes: Testing .. '.$route['pattern']) : '');

            // Check for a method being set, if it doesn't match, continue
            if( $route['method'] != 'any' && $route['method'] != $_SERVER['REQUEST_METHOD']) {
                (cmsDEBUG ? memoryUsage('Routes: Dismissing pattern due to incorrect REQUEST_METHOD') : '');
                continue;
            }

            // Match Absolute URLs
            if( $route['pattern'] === $url ) {
                (cmsDEBUG ? memoryUsage('Routes: Absolute URL Matched') : '');
                $this->invoke($route);
                return true;
            }

            // Filter out empty values, and essentially reset the array keys
            $parts_u = array_values( array_filter( explode( '/', $url ) ) );
            $parts_p = array_values( array_filter( explode( '/', $route['pattern'] ) ) );

            // If the route and parts aren't of equal length, insta-dismiss this route
            if( count( $parts_u ) !== count( $parts_p ) ) {
                (cmsDEBUG ? memoryUsage('Routes: Dismissing due to incorrect parts counts') : '');
                continue;
            }

            // Store the route's pattern for replacing later on
            $ourRoute = $route['pattern'];

            // Collect all the replacement 'variables' from the route structure into an array
            (cmsDEBUG ? memoryUsage('Routes: Gathering variables from pattern') : '');
            $replacements = preg_match_all( '/\:([A-Za-z0-9]+)/', $route['pattern'], $matches );
            $replacements = ( !empty( $matches[1] ) ? $matches[1] : array() );

            // Loop through our replacements (if there are any),
            //  In the matching, if there is a requirement set, use that,
            //  else, use our generic alpha-numeric string match that includes SEO friendly chars.
            (cmsDEBUG ? memoryUsage('Routes: Replace variables with Replacements') : '');
            foreach( $replacements as $replacement ) {
                $replaceWith = '[A-Za-z0-9\-\_]+';

                if( !empty( $route['requirements'][$replacement] ) ) {
                    $replaceWith = $route['requirements'][$replacement];
                }

                $ourRoute = str_replace( ':' . $replacement, '(' . $replaceWith . ')', $ourRoute );
            }

            // If the route matches the URL, we've got a winner!
            (cmsDEBUG ? memoryUsage('Routes: Test Pattern') : '');
            if( preg_match( '#' . $ourRoute . '#', $url, $matches ) ) {

                // Remove the URL from the paramaters
                unset( $matches[0] );
                $matches = array_values( $matches );
                $params  = array();

                // Make sure our key/index array is sorted properly
                foreach( $matches as $index => $value ) {
                    $params[ $replacements[$index] ] = $value;
                }

                // replace get params with what we have here & whats in the URL...
                // we dont want them to see what we are playing with internally tbh
                $this->modifyGET($params);

                // add some extras here...
                $params['_all'] = $params;

                // Add a hook for the params
                $objPlugin->hook('CMS_ROUTE_PARAMS', $params);

                // merge the arguments & the params and then invoke the route
                $route['arguments'] = array_merge( (array) $route['arguments'], $params);
                $this->invoke($route);

                unset($route, $matches, $params, $replacements, $parts_u, $parts_p, $ourRoute, $replaceWith, $objCache);
                return true;
            }// End of our If conditional checking for the url match

            (cmsDEBUG ? memoryUsage('Routes: Pattern Dismissed, Pattern Variables didn\'t match') : '');

        } // End the foreach loop on the routes

        (cmsDEBUG ? memoryUsage('Routes: No Patterns Matched. Invoke 404') : '');
        $this->throwHTTP(404);

        return;
    }

    /**
     * Invokes the action of a route
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies & Dan Aldridge
     *
     * @param       $route  array   Key/Value array of a Route
     *
     * @return      bool
     */
    public function invoke( $route = array( ) ) {
        (cmsDEBUG ? memoryUsage('Routes: Pattern Matched. Invoke Route :D') : '');
        if( empty( $route ) ) {
            trigger_error('Route passed is null. :/', E_USER_ERROR);
        }

        // Check if the route is a redirection
        if( !empty( $route['redirect'] ) ) {
            // TODO: Add Internal Redirections (Internal, meaning no 301, just different internal processing)
            $this->throwHTTP( 301, $route['redirect'] );
            return true;
        }

        // We assume the invoke is a module call, Let's go!
        $module = $route['arguments']['module'];
        $method = $route['arguments']['method'];

        // Check the class and subsequent method are callable, else trigger an error
        if ( !is_callable( array( $module, $method ) ) ) {
            trigger_error( 'The module or method you are trying to call, dosen\'t exist.' );
            $a = array($module, $method);
            echo dump($a, 'Your trying to call..');
            return false;
        }

        // Retrieve the info we need about the class and method
        (cmsDEBUG ? memoryUsage('Routes: Method found to be callable. Do our thing :D') : '');
        $refMethod = new ReflectionMethod( $module, $method );
        $params    = $refMethod->getParameters( );
        $args      = array( );

        // Loop through the parameters the method asks for, and match them up with our arguments
        foreach( $params as $k => $name ) {
            $var = $name->getName();

            // check if the var they asked for is in the params
            if(!isset($route['arguments'][$var])){
                $args[$var] = null;
                continue;
            }

            // and then check if we have to throw the var at them as a reference
            if($name->isPassedByReference()){
                $args[$var] = &$route['arguments'][$var];
            }else{
                $args[$var] = $route['arguments'][$var];
            }
        }

        // GO! $Module!, $Module used $Method($args)... It was super effective!
        (cmsDEBUG ? memoryUsage('Routes: Call Method.') : '');
        $objModule = new $module;
        $objModule->setVars(array(
            '_method' => $method,
            '_module' => $module,
        ));
        $refMethod->invokeArgs( $objModule , $args );
    }

    /**
     * Adds a route to the database
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $module string  ID hash of the module
     * @param       $route  array   Key/Value array of a Route
     *
     * @return      bool
     */
    public function addRoute( array $route ) {
        $values   = array();
        $label    = $route['label'];
        $objSQL   = coreObj::getDBO();
        $objCache = coreObj::getCache();

        $values['label']        = $label;
        $values['status']       = '1';
        $values['module']       = $route['moduleID'];
        $values['pattern']      = $route['pattern'];
        $values['arguments']    = json_encode( !empty( $route['arguments'] )    ? $route['arguments']    : array() );
        $values['requirements'] = json_encode( !empty( $route['requirements'] ) ? $route['requirements'] : array() );
        // To Add Logic for: Status && Redirection

        $query = $objSQL->queryBuilder()
                        ->insertInto('#__routes')
                        ->set($values)
                        ->build();

        $result = $objSQL->query($query);
        $objCache->doCache('route');

        return $result;
    }

    /**
     * Adds a collection of routes to the database (multi-alias of addRoute)
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $module string  ID hash of the module
     * @param       $routes array   Key/Value array of the Routes
     *
     * @return      bool
     */
    public function addRoutes( $module, array $routes ) {
        if( empty( $routes ) ) {
            return false;
        }

        foreach ( $routes as $name => $route ) {
            $this->addRoute( $module, array( $name => $route ) );
        }
    }

    /**
     * Completely removes a route from the database
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $id     int     ID of the Route
     *
     * @return      bool
     */
    public function deleteRoute( $id ) {
        $objSQL   = coreObj::getDBO();
        $objCache = coreObj::getCache();

        $query  = $objSQL->queryBuilder()
                        ->deleteFrom('#__routes')
                        ->where(sprintf('id = %d', $id))
                        //      'id', '=', '%d'
                        ->build();

        $result = $objSQL->query($query);
        $objCache->doCache('route');

        return $result;
    }

    /**
     * Toggles a route from being active or inactive
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $id     int     ID of the Route
     * @param       $status int     New Status of the Route (0=Inactive, 1=Active)
     *
     * @return      array
     */
    public function toggleRoute( $id, $status = null ) {

        $update   = array();
        $objSQL   = coreObj::getDBO();
        $objCache = coreObj::getCache();
        $query    = $objSQL->queryBuilder();

        if( is_bool( $status ) !== true ) {
            $update['status'] = 'IF(status=1, 0, 1)';
        } else {
            $update['status'] = ( $status === true ? '1' : '0' );
        }

        $query = $query->update('#__routes')
                        ->set($update)
                        ->where(sprintf('id = %d', $id))
                        ->build();

        $result = $objSQL->query($query);
        $objCache->doCache('route');

        return $result;
    }

    /**
     * Generates the cache for the routing system, used as a callback in the caching class
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @todo        Use 2 Queries, One to select non-structure url's (without :'s)
     *                  and one with structure'd url's. The first should be listed
     *                  before the second, to allow for successful processing and
     *                  precedence.
     *
     * @return      array
     */
    public static function generate_cache(){
        $output = array();
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select('module', 'label', 'pattern', 'arguments', 'requirements', 'status', 'redirect')
                            ->addField('pattern LIKE "%:%" as `dynamic`')
                        ->from('#__routes')
                        ->where('status', '=', '1')
                        ->orderBy('`dynamic`, CHAR_LENGTH(pattern)', 'DESC')
                        ->build();

        $results = $objSQL->fetchAll( $query );

        foreach( $results as $result ) {

            $args = json_decode( $result['arguments'], true);
                if($args === null){
                    $args = array();
                }

            $reqs = json_decode( $result['requirements'], true);
                if($reqs === null){
                    $reqs = array();
                }

            $output[$result['pattern']] = array(
                'method'        => ( !empty( $result['method'] ) ? $result['method'] : 'any' ),
                'pattern'       => $result['pattern'],
                'module'        => $result['module'],
                'arguments'     => $args,
                'requirements'  => $reqs,
                'label'         => $result['label'],
                'status'        => $result['status'],
                'redirect'      => $result['redirect']
            );
        }

        return $output;
    }

    /**
     * Throws a HTTP Error Code and a pretty CMS Page
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int    $error
     *
     * @return bool
     */
    public function throwHTTP($error=000, $val=null){
        if(headers_sent()){ return false; }

        $msg = NULL;
        $objPage = coreObj::getPage();
        switch($error){
            default:
            case 000:
                header('HTTP/1.0 '.$error.'');
                $msg = 'Something went wrong, we cannot determine what. HTTP Error: '.$error;
            break;

            case 301:
                header('HTTP/1.0 301 Moved Permanently');
                header('Location: ' . $val);
            break;

            case 400:
                header('HTTP/1.0 400 Bad Request');
                $objPage->setTitle('Error 400 - Bad Request');
                $msg = 'Error 400 - The server did not understand your request.' .
                        ' If the error persists contact an administrator with details on how to replicate the error.';
            break;

            case 401:
                header('HTTP/1.0 401 Unauthorized');
                $objPage->setTitle('Error 401 Unauthorized');
                $msg = 'Error 401 - You do not have authorization to access esource.';
            break;

            case 403:
                header('HTTP/1.0 403 Forbidden');
                $objPage->setTitle('Error 403 - Forbidden');
                $msg = 'Error 403 - You have been denied access to the requested page.';
            break;

            case 404:
                header('HTTP/1.0 404 Not Found');
                $objPage->setTitle('Error 404 - Page Not Found');
                $msg = 'Error 404 - The file you were looking for cannot be found.';
            break;

            case 500:
                header('HTTP/1.0 500 Internal Server Error');
                $objPage->setTitle('Error 500 - Internal Server Error');
                $msg = 'Error 500 - Oops it seems we have broken something..   ';
            break;
        }

        //hmsgDie('FAIL', $msg);
    }

    /**
     * Merges $params with the current _GET global.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   array   $params
     */
    public function modifyGET($params=array()){
        $url = explode('?', $_SERVER['REQUEST_URI']);

        $urlParams = array();
        if(isset($url[1]) && !empty($url[1])){
            //parse the _GET vars from the url
            parse_str($url[1], $urlParams);
        }

        //and merge away :D
        $_GET = array_merge($params, $urlParams);
    }
}

?>