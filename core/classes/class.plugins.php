<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
* Handles the plugin system for the CMS
*
* @version     1.2
* @since       1.0.0
* @author      Dan Aldridge
*/
class Core_Classes_Plugins extends Core_Classes_coreObj{
    private $dontExec       = false,
            $hooks          = array(),
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
        $objSQL = Core_Classes_coreObj::getDBO();

        //make sure we didnt get an empty var...
        if(!is_array($plugins) || is_empty($plugins)){
            //if we did try and get a fresh copy from the db
            $objCache = Core_Classes_coreObj::getCache();

            $plugins = $objCache->load('plugins');

            if(!is_array($plugins) || is_empty($plugins)){
                $this->dontExec = true;
                return false; //no luck this time so just return quietly
            }
        }

        //loop though each plugin
        foreach($plugins as $hook){
            $hookStr = $hook['path'];

            //make sure its actually a file and is readable
            if(!is_file($hookStr) && !is_readable($hookStr)){ continue; }

            //also make sure its enabled..
            if(!$hook['enabled']){ continue; }

            //and then include it :D
            include_once( str_replace('./', cmsROOT.'', $hookStr) );
        }

        //everything worked as expected so just return true;
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
     * @param       string     $args
     * @param       string     $option
     * @param       int        $priority
     *
     * @return      string
     */
    public function hook($hook, &$args='', $option='run', $priority=MED){
        //decide what we need to do here
        switch($option){
            case 'run':
                $hooks = $this->hooks;
                //make sure we have something to run with
                if(!is_array($hooks) || is_empty($hooks)){ return; }

                if( !in_array( $hook, $this->availableHooks ) ) {
                    $this->availableHooks[] = $hook;
                }

                //loop though each 'priority'
                foreach(array('1', '2', '3') as $prio){
                    if(!isset($hooks[$hook][$prio]) || !is_array($hooks[$hook][$prio]) || is_empty($hooks[$hook][$prio])){ continue; }

                    // and then each hook
                    while(current($hooks[$hook][$prio])){
                        //get func name
                        $function = key($hooks[$hook][$prio]);
                        $cb = '';

                        //make sure we can call it still
                        if(is_callable($function)){ $cb = $function($args); }

                        //check to see if we got a response from the func, this should be true
                        if(is_empty($cb)){ $cb = false; }

                        //assign it to the array and continue
                        $result[$hook][$prio][$function] = $cb;
                        next($hooks[$hook][$prio]);
                     }
                }
                if( isset($function) && isset( $result[$hook][$priority][$function] ) ) {
                    return $result[$hook][$priority][$function];
                }
            break;

            case 'add':
                //register the hook with the system
                $this->hooks[$hook][$priority][$args] = 'fail';
            break;

            case 'rm':
                //remove the hook from the system
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