<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class core_SQL extends coreObj{

    public  $queries        = array();
    public  $dbSettings     = array();
    public  $DBH            = false,        // database handler
            $results        = false,        // results holder for the query
            $failed         = false,        // if something failed, this is where to check
            $debug          = false,        // debug switch
            $query          = false,        // last query ran
            $prefix         = array(),      // holds all the prefixes
            $logging        = false;        // is logging enabled?


    /**
     * Sets up a new SQL Class
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function __construct($options){
        $this->dbSettings = array(
            'driver'     => doArgs('driver',        '',      $options),
            'host'       => doArgs('host',          '',      $options),
            'username'   => doArgs('username',      '',      $options),
            'password'   => doArgs('password',      '',      $options),
            'database'   => doArgs('database',      '',      $options),
            'prefix'     => doArgs('prefix',        '',      $options),
            'persistent' => doArgs('persistent',    false,   $options),
            'debug'      => doArgs('debug',         false,   $options),
            'logging'    => doArgs('logging',       false,   $options),
        );

        if($this->dbSettings['driver'] == 'pdo' && !class_exists('PDO', false)){
            trigger_error('Error: You have selected to use PDO, the interface for this Driver dosen\'t exist.', E_USER_ERROR);
        }

        if($this->dbSettings['driver'] == 'mysqli' && ( !class_exists('driver_mysqli', false) || !class_exists('mysqli', false) )){
            trigger_error('Error: You have selected to use MySQLi, the interface for this Driver dosen\'t exist.', E_USER_ERROR);
        }

        if($this->dbSettings['driver'] == 'mysql' && ( !class_exists('driver_mysql', false) || !function_exists('mysql_connect') )){
            trigger_error('Error: You have selected to use MySQL, the interface for this Driver dosen\'t exist.', E_USER_ERROR);
        }

$c = $this->getClassName();
echo dump($c, 'SQL Class Loaded');

        return false;
    }

    public function __destruct(){

        $this->index($this->dbSettings['database']);
        return $this->disconnect();
    }

    public function __clone(){

        trigger_error('Error: Cloning prohibited.', E_USER_ERROR);
        return false;
    }

    public function __wakeup(){

        trigger_error('Error: Deserialization of singleton prohibited...', E_USER_ERROR);
        return false;
    }

    /**
     * This Method will be called if there is no suitable override in the driver.
     * 
     * @param   string $method
     * @param   array  $args
     */ 
    public function __call($method, $args){
        $a = array(
            'Class Name'    => $this->getClassName(),
            'Method Called' => $method,
            'Method Args'   => $args,
        );
        trigger_error('Error: Method dosen\'t exist, Override this function from within the SQL Driver.'.dump($a), E_USER_ERROR);
    }

    /**
     * Replaces all avalible prefixes with the Table Prefix 
     * 
     * @param   string $query
     * 
     * @return  string $query
     */ 
    public function _replacePrefix($query){
        if(!count($this->prefix) || !is_array($this->prefix)){ return $query; }

        return str_replace(array_keys($this->prefix), array_values($this->prefix), $query);
    }

    /**
     * Adds a table prefix to the list.
     *
     * @version     1.0
     * @since       1.0.0
     * @param   string $prefix
     * 
     * @return  bool
     */ 
    public function registerPrefix($prefix, $replace){
        if(array_key_exists($prefix, $this->prefix)){ return false; }

        $this->prefix[$prefix] = $replace;

        return true;
    }

}

/**
 * SQL Class interface, defines the needed functionality for the SQL Drivers
 *
 * @version     1.0
 * @since       1.0.0
 * @author      xLink
 */
interface base_SQL{

    public function __construct($config);

    public static function getInstance($options=array());

/**
  //
  //-- Connection Functionality
  //
**/

    public function selectDB($db);
    public function connect();
    public function disconnect();
    public function getError();

/**
  //
  //-- Core Functionality
  //
**/

    public function escape($string);

    //public function results($query);
    //public function affectedRows();
/**
  //
  //-- Extra Functionality
  //
**/

}

?>