<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 *
 *
 * @version     1.0
 * @since       1.0.0
 * @author      xLink
 */
class coreObj {

    public static  $classDirs   = array(), 
                    $_classes    = array(), 
                    $_instances  = array();

    /**    
     * Adds a directory to be scanned for classes to be loaded
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   string  $dir
     *
     * @return  array
     */
    public static function addClassDirs($dir){
        if(empty($dir) || !is_string($dir)){ 
            return false; 
        }

        self::$classDirs[$dir] = glob($dir);

        return self::$classDirs;
    }

    /**    
     * Function for Autoloading Classes
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   string  $class      Class to load
     *
     * @return  bool
     */
    public static function loadClass($class) {
        //echo dump($class, 'LOADING', 'pink');
        if(empty(self::$classDirs)){ trigger_error('Error: No Directories to scan for class.', E_USER_ERROR); }

        //only use the last part of the class name if it has an underscore
        if(strpos($class, '_') !== false){
            $class = explode('_', $class);
            $class = end($class);
        }
        $class = strtolower($class);

        $dirs = self::$classDirs;
        foreach($dirs as $dir => $files){
            if(!count($files)){ continue; }
            foreach($files as $file){
                if(strpos($file, $class)!==false){ 
                    include_once($file);
                    // echo dump($file, 'LOADED FILE', 'pink');
                    return true;
                }
            }
        }

        trigger_error('Error: No File found for this Class.'.dump($dirs, $class), E_USER_ERROR);
        return false;
    }

    /**    
     * Sets a variable with a value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   string  $var
     * @param   mixed   $value
     */
    public function setVar($var, $value){
        
        $this->$var = $value;
    }

    /**
     * Sets multiple variables with values
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   array $array
     */
    public function setVars($array){

        if(!is_array($array)){ return false; }

        foreach($array as $k => $v){
            $this->$k = $v;
        }
        return true;
    }

    /**
     * Returns a var's value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   string  $var
     *
     * @return  mixed
     */
    public function getVar($var){

        return (isset($this->$var) ? $this->$var : false);
    }

    /**
     * Returns a config variable
     *
     * @version 1.2
     * @since   1.0.0
     * @author  xLink
     *
     * @param   string  $array
     * @param   string  $setting
     * @param   mixed   $default
     *
     * @return  mixed
     */
    public static function config($array=null, $setting=null, $default=null){
        global $config;

        //if no arguments were passed, throw it all out
        if(!func_num_args()){ 
            return $config; 
        }

        //if just an array key was passed and it exists, throw that out
        if(func_num_args()==1 && in_array($array, array_keys($config))){
            return $config[$array];
        }

        return doArgs($setting, $default, $config[$array]);
    }

    /**
     * 
     * @version 1.0
     * @since   1.0
     * 
     *
     * @param   string      $prefix  Prefix used to distinguish objects.
     */
    public static function getInstance($prefix=''){

        if(!is_string($prefix)){ $prefix = md5($prefix); }

        if (!isset(self::$_instances[$prefix]) || empty(self::$_instances[$prefix])){
            $class = self::getStaticClassName();
            self::$_instances[$prefix] = new $class($prefix);
        }

        return self::$_instances[$prefix];
    }

    /**
     * Returns the name of the class this var an instance of
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @return  string
     */
    public function getClassName(){
        return get_called_class();
    }

    /**
     * Returns the name of the class for static calling
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @return  string
     */
    public static function getStaticClassName() {
        return get_called_class();
    }


//
//--SQL
//
    public static function getDBO(){
        global $errorTPL;
        
        // echo dump($a, 'New DBO!', 'red');
        if(!isset(self::$_classes['database'])){
            $options = self::config('db');

            $name = 'driver_'.$options['driver'];
            if(!class_exists($name)){ die(); }

            $options['persistant'] = true;
            $options['debug']      = (LOCALHOST && cmsDEBUG ? true : false);
            $options['logging']    = is_file(cmsROOT.'cache/ALLOW_LOGGING');

            $objSQL = $name::getInstance($options);
                if($objSQL === false){
                    if (!headers_sent()){
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    hmsgDie('FAIL', 'Error: No DB Avaliable');
                }

            if(!$objSQL->connect()){
                msgDie('FAIL', 
                    sprintf($errorTPL, 'Fatal Error', 
                        'Connecting to SQL failed. '.
                            $objSQL->getVar('errorMsg').
                            (cmsDEBUG ? '<br />'.$objSQL->getError() : NULL)
                    )
                );
            }
            self::$_classes['database'] = $objSQL;
        }

        return self::$_classes['database'];
    }

    public static function getSession(){
        $options = array();
        if(!isset(self::$_classes['session'])){
            self::$_classes['session'] = self::createSession($options);
        }

        return self::$_classes['session'];
    }

    protected static function createSession($options = array()){
        // // Get the editor configuration setting
        // $handler = self::config('session', 'session_handler', 'none');

        // // Config time is in minutes
        // $options['expire'] = (self::config('session', 'lifetime')) ? self::config('session', 'lifetime') * 60 : 900;

        $objSession = session::getInstance($handler, $options);
        // if ($objSession->getState() == 'expired'){
        //     $objSession->restart();
        // }

        return $objSession;
    }


}

?>