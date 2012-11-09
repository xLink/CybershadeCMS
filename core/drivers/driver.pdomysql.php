<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class driver_pdomysql extends coreSQL implements baseSQL{

    public static function getInstance($name=null, $options=array()){
        $c = __CLASS__;

        if( !isset(self::$_classes['database'][$c]) ){
            self::$_instances['database'][$c] = new self(null, $options);
        }

        return self::$_instances['database'][$c];
    }

    public function connect(){
        if( $this->dbSettings['persistent'] === true ){
            $host = $this->dbSettings['host'];
            $username = $this->dbSettings['username'];
            $password = $this->dbSettings['password'];

            $this->DBH = new PDO(
                sprintf('mysql:host=%s', $host),
                $username,
                $password,
                array( PDO::ATTR_PERSISTENT => true )
            );

            if( $this->DBH->connect_error != null ){
                trigger_error('Database Connection: Connect Error');
                return false;
            }
        } else {
            $this->DBH = new PDO(
                sprintf('mysql:host=%s', $host),
                $username,
                $password
            );

            if( $this->DBH->connect_error != false ){
                trigger_error('Database Connection: Connect Error');
                return false;
            }
        }

        if( $this->selectDB($this->dbSettings['database']) === false ){
            trigger_error('Cannot select database - check user permissions.<br />', E_USER_ERROR);
            return false;
        }

        $this->registerPrefix('#__', $this->dbSettings['prefix']);

        $this->query('SET CHARACTER SET utf8;');
        $this->query('SET GLOBAL innodb_flush_log_at_trx_commit = 2;');

        return true;
    }

    public function selectDB( $database ){
        return $this->DBH->select_db($database);
    }

    public function disconnect(){
        $this->freeResult();

        if( $this->dbSettings['persistent'] === false ){
            $this->DBH->close();
            return true;
        }

        return false;
    }


    public function getError(){
        if(mysql_errno($this->DBH) != 0){
            return sprintf(' (%d) %s ', mysql_errno($this->DBH), mysql_error($this->DBH));
        }
        return false;
    }

    public function escape( $string ){
        // Shouldn't this be PDO::prepare() ?
        return $this->DBH->real_escape_string();
    }


    public function freeResult(){

        if(isset($this->results) && is_resource($this->results)){
            $this->results->close();
            unset($this->results);
        }
    }


    /**
     //
     //-- Need to continue from below this line
     //
     */

    public function query($query){
        $debug['query_start'] = microtime(true);

        //apply the prefix swapping mech
        $query = $this->_query = $this->_replacePrefix($query);
        //exec the query and cache it
        $this->results = $this->DBH->query($query) or trigger_error('MySQL Error:<br />'.dump($query, 'Query::'.$this->getError()), E_USER_ERROR);


        if( cmsDEBUG || User::$IS_ADMIN ){
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

}
?>