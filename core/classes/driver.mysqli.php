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
class driver_mysqli extends core_SQL implements base_SQL{
            
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

    public function selectDB($database){
        return $this->DBH->select_db($database);
    }

    public function connect(){
        // if we have persistent enabled, we'll try that first
        if($this->dbSettings['persistent'] === true){
            $this->DBH = new mysqli('p:'.$this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password']);
            if($this->DBH->connect_error != null){
                trigger_error('Database Connection: Connect Error ('.$mysqli->connect_errno.') '.$mysqli->connect_error, E_USER_ERROR);
                return false;
            }
        }

        //persistent is off, lets try and connect normally
        if($this->dbSettings['persistent'] === false){
            $this->DBH = new mysqli($this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password']);
        }

            //we havent got a resource we need to bomb out now
            if($this->DBH->connect_error != null){
                trigger_error('Cannot connect to the database - verify username and password.<br />', E_USER_ERROR);
                return false;
            }

        //select the DB
        if($this->selectDB($this->dbSettings['database']) === false){
            trigger_error('Cannot select database - check user permissions.<br />', E_USER_ERROR);
            return false;
        }

        $this->registerPrefix('#__', $this->dbSettings['prefix']);

        //and carry on
        return true;
    }

    public function disconnect(){
        $this->freeResult();
        if($this->dbSettings['persistent'] === false){
            $this->DBH->close();
            return true;
        }

        return false;
    }

    public function getError(){
        return mysqli_error($this->DBH);
    }

/**
  //
  //-- Core Functionality
  //
**/

    public function escape($string){
        return $this->DBH->real_escape_string($string);
    }

    public function freeResult(){
        
        if(isset($this->results) && is_resource($this->results)){
            $this->results->close();
            unset($this->results);
        }
    }


    public function query($query, $args=array()){
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
        $this->results = $this->DBH->query($this->_query) or trigger_error('MySQL Error:<br />'.dump($query, 'Query::'.$this->getError()), E_USER_ERROR);
        $this->queries[md5($query)] = $this->results;

        return $this->results;
    }

    public function results(){
        if($this->results === false){ return false; }

        $results = array();
        while ($row = $this->results->fetch_array(MYSQLI_ASSOC)){
            $results[] = $row;
        }
        return $results;
    }


    public function affectedRows(){
        return $this->DBH->affected_rows;
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

    public function getTable($query){
        $this->query($query);

        if($this->results){
            $table = array();
            while($line = $this->results->fetch_assoc()){
                $table[] = $line;
            }
            $this->results->free_result();
            unset($this->results);
            return $table;
        }

        return false;
    }

    public function getLine($table, $clause=null, $args=array()){

    }

    public function getValue($table, $field, $clause=null){

    }

    public function getCount($table, $clause=null){

    }

    public function getColumns($table){

    }

    public function insertRow($table, $array){

    }

    public function updateRow($table, $array, $clause){

    }

    public function deleteRow($table, $clause){

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