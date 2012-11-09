<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
* MySQLi Driver support for the SQLBase
*
* @version      1.0
* @since        1.0.0
* @author       Dan Aldridge
*/
class driver_mysqli extends coreSQL implements baseSQL{

    /**
     * Returns or Sets up Instance for this class.
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       array    $config
     *
     * @return      array
     */
    public static function getInstance($name=null, $options=array()){
        $c = __CLASS__;

        if (!isset(self::$_classes['database'][$c])){
            self::$_instances['database'][$c] = new self($options);
        }

        return self::$_instances['database'][$c];
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
            $this->DBH = new mysqli('p:'.$this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password'], $this->dbSettings['database'], (int)$this->dbSettings['port'] );
            if($this->DBH->connect_error != null){
                trigger_error('Database Connection: Connect Error ('.$mysqli->connect_errno.') '.$mysqli->connect_error, E_USER_ERROR);
                return false;
            }
        }

        //persistent is off, lets try and connect normally
        if($this->dbSettings['persistent'] === false){
            $this->DBH = new mysqli($this->dbSettings['host'], $this->dbSettings['username'], $this->dbSettings['password'], $this->dbSettings['database'], (int)$this->dbSettings['port'] );
        }

            //we havent got a resource we need to bomb out now
            if($this->DBH->connect_error != null){
                trigger_error('Cannot connect to the database - verify username and password.<br />', E_USER_ERROR);
                return false;
            }

        $this->registerPrefix('#__', $this->dbSettings['prefix']);

        $this->query('SET CHARACTER SET utf8;');
        $this->query('SET GLOBAL innodb_flush_log_at_trx_commit = 2;');

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
        if($this->DBH->errno != 0){
            return sprintf(' (%d) %s ', $this->DBH->errno, $this->DBH->error);
        }
        return false;
    }

/**
  //
  //-- Core Functionality
  //
**/

    public function queryBuilder(){
        return new mysql_queryBuilder();
    }


    public function escape($string){
        return $this->DBH->real_escape_string($string);
    }

    public function freeResult(){

        if(isset($this->results) && is_resource($this->results)){
            $this->results->close();
            unset($this->results);
        }
    }


    public function query($query){
        $debug['query_start'] = microtime(true);

        //apply the prefix swapping mech
        $query = $this->_query = $this->_replacePrefix($query);

        //exec the query and cache it
        $this->results = $result = $this->DBH->query($query);

        if( cmsDEBUG || User::$IS_ADMIN ){
            $backtrace = debug_backtrace();
            $callee = $backtrace[1];

            $debug['query']         = $query;
            $debug['method']        = $callee['function'];
            $debug['args']          = json_encode($callee['args']);
            $debug['file']          = $callee['file'];
            $debug['line']          = $callee['line'];
            $debug['affected_rows'] = $this->affectedRows();
            $debug['query_end']     = microtime(true);
            $debug['time_taken']    = substr(($debug['query_end'] - $debug['query_start']), 0, 7);

            $this->totalTime        += $debug['time_taken'];
            $debug['total_time']    = $this->totalTime;
            $debug['status']        = 'ok';
            $debug['error']         = '';

        }
        $this->debug[] = $debug;

        if( $result === false ){
            $this->recordMessage(mysql_error(), 'WARNING');
        }

        return $this->results;
    }

    public function results($key){
        if($this->results === false){ return false; }

        $results = array();
        while ($row = $this->results->fetch_array(MYSQLI_ASSOC)){
            if(!is_empty($key) && array_key_exists($key, $row)){
                $results[$row[$key]] = $row;
            }else{
                $results[] = $row;
            }
        }
        return $results;
    }


    public function affectedRows(){
        return $this->DBH->affected_rows;
    }

    public function recordMessage($message, $mode){
        if(!$this->dbSettings['debug']){
            return false;
        }

        $backtrace = debug_backtrace();
        $callee = $backtrace[1];

        $a = array($this->_query, $callee);
        trigger_error('MySQL Error:');
        echo dump($a, 'Query::'.$this->getError());

        $max = count($this->debug);

        $this->debug[($max - 1)]['status'] = 'error';
        $this->debug[($max - 1)]['error'] = $this->getError();

    }

}

?>