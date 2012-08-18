<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
* MySQL Driver support for the SQLBase
*
* @version      1.0
* @since        1.0.0
* @author       xLink
*/
class driver_mysql extends core_SQL implements base_SQL{

    /**
     * Returns or Sets up Instance for this class.
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public static function getInstance($options=array()){
        if (!isset(self::$_instances['database'])){
            $c = __CLASS__;
            self::$_instances['database'] = new self($options);
        }
        return self::$_instances['database'];
    }

/**
  //
  //-- Connection Functionality
  //
**/

    public function selectDB($db){
        return mysql_select_db($db, $this->DBH);
    }

    /**
     * Try and connect
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function connect(){

        // if we have persistent enabled, we'll try that first
        if($this->dbSettings['persistent'] === true){
            $this->DBH = @mysql_pconnect($this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password']);
            if($this->DBH === false){
                $this->dbSettings['persistent'] = false;
            }
        }

        //persistent is off, lets try and connect normally
        if($this->dbSettings['persistent'] === false){
            $this->DBH = @mysql_connect($this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password']);
        }

            //we havent got a resource we need to bomb out now
            if($this->DBH === false){
                trigger_error('Cannot connect to the database - verify username and password.<br />', E_USER_ERROR);
                return false;
            }

        //select the DB
        if($this->selectDB($this->dbSettings['database']) === false){
            trigger_error('Cannot select database - check user permissions.<br />', E_USER_ERROR);
            return false;
        }

        $this->registerPrefix('#__', $this->dbSettings['prefix']);

        $this->query('SET GLOBAL innodb_flush_log_at_trx_commit = 2;');

        //and carry on
        return true;
    }

    public function disconnect(){
        $this->freeResult();
        if($this->dbSettings['persistent'] === false){
            mysql_close($this->DBH);
        }
    }

    public function getError(){
        return ' ('. mysql_errno($this->DBH) .') '. mysql_error($this->DBH);
    }

/**
  //
  //-- Core Functionality
  //
**/

    public function escape($string){
        return mysql_real_escape_string($string, $this->DBH);
    }

    public function freeResult(){
        if(isset($this->results) && is_resource($this->results)){
            mysql_free_result($this->results);
            unset($this->results);
        }
    }

    public function query($query, $args=array(), $log=false){
        $this->freeResult();

        // if $query is true, then throw us into QueryBuilder Mode :D
        if($query === true){ return new queryBuilder(); }

        //if we already have this query cached then lets roll
        if(isset($this->queries[md5($query)])){
            $this->results = $this->queries[md5($query)];
            return $this->queries[md5($query)];
        }

        $this->_query = $this->_replacePrefix($query);

        //exec the query and cache it
        $this->results = mysql_query($this->_query, $this->DBH) or trigger_error('MySQL Error:<br />'.dump($query, 'Query::'.$this->getError()), E_USER_ERROR);
        $this->queries[md5($query)] = $this->results;
        
        return $this->results;
    }

    public function results(){
        if($this->results === false){ return false; }

        return mysql_fetch_assoc($this->results);
    }

    public function affectedRows(){
        return mysql_affected_rows($this->DBH);
    }


/**
  //
  //-- Extra Functionality
  //
**/

    public function index($db){

        $query = $this->query('SHOW TABLES');
        $results = $this->results($query);
            if(!count($results)){ return false; }

        foreach($results as $key => $value) {
            if (!isset($value['Tables_in_'.$db])){ continue; }

            $this->query('REPAIR TABLE '.$value['Tables_in_'.$db]);
            $this->query('OPTIMIZE TABLE '.$value['Tables_in_'.$db]);
            $this->query('FLUSH TABLE '.$value['Tables_in_'.$db]);
        }

        return true;
    }

    public function getTable($query){
        $this->query($query);

        if(is_resource($this->results)){
            $table = array();
            while($line = mysql_fetch_assoc($this->results)){
                $table[] = $line;
            }
            mysql_free_result($this->results);
            unset($this->results);
            return $table;
        }

        return false;
    }

    public function getColumns($table){
    }


    public function getInfo($table, $clause=null, $log=false){

    }

    public function getValue($table, $field, $clause=null, $log=false){

    }

    public function getLine($query, $args=array(), $log=false){

    }

    public function insertRow($table, $array, $log=false){

    }

    public function updateRow($table, $array, $clause, $log=false){

    }

    public function deleteRow($table, $clause, $log=false){

    }

    public function getAI($table){

    }

    public function recordMessage($message, $mode=false){

    }

    public function recordLog($query, $log){

    }

    public function recordError($message, $fileInfo){

    }










}

?>