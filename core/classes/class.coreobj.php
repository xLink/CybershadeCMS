<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Core Obj, the class that extends all other classes
 *
 * @version  1.1
 * @since    1.0.0
 * @author   Dan Aldridge
 */
class Core_Classes_coreObj {

    public static   $classDirs      = array(),
                    $_classes       = array(),
                    $_instances     = array(),
                    $_config        = array(),
                    $_lang          = array(),
                    $coreMethods    = array(),
                    $loadedConfig   = false;


    /**
     * Adds a directory to be scanned for classes to be loaded
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $dir
     *
     * @return  array
     */
    public static function addClassDirs($dirs){
        if( is_empty($dirs) || !is_array($dirs) ){
            return false;
        }

        foreach($dirs as $label => $dir){
            self::$classDirs[ $label ] = glob( $dir );
        }

        return self::$classDirs;
    }

    /**
     * Function for Autoloading Classes
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $class      Class to load
     *
     * @return  bool
     */
    public static function loadClass($class) {

        if ( !class_exists($class) && !interface_exists($class) ){

            // explode the classname by _'s
            $fp = explode('_', $class);

            // grab the class name, and sprintf it into a filename
            $fn = array_pop($fp);
            $file = sprintf('class.%s.php', $fn);
            $fp = array_map('strtolower', $fp);

            // handle the modules, their dir structure is a little off
            switch( $fp[0] ){
                // modules just need the module adding to the path
                case 'modules':
                    $fp[] = $fn;
                break;

                case 'admin':
                case 'user':
                    $acp = array();
                    if( $fp[1] == 'modules' ){
                        $acp[] = 'modules';
                    }
                    $acp[] = $fn;

                    $fp = $acp;
                break;

                // override means they are doing something from the themes folder, so we need to account for that by
                // throwing in the theme name and making sure the directory route is right
                case 'override':
                    $fp = array_reverse($fp);
                    $fp[] = self::config('site', 'theme');
                    $fp[] = 'themes';
                    $fp = array_reverse($fp);
                    $fp[] = $fn;

                break;
            }

            // re-add the filename into the mix and implode it to make the filepath
            if( $fp[0] == 'core' ){
                $fp[] = strtolower($file);
            }else{
                $fp[] = ( isset($acp) ? str_replace('class.', 'admin.', $file) : $file );
            }

            $fp = implode('/', $fp);

            if( file_exists($fp) && !is_dir($fp) ){
                include_once($fp);
                return true;
            }

        }

        trigger_error('No File found for this Class. '.$class.'. Tried File: '.$fp, E_USER_ERROR);
        return false;
    }

    /**
     * Sets a variable with a value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
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
     * @author  Dan Aldridge
     *
     * @param   array $array
     */
    public function setVars($array){

        if(!is_array($array)){ return false; }

        foreach($array as $k => $v){
            $this->setVar($k, $v);
        }
        return true;
    }

    /**
     * Returns a var's value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
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
     * @author  Dan Aldridge
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
     * Returns the last error set.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  string
     */
    public function getError(){
        return end($this->errors);
    }

    /**
     * Returns the entire array.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  array
     */
    public function getErrors(){
        return $this->errors;
    }

    /**
     * Allows for an error to be set just before returning false
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $msg
     */
    public function setError($msg){
        $this->errors[] = (string)$msg;
    }

    /**
     * Returns a config variable
     *
     * @version 1.2
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $array
     * @param   string  $setting
     * @param   mixed   $default
     *
     * @return  mixed
     */
    public static function config($array=null, $setting=null, $default=null){
        $config = self::$_config;

        // if no arguments were passed, throw it all out
        if( !func_num_args() ){
            return $config;
        }

        // if just an array key was passed and it exists, throw that out
        if( func_num_args() == 1 && in_array($array, array_keys($config) )){
            return $config[$array];
        }

        // make sure we have something before trying to throw it out
        if( !in_array($array, array_keys($config)) ){
            $a = func_get_args();
            return false;
        }

        return doArgs($setting, $default, $config[$array]);
    }

    /**
     * Merges an array with the config array
     *
     * @version 1.2
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   array $var
     *
     * @return  mixed
     */
    public static function addConfig( $var ){
        if( is_empty($var) && !is_array($var) ){
            return false;
        }

        self::$_config = array_merge( self::$_config, $var );
        //echo dump(self::$_config);

        return self::$_config;
    }

    /**
     * Returns or spawns a new instance of this class.
     *
     * @version 1.2
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string      $prefix  Prefix used to distinguish objects.
     *
     * @return  new object
     */
    public static function getInstance($name, $options=array()){

        if (!isset(Core_Classes_coreObj::$_classes[$name]) || empty(Core_Classes_coreObj::$_classes[$name])){
            $class = self::getStaticClassName();
            $iClass = new $class($name, $options);

            // default to returning the class as is, but test to see if we have setupInstance
            // && if so, we'll return that :D
            Core_Classes_coreObj::$_classes[$name] = $iClass;

            // grab the methods for this clas & make if we have a setupInstance() then run it
            $iClass::$coreMethods = get_class_methods($iClass);
            if( in_array('setupInstance', $iClass::$coreMethods) && is_callable(array($iClass, 'setupInstance')) ){
                Core_Classes_coreObj::$_classes[$name] = $iClass->setupInstance($name, $options);
            }
        }

        return Core_Classes_coreObj::$_classes[$name];
    }

