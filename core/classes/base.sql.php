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
            $_query         = false,        // last query ran
            $prefix         = array(),      // holds all the prefixes
            $totalTime      = 0,            // running tally of query time
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

        return false;
    }

    public function __destruct(){

        $this->index($this->dbSettings['database']);
        return $this->disconnect();
    }

    final public function __clone(){

        trigger_error('Error: Cloning prohibited.', E_USER_ERROR);
        return false;
    }

    final public function __wakeup(){

        trigger_error('Error: Deserialization of singleton prohibited...', E_USER_ERROR);
        return false;
    }

    /**
     * This Method will be called if there is no suitable override in the driver.
     * 
     * @param   string $method
     * @param   array  $args
     */ 
    final public function __call($method, $args){
        $debug = array(
            'Class Name'    => $this->getClassName(),
            'Method Called' => $method,
            'Method Args'   => $args,
        );
        trigger_error('Error: Method dosen\'t exist, Override this function from within the SQL Driver.'.dump($debug), E_USER_ERROR);
    }

    /**
     * Replaces all avalible prefixes with the Table Prefix 
     * 
     * @param   string $query
     * 
     * @return  string $query
     */ 
    final public function _replacePrefix($query){
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
    final public function registerPrefix($prefix, $replace){
        if(array_key_exists($prefix, $this->prefix)){ return false; }

        $this->prefix[$prefix] = $replace;

        return true;
    }

/**
  //
  //-- Extra Functionality
  //
**/
    public function getDebug(){
        if(!$this->dbSettings['debug']){ return false; }

        return $this->debug;
    }

    public function queryBuilder(){
        return new queryBuilder();
    }

    public function index($db){
        $query = $this->query('SHOW TABLES');
        $results = $this->results($query);
            if(!count($results) || $results === false){ return false; }
            $this->freeResult();

        foreach($results as $key => $value) {
            if (!isset($value['Tables_in_'.$db])){ continue; }

            $this->query('REPAIR TABLE '.$value['Tables_in_'.$db]);
                $this->freeResult();
            $this->query('OPTIMIZE TABLE '.$value['Tables_in_'.$db]);
                $this->freeResult();
            $this->query('FLUSH TABLE '.$value['Tables_in_'.$db]);
                $this->freeResult();
        }

        return true;
    }

    public function fetchAll($query){
        $this->query($query);
            if(!$this->affectedRows()){ 
                return false; 
            }

        $line = $this->results();
        $this->freeResult();
        return $line;
    }

    public function fetchLine($query){
        if(!is_string($query)){ return false; }

        if(strpos($query, 'LIMIT 1') === false){
            $query = $query.' LIMIT 1';
        }

        return $this->fetchAll($query);
    }

    public function fetchValue($table, $field, $clause=null){
        //generate query
        $query = $this->queryBuilder()
                        ->select($field)
                        ->from($table);

            if(!is_empty($clause)){
                $query = $query->where($clause);
            }

        //build the query
        $query = $query->build();

        //run the query
        $this->query($query);
            if(!$this->affectedRows()){
                return false;
            }

        $line = $this->results();
        $this->freeResult();

        //and then return the results
        $field = (is_array($field) ? array_values($field) : array($field));
        return $line[0][end($field)];
    }

    public function fetchCount($table, $clause=null){
        return $this->fetchValue($table, 'COUNT(*)', $clause);
    }

    public function fetchColumnInfo($table){
        $query = 'SHOW COLUMNS FROM `%s`';

        $this->query(sprintf($query, $table));
            if(!$this->affectedRows()){ 
                return false; 
            }

        $cols = $this->results();

        $this->freeResult();
        return $cols;
    }

    public function fetchColumnData($table, $data){
        $cols = $this->fetchColumnInfo($table);
            if(!$cols){
                return false;
            }

        $names = array();
        foreach($cols as $col){
            $names[] = $col[$data];
        }

        return $names;
    }

    public function fetchAutoIncrement($table){
        $query = $this->queryBuilder()
                    ->select('AUTO_INCREMENT')
                    ->from('information_schema.TABLES')
                        ->where('TABLE_NAME LIKE #__config')
                            ->andWhere('TABLE_SCHEMA = %s')
                    ->build();
                    
        $this->query(sprintf($query, $this->config('db', 'database')));
            if(!$this->affectedRows()){ 
                return false; 
            }

        $line = $this->results();
        $line = end($line);

        $this->freeResult();
        return $line['AUTO_INCREMENT'];
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

    public function selectDB($db);
    public function connect();
    public function disconnect();
    public function getError();

    public function escape($string);
    public function freeResult();
    public function query($query);
    public function results();
    public function affectedRows();
}

?>