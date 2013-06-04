<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
namespace CSCMS\Core\Classes;
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


/**
* Handles the plugin system for the CMS
*
* @version     1.2
* @since       1.0.0
* @author      Dan Aldridge
*/
class Plugins extends coreObj{
    private $dontExec       = false,
            $hooks          = array(),
            $result         = array(),
            $availableHooks = array();

    /**
     * Get plugin list from the database, and attempt to load them in
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @return      bool
     */
    public function __construct(){

        // try and load the plugins
        $this->load();

    }

    /**
     * Get plugin list from the database, and attempt to load them in
     *
     * @version     1.1
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       array $plugins
     *
     * @return      bool
     */
    public function load($plugins=array()){
        if($this->dontExec == true){ return false; }
        $objSQL = coreObj::getDBO();

        // make sure we didn't get an empty var...
        if(!is_array($plugins) || is_empty($plugins)){
            // if we did try and get a fresh copy from the db
            $objCache = coreObj::getCache();

            $plugins = $objCache->load('plugins');

            if(!is_array($plugins) || is_empty($plugins)){
                $this->dontExec = true;
                return false; // no luck this time so just return quietly
            }
        }

        // loop though each plugin
        foreach( $plugins as $hook ){
            $hookStr = $hook['path'];

            // make sure its actually a file and is readable
            if( !is_file($hookStr) && !is_readable($hookStr) ){
                continue;
            }

            // also make sure its enabled..
            if( $hook['enabled'] === false ){
                continue;
            }

            // and then include it :D
            include_once( str_replace('./', cmsROOT.'', $hookStr) );
        }

        // everything worked as expected so just return true;
        return true;
    }

    /**
     * This is the backbone of the sys
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string     $hook
     * @param       mixed      $args
     * @param       string     $option
     * @param       int        $priority
     *
     * @return      string
     */
    public function hook($hook, &$args='', $option='run', $priority=MED){
        // decide what we need to do here
        switch( $option ){
            case 'run':
                $hooks = $this->hooks;
                $func = '';

                // make sure we have something to run with
                if( !is_array($hooks) || is_empty($hooks) ){ return; }

                if( !in_array($hook, $this->availableHooks) ) {
                    $this->availableHooks[] = $hook;
                }

                // loop though each 'priority'
                foreach( array('1', '2', '3') as $prio ){
                    if( !isset($hooks[$hook][$prio]) || !is_array($hooks[$hook][$prio]) || is_empty($hooks[$hook][$prio]) ){
                        continue;
                    }

                    // and then each hook
                    while( current($hooks[$hook][$prio]) ){
                        // get func name
                        $func = key($hooks[$hook][$prio]);
                        $return = null;

                        // see if we want to call to a class or not
                        $test = json_decode($func, true);
                        if( $test !== null ){
                            $function = $test;
                        } else{
                            $function = $func;
                        }

                        // make sure we can call it still
                        if( is_callable( $function ) ){

                            if( !is_object($function) ){
                                // if class, then reflect method, else just reflect function
                                if( is_array($function) && count($function) > 1 ){
                                    $ref = new \ReflectionMethod( $function[0], $function[1] );
                                }else{
                                    $ref = new \ReflectionFunction( $function );
                                }

                                // grab a list of arguments
                                $vars = array();
                                $params = $ref->getParameters();
                                foreach( $params as $k => $name ) {
                                    // and then check if we have to throw the var at them as a reference
                                    if( $name->isPassedByReference() ){
                                        $vars[$k] = &$args[$k];
                                    }else{
                                        $vars[$k] = $args[$k];
                                    }
                                }

                                // invoke the args and continue
                                if( is_array($function) && count($function) > 1 ){
                                    $return = $ref->invokeArgs( new $function[0], $vars );
                                }else{
                                    $return = $ref->invokeArgs( $vars );
                                }

                            }else{

                            }

                        }

                        // check to see if we got a response from the func, this should be true
                        if( is_empty($return) ){
                            $return = false;
                        }

                        // assign it to the array and continue
                        $this->result[$hook][] = $return;
                        next($hooks[$hook][$prio]);
                     }
                }
                if( isset($func) && isset( $this->result[$hook] ) ) {
                    return $this->result[$hook];
                }
            break;

            case 'add':
                // register the hook with the system
                $this->hooks[$hook][$priority][$args] = 'fail';
            break;

            case 'rm':
                // remove the hook from the system
                unset($this->hooks[$hook][$priority][$args]);
            break;
        }
    }

    /**
     * Attach a function to a hook with specified priority
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $hook
     * @param       string  $callback
     * @param       int     $priority
     */
    public function addHook($hook, $callback, $priority=MED){
        if( is_array($callback) && count($callback) > 1 ){
            $callback = json_encode($callback);
        }

        $this->hook($hook, $callback, 'add', $priority);
    }

    /**
     * Remove a hook from the system
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $hook
     * @param       string  $callback
     * @param       int     $priority
     */
    public function delHook($hook, $callback, $priority=MED){
        $this->hook($hook, $callback, 'rm', $priority);
    }

    /**
     * Returns a list of all the available hooks on this page.
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $hook
     * @param       string  $callback
     * @param       int     $priority
     */
    public function getAvailableHooks(){
        return $this->availableHooks;
    }
}
?>