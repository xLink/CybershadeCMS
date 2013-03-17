<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_coreSQL extends Core_Classes_coreObj{

    public  $queries        = array();
    public  $driver         = '';
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
     * @author      Dan Aldridge
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function __construct($name=null, $options=array()){
        $this->driver = @end( explode('_', $this->getClassName( ) ) );
        $this->dbSettings = array(
            'driver'     => doArgs('driver',        '',      $options),
            'host'       => doArgs('host',          '',      $options),
            'port'       => doArgs('port',          '',      $options),
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

        if($this->dbSettings['driver'] == 'mysqli' && ( !class_exists('Core_Drivers_mysqli', false) || !class_exists('mysqli', false) )){
            trigger_error('Error: You have selected to use MySQLi, the interface for this Driver dosen\'t exist.', E_USER_ERROR);
        }

        if($this->dbSettings['driver'] == 'mysql' && ( !class_exists('Core_Drivers_mysql', false) || !function_exists('mysql_connect') )){
            trigger_error('Error: You have selected to use MySQL, the interface for this Driver dosen\'t exist.', E_USER_ERROR);
        }

        return false;
    }

    public function __destruct(){
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

    public function queryBuilder(){
        $driver = $this->driver;
        if( !in_array($this->driver, array('mysql', 'mysqli', 'pdomysql')) ){
            $driver = 'mysqli';
        }

        $classname = 'Core_Drivers_'.strtolower($driver).'QueryBuilder';
        return new $classname;
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

    public function fetchAll($query, $key=false){
        $this->query($query);
            if(!$this->affectedRows()){
                return array();
            }

        $line = $this->results($key);
        $this->freeResult();
        return $line;
    }

    public function fetchLine($query){
        if(!is_string($query)){ return false; }

        if(strpos($query, ' LIMIT 1') === false){
            $query = $query.' LIMIT 1';
        }

        $line = $this->fetchAll($query);
        return (is_array($line) && count($line) ? $line[0] : false);
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
                ->where(sprintf('TABLE_NAME LIKE "%s"', $table))
                    ->andWhere(sprintf('TABLE_SCHEMA = %s', $this->config('db', 'database')))
            ->build();

        $this->query($query);
            if(!$this->affectedRows()){
                return false;
            }

        $line = $this->results();
        $line = end($line);

        $this->freeResult();
        return $line['AUTO_INCREMENT'];
    }


/**
 * Just borrowed Joomla's Quotey stuff, does what we want XD
 *
 **/

    public function quote($text, $escape = true) {
        return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
    }

    public function quoteName($name, $as = null) {
        if (is_string($name)) {
            $quotedName = $this->quoteNameStr(explode('.', $name));

            $quotedAs = '';
            if (!is_null($as)) {
                settype($as, 'array');
                $quotedAs .= ' AS ' . $this->quoteNameStr($as);
            }

            return $quotedName . $quotedAs;
        } else {
            $fin = array();

            if (is_null($as)) {
                foreach ($name as $str) {
                    $fin[] = $this->quoteName($str);
                }
            } elseif (is_array($name) && (count($name) == count($as))) {
                for ($i = 0; $i < count($name); $i++) {
                    $fin[] = $this->quoteName($name[$i], $as[$i]);
                }
            }

            return $fin;
        }
    }

    protected function quoteNameStr($strArr) {
        $parts = array();
        $q = $this->nameQuote;

        foreach ($strArr as $part) {
            if (is_null($part)) { continue; }

            if (strlen($q) == 1) {
                $parts[] = $q . $part . $q;
            } else {
                $parts[] = $q{0} . $part . $q{1};
            }
        }

        return implode('.', $parts);
    }
}

?>