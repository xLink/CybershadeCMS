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
        global $routes;

        // Append a forward slash to the incoming url if there isn't one
        //  (Should be solved elsewhere)
        if( strpos( $url, '/' ) !== 0 )
        {
            $url = '/' . $url;
        }

        $objCache = coreObj::getCache();

        // @TODO: Once the caching class is sorted, we can get rid of this line
        //require_once( cmsROOT . 'cache/cache_routes.php' );
        #$routes = $objCache->load('routes');
        $routes = $this->routes;

        foreach( $routes as $label => $route ) {

            // Check for a method being set, if it doesn't match, continue
            if( $route['method'] != 'any' && $route['method'] != $_SERVER['REQUEST_METHOD']) {
                continue;
            }

            // Match Absolute URLs
            if( $route['pattern'] == $url ) {
                $this->invoke($route);
                return true;
            }

            // Store the route's pattern for replacing later on
            $ourRoute = $route['pattern'];

            // Seperate the route and URL into parts
            $parts_u = explode( '/', $url );
            $parts_r = explode( '/', $route['pattern'] );

            // Filter out empty values, and essentially reset the array keys
            $parts_u = array_values( array_filter( $parts_u ) );
            $parts_r = array_values( array_filter( $parts_r ) );

            // If the route and parts aren't of equal length, insta-dismiss this route
            if( count( $parts_u ) !== count( $parts_r) ) { continue; }

            // Collect all the replacement 'variables' from the route structure into an array
            $replacements = preg_match_all( '/\:([A-Za-z0-9]+)/', $route['pattern'], $matches );
            $replacements = ( !empty( $matches[1] ) ? $matches[1] : array() );

            // Loop through our replacements (if there are any),
            //  In the matching, if there is a requirement set, use that,
            //  else, use our generic alpha-numeric string match that includes SEO friendly chars.
            foreach( $replacements as $replacement ) {
                $replaceWith = '[A-Za-z0-9\-\_]+';

                if( !empty( $route['requirements'][$replacement] ) ) {
                    $replaceWith = $route['requirements'][$replacement];
                }

                $ourRoute = str_replace( ':' . $replacement, '(' . $replaceWith . ')', $ourRoute );
            }

            // If the route matches the URL, we've got a winner!
            if( preg_match( '#' . $ourRoute . '#', $url, $matches  ) ) {

                // Remove the URL from the paramaters
                unset( $matches[0] );
                $matches = array_values( $matches );
                $params  = array();

                // Make sure our key/index array is sorted properly
                foreach( $matches as $index => $value )
                {

                    $params[ $replacements[$index] ] = $value;
                }

                $route['arguments'] = array_merge( (array) $route['arguments'], $params);
                $this->invoke($route);

                return true;
            }

        } // End the foreach loop on the routes

        echo '404';
        // 404, Route not found
        return;
    }

    /**
     * Invokes the action of a route
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       $route  array   Key/Value array of a Route
     *
     * @return      bool
     */
    public function invoke($route=array()){
        if( empty( $route ) ) {
            trigger_error('Route passed is null. :/', E_USER_ERROR);
        }

        // Check if the route is a redirection
        if( !empty( $route['redirect'] ) ) {
            // TODO: Add Internal Redirections (Internal, meaning no 301, just different internal processing)
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $route['redirect']);
            return true;
        }

        // We assume the invoke is a module call, Let's go!
        // call_user_func_array( array( $module, $method ), $params );
        echo 'Invoking Module Call';
        echo dump($route, $_GET['l']);
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
    public function addRoute( $module, array $route ) {
        $values   = array();
        $label    = key( $route );
        $route    = $route[$label];
        $objSQL   = coreObj::getDBO();
        $objCache = coreObj::getCache();

        $values['label']        = $label;
        $values['status']       = '1';
        $values['module']       = $module;
        $values['pattern']      = $route[0];
        $values['arguments']    = json_encode( $route[1] );
        $values['requirements'] = json_encode( !empty( $route[2] ) ? $route[2] : null );
        // To Add Logic for: Status && Redirection

        $query = $objSQL->queryBuilder()
                        ->insertInto('#__routes')
                        ->set($values)
                        ->build();

        echo dump( $query );

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
        if( empty( $routes ) )
        {
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
                        ->from('#__routes')
                        ->build();

        $results = $objSQL->fetchAll( $query );

        foreach( $results as $result ) {

            $args = ( json_decode( $result['arguments'], true )    !== null ? json_decode( $result['arguments'], true )    : array() );
            $reqs = ( json_decode( $result['requirements'], true ) !== null ? json_decode( $result['requirements'], true ) : array() );

            $args = recursiveArray($args, 'stripslashes');
            $reqs = recursiveArray($reqs, 'stripslashes');

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
}

?>