    /**
     * Returns the name of the class this var an instance of
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
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
     * @author  Dan Aldridge
     *
     * @return  string
     */
    public static function getStaticClassName() {
        return get_called_class();
    }



/**
  //
  //-- Get Class Instances
  //
**/

    /**
     * Determines whether we need to call a getInstance() Alias or just let it through
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $method
     * @param   array $args
     *
     * @return  mixed
     */
    public static function __callStatic($method, $args){

        // check to see if we have called a get*() method
        if( substr($method, 0, 3) === 'get' ){
            $className = str_replace('get', '', $method);
            $className = ucwords($className);
            $className = 'Core_Classes_'.$className;

            if( !isset(self::$coreMethods) ){
                $objCore = new Core_Classes_coreObj;
                self::$coreMethods = get_class_methods($objCore);
            }

            if( class_exists($className) && !in_array($className, self::$coreMethods) ){

                if( !isset(Core_Classes_coreObj::$_classes[$className]) ){
                    //$className::getInstance($className, (is_array($args) && isset($args[1]) ? $args[1] : array()));
                    $className::getInstance($className, $args[1]);
                }

                return Core_Classes_coreObj::$_classes[$className];
            }
        }

        // Method name didnt match what we expected so just output an error now.

        $debug = array(
            'Class Name'    => self::getStaticClassName(),
            'Method Called' => $method,
            'Method Args'   => $args,
        );
        trigger_error('Error: Static Method dosen\'t exist.'.dump($debug));

        return null;
    }

    public static function getLib( $name, $args = array() ){
        $dir = cmsROOT.'libaries/';

        // if the class dosent exist, then we'll load it
        if( !class_exists($name, false) ){
            $path = strtolower($dir.$name.'/class.'.$name.'.php');

            if( file_exists($path) ){
                include_once($path);
            }
        }

        // if it already exists, load it in and throw it the args array
        if( class_exists($name, false) ){
            $obj = new ReflectionClass($name);
            
            if( count($args) ){
                return $obj->newInstanceArgs($args);
            }else{
                return $obj->newInstance();
            }
        }

        // if nothing happened, return false
        return false;
    }

    public static function getDBO(){
        global $errorTPL;

        if(!isset(Core_Classes_coreObj::$_classes['database'])){
            $options = self::config('db');
                if(!$options){ trigger_error('Error: Could not obtain values from the configuration file. Please ensure it is present.', E_USER_ERROR); }

            $name = 'Core_Drivers_'.$options['driver'];

            $options['persistant'] = true;
            $options['debug']      = (cmsDEBUG ? true : false);
            $options['logging']    = is_file(cmsROOT.'cache/ALLOW_LOGGING');

            $objSQL = new $name(null, $options);
                if($objSQL === false){
                    if( !headers_sent() ){
                        header('HTTP/1.1 500 Internal Server Error');
                        exit;
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
            Core_Classes_coreObj::$_classes['database'] = $objSQL;
        }

        return Core_Classes_coreObj::$_classes['database'];
    }

    public static function getTPL(){
        global $errorTPL;

        if(!isset(Core_Classes_coreObj::$_classes['tpl'])){
            $cachePath = cmsROOT.'cache/template/';
            if(is_dir($cachePath) && !is_writable($cachePath)){
                @chmod($cachePath, 0755);
            }

            if(!is_writable($cachePath)){
                trigger_error('Could not set CHMOD permissions on "<i>'.$cachePath.'</i>" set to 775 to continue.', E_USER_ERROR);
            }

            Core_Classes_Template::getInstance('tpl', array(
                'useCache' => (is_writable($cachePath) ? true : false),
                'cacheDir' => $cachePath,
                'root'     => '.',
            ));
        }

        return Core_Classes_coreObj::$_classes['tpl'];
    }

    public static function getCache(){
        if(!isset(Core_Classes_coreObj::$_classes['cache'])){

            //cache setup
            $cachePath = cmsROOT.'cache/';
            if(is_dir($cachePath) && !is_writable($cachePath)){
                @chmod($cachePath, 0755);
            }

            if(!is_writable($cachePath)){
                trigger_error('Could not set CHMOD permissions on "<i>'.$cachePath.'</i>" set to 775 to continue.', E_USER_ERROR);
            }

            Core_Classes_Cache::getInstance('cache', array(
                'useCache' => (is_writable($cachePath) ? true : false),
                'cacheDir' => $cachePath,
            ));


            Core_Classes_coreObj::$_classes['cache']->get( 'config' );
        }

        return Core_Classes_coreObj::$_classes['cache'];
    }

}


?>