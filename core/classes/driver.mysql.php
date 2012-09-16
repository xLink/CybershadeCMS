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
* @author       Daniel Aldridge
*/
class driver_mysql extends core_SQL implements base_SQL{

    /**
     * Returns or Sets up Instance for this class.
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Aldridge
     *
     * @param       array    $config
     *
     * @return      array
     */
    public static function getInstance($name=null, $options=array()){
        $c = __CLASS__;

        if (!isset(self::$_classes['database'][$c])){
            self::$_instances['database'][$c] = new self($name, $options);
        }

        return self::$_instances['database'][$c];
    }

/**
  //
  //-- Connection Functionality
  //
**/
    /**
     * Select new DB
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Aldridge
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function selectDB($db){
        return mysql_select_db($db, $this->DBH);
    }

    /**
     * Open a connection to MySQL & Select DB
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Aldridge
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

        $this->query('SET CHARACTER SET utf8;');
        $this->query('SET GLOBAL innodb_flush_log_at_trx_commit = 2;');

        //and carry on
        return true;
    }

    /**
     * Disconnect
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Aldridge
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function disconnect(){
        $this->freeResult();
        if($this->dbSettings['persistent'] === false){
            mysql_close($this->DBH);
        }
    }

    /**
     * Error Handler for
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Aldridge
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function getError(){
        $backtrace = debug_backtrace();
        $callee = next($backtrace);
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

    public function query($query){
        $debug['query_start'] = microtime(true);

        //apply the prefix swapping mech
        $query = $this->_query = $this->_replacePrefix($query);

        //exec the query and cache it
        $this->results = mysql_query($query, $this->DBH) or trigger_error('MySQL Error:<br />'.dump($query, 'Query::'.$this->getError()), E_USER_ERROR);


        if($this->dbSettings['debug']){
            $backtrace = debug_backtrace();
            $callee = next($backtrace);

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
        }
        $this->debug[] = $debug;
        return $this->results;
    }

    public function results(){
        if(!is_resource($this->results) || $this->results === false){ return false; }


        if($this->affectedRows() == 0){
            return false;
        }

        if($this->affectedRows() != 1){
            $results = array();
            while($row = mysql_fetch_assoc($this->results)){
                $results[] = $row;
            }
            return $results;
        }

        return array(mysql_fetch_assoc($this->results));
    }

    public function affectedRows(){
        return mysql_affected_rows($this->DBH);
    }

}

?>