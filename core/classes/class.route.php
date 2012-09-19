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

    function processURL( $url ) {
        global $routes;

        foreach( $routes as $label => $route )
        {
            $info = $route[1];
            $route = $route[0];
            $usedParams = array();

            // Seperate the route and URL into parts
            $parts_p = explode( '/', $url );
            $parts_r = explode( '/', $route );

            // Filter out empty values, and essentially reset the array keys
            $parts_p = array_values( array_filter( $parts_p ) );
            $parts_r = array_values( array_filter( $parts_r ) );

            // /path/to/forum
            // /forum/your/:mom

            // If the route and parts are of equal length
            if( count( $parts_p ) === count( $parts_r) ) {

                // Let's assume we've found a match
                $matched = true;

                // Loop through each part of the route
                foreach( $parts_r as $index => $part ) {

                    // If the part doesn't contain something to be replaced, and isn't exact to the route
                    //      $matched = false; break;

                    // If the part contains something to be replaced
                    //  and when replaced, matches the URL
                    //  replace the url and set matched = true

                    // If $matched === true
                    //      Invoke Module::$Method( $params );
                    //             call_user_func_array(array($module, $method), $params);

                    ////////////////////////////////////////////

                    if( strpos( $part, ':' ) === false && $part !== $parts_p[$index] ) {
                        $matched = false;
                        break;

                    } else if( strpos( $part, ':' ) !== false ){
                        preg_match_all( '/\:([A-Za-z]+)/', $part, $matches );
                        $temp = $part;

                        echo dump( $matches[0] );

                        // Loop through the parts we have left (that haven't been replaced)
                        foreach( $matches[0] as $index => $match )
                        {
                            $replacedPart = str_replace( $match, $info[$index], $temp );
                        }
                        
                        // Get the params
                    }
                }
            } // End if for checking the part lengths
        } // End the foreach loop on the routes
    }

    public function processRoutes( $routes = array()  ) {
        if( !is_array( $routes ) || is_empty( $routes ) ) {
            return array();
        }
    }

    /**
     * Adds a route structure into the Database
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   $module     string  
     *
     * @return  void
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

    public function addRoutes( $module, array $routes ) {
        if( empty( $routes ) )
        {
            return false;
        }

        foreach ( $routes as $name => $route ) {
            $this->addRoute( $module, array( $name => $route ) );
        }
    }

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

    public static function generate_cache(){
        $output = array();
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select('module', 'label', 'pattern', 'arguments', 'requirements', 'status', 'redirect')
                        ->from('#__routes')
                        ->build();

        $results = $objSQL->fetchAll( $query );

        foreach( $results as $result ) {

            $args = (array)( json_decode( $result['arguments'] )    !== null ? json_decode( $result['arguments'] )    : array() );
            $reqs = (array)( json_decode( $result['requirements'] ) !== null ? json_decode( $result['requirements'] ) : array() );

            $args = recursiveArray($args, 'stripslashes');
            $reqs = recursiveArray($reqs, 'stripslashes');

            $output[$result['pattern']] = array(
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