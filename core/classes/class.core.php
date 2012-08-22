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

        //loop thru the dirs, trying to match the class with the file
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
        if(isset($this->$var) && !empty($this->$var)){
            return $this->$var;
        }

        return false;
    }

    /**
     * Returns a normalized array of arguments from the function
     *
     * @version 1.0
     * @since   1.0.0
     * @author  xLink
     *
     * @param   array  This should be func_get_args();
     *
     * @return  array
     */
    public function _getArgs($args){
        $argsCnt = count($args);
        if(!$argsCnt){ return array(); }

        if($argsCnt == 1){
            if(!is_array($args[0])){ return array($args[0]); }

            return $args[0];
        }else{
            $return = array();
            foreach($args as $arg){ $return[] = $arg; }

            return $return;
        }

        return array();
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

        //make sure we have soemthing before trying to throw it out
        if(!in_array($array, array_keys($config))){
            return false;
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
    public static function getDBO($driver=null){
        global $errorTPL;

        if(!isset(self::$_classes['database_'.$driver])){
            $options = self::config('db');
                if(!$options){ trigger_error('Error: Could not obtain values from teh configuration file. Please ensure it is present.', E_USER_ERROR); }

            $name = $options['driver'];

            //see if we have an override
            $driver = strtolower($driver);
            if(in_array($driver, array('mysql', 'mysqli'))){
                $name = $driver;
            } $name = 'driver_'.$name;

            $options['persistant'] = true;
            $options['debug']      = (cmsDEBUG ? true : false);
            $options['logging']    = is_file(cmsROOT.'cache/ALLOW_LOGGING');

            $objSQL = $name::getInstance($options);
                if($objSQL === false){
                    if(!headers_sent()){
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
            self::$_classes['database_'.$name] = $objSQL;
        }

        return self::$_classes['database_'.$name];
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