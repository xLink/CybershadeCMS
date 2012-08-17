<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class core_SQL extends coreObj implements base_SQL{

    public  $queries        = array();
    public  $dbSettings     = array();
    public  $DBH            = false;
    public  $failed         = false,
            $debug          = false,
            $logging        = false;


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
    public function __construct($options) {
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

    public function __destruct() {

        $this->index($this->dbSettings['database']);
        return $this->disconnect();
    }

    public function __clone() {

        trigger_error('Error: Cloning prohibited.', E_USER_ERROR);
        return false;
    }

    public function __wakeup() {

        trigger_error('Error: Deserialization of singleton prohibited...', E_USER_ERROR);
        return false;
    }


    public function connect(){

        trigger_error('Error: Override this function from within the SQL Driver. Class Loaded: '.$this->getClassName(), E_USER_ERROR);
        return false;
    }

    public function disconnect(){

        trigger_error('Error: Override this function from within the SQL Driver. Class Loaded: '.$this->getClassName(), E_USER_ERROR);
        return false;
    }

    public function selectDB($db){

        trigger_error('Error: Override this function from within the SQL Driver. Class Loaded: '.$this->getClassName(), E_USER_ERROR);
        return false;
    }

    public function getError(){

        trigger_error('Error: Override this function from within the SQL Driver. Class Loaded: '.$this->getClassName(), E_USER_ERROR);
        return false;
    }


    /**
     * Executed when instance is destroyed to make sure tables are in tip-top shape!
     * 
     * @param $link     Instance Link
     * @param $db       Database to run repair / optimize & flush on.
     */ 
    public function index($db){

        $query = $this->query('SHOW TABLES');
        $results = $this->results();
            if(!count($results)){ return false; }

        foreach($results as $key => $value) {
            if (isset($value['Tables_in_'.$db])) {
                $this->query('REPAIR TABLE '.$value['Tables_in_'.$db]);
                $this->query('OPTIMIZE TABLE '.$value['Tables_in_'.$db]);
                $this->query('FLUSH TABLE '.$value['Tables_in_'.$db]);
            }
        }

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

    //public function escape($string);

    //public function results($query);
    //public function affectedRows();
/**
  //
  //-- Extra Functionality
  //
**/

}

?>