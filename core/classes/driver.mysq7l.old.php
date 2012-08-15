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
class driver_mysql extends coreObj implements base_SQL{

    private $db = array();

    /**
     * Sets up a new MySQL Class
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       array    $config
     *
     * @return      bool
     */
    public function setup($config=array()) {
        if(is_empty($config)){ return false; }

        $this->db = array(
            'host'      => doArgs('host',       '', $config),
            'username'  => doArgs('username',   '', $config),
            'password'  => doArgs('password',   '', $config),
            'database'  => doArgs('database',   '', $config),
            'prefix'    => doArgs('prefix',     '', $config),
        );


        return true;
    }

    public static function getInstance($options=array()){
        $method = __FUNCTION__;
        return parent::$method();
    }

    /**
     * Sets up a connection to the database
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *  
     * @param       bool     $debug
     * @param       bool     $logging
     *  
     * @return      bool
     */
    public function connect($persistent=false, $debug=false, $logging=false) {
        $this->failed     = false;
        $this->debug      = $debug;
        $this->logging    = $logging;
        $this->persistent = $persistent;

        if($this->persistent === true) {
            $this->link_id = @mysql_pconnect($this->db['host'], $this->db['username'], $this->db['password']);
            if($this->link_id == false) {
                $this->persistent = false;
            }
        }

        if($this->persistent === false) {
            $this->link_id = mysql_connect($this->db['host'], $this->db['username'], $this->db['password']);
        }

        if(!$this->link_id){
            $this->errorMsg = 'Cannot connect to the database - verify username and password.';
            return false;
        }

        $this->selectDb($this->db['database']);
        if($this->failed){
            $this->errorMsg = 'Cannot select database - check user permissions.';
            return false;
        }

        if($this->persistent == false && !defined('NO_DB')) {
            $this->recordMessage('CMS is not using a persistent connection with the database.', 'WARNING');
        }

        $this->query('SET GLOBAL innodb_flush_log_at_trx_commit = 2;');

        unset($this->db['password']);
        return true;
    }

    /**
     * Disconnects the current connection
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     */
    public function disconnect(){
        $this->freeResult();
        if($this->persistent == false){
            mysql_close($this->link_id);
        }

        if($this->debug){
            $queries = 0;
            $queries_failed = 0;
            foreach($this->debugtext as $row) {
                if($row['time'] != '---------') {
                    $this->link_time += $row['time'];
                    $queries++;
                }
                if($row['status'] == 'error'){
                    $queries_failed++;
                }
            }

            $this->queries_executed = $queries + $queries_failed;
            $this->debugtext[] = array(
                'query'  => '<span style="color:green"><b>REPORT:</b></span> {'.$this->queries_executed.'} queries executed, {'.$queries.'} succeded and {'.$queries_failed.'} failed in '.substr($this->link_time, 0, 7).' seconds',
                'time'   => '---------',
                'status' => 'ok',
            );
        }
    }

    /**
     * Escapes a string ready for the database
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *  
     * @param       mixed     $string
     *  
     * @return      mixed
     */
    public function escape($string){
        if(function_exists('mysql_real_escape_string') && $this->link_id) {
            if(is_array($string)){
                recursiveArray($string, 'mysql_real_escape_string');
                return $string;
            }

            return mysql_real_escape_string($string);
        } else {
            if(is_array($string)){
                recursiveArray($string, 'mysql_escape_string');
                return $string;
            }

            return mysql_escape_string($string);
        }
    }

    /**
     * Retreives the last error reported
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @return      string
     */
    public function getError() {
        return mysql_error($this->link_id);
    }

    /**
     * Selects the database for use
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $db
     *
     * @return      bool
     */
    public function selectDb($db) {
        return mysql_select_db($db, $this->link_id) or $this->recordMessage(null, 'ERROR');
    }

    /**
     * Returns the names of columns from a table structure
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string $table
     *
     * @return      array
     */
    public function getColumns($table){
        $columns = $this->getTable('SHOW COLUMNS FROM `#__%s`', array($table));
            if(!$columns || (is_array($columns) && !count($columns))){
                $this->setError('Query failed. SQL: '.mysql_error());
                return false;
            }

        $return = array();
        foreach($columns as $column){
            $return[] = $column['Field'];
        }

        return $return;
    }

    /**
     * Selects the database for use
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     */
    public function freeResult() {
        if(isset($this->results) && is_resource($this->results)) {
            mysql_free_result($this->results);
            unset($this->results);
        }
    }

    /**
     * Gets the specified table prefix
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string    $mode
     *
     * @return      string
     */
    public function prefix($mode='') {
        if(isset($this->prefix[$mode])){
            return $this->prefix[$mode];
        }

        if(is_empty($mode) || $mode==0){
            return $this->db['prefix'];
        }

        return false;
    }

    /**
     * Adds a new prefix to the collection, useful for bridging projects
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $mode
     * @param       string     $prefix
     *
     * @return      bool
     */
    public function addPrefix($mode, $prefix){
        if($mode == 0){
            return false;
        }

        $this->prefix[$mode] = $prefix;

        return true;
    }

    /**
     * Prepares the query for use, escapes parameters, sets table prefix.
     *
     * @version     1.3
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $query
     * @param       string  $arg1
     * @param       string  $arg2
     * @param       ...
     *
     * @return      string
     */
    public function prepare(){
        //grab the functions args
        $args = func_get_args();

        //first arg is the query
        $query = array_shift($args);

        //replace #__ with the table prefix
        $query = str_replace('$P', $this->prefix(), $query);
        $query = str_replace('#__', $this->prefix(), $query);


        //return thru sprintf
        return vsprintf($query, ( is_array( $args[0] ) ? $args[0] : $args ));
    }


    /**
     * Prepares the clauses, this saves the need for manual calling
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       array   $clause
     *
     * @return      mixed
     */
    private function autoPrepare($clause){
        if(!is_array($clause) || is_empty($clause)){
            return $clause;
        }
        return call_user_func_array(array($this, 'prepare'), $clause);
    }

    /**
     * Queries the database
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $query
     * @param       string     $log
     *
     * @return      resource
     */
    public function query($query, $args=array(), $log=false) {
        $this->freeResult();

        $this->query_time = microtime(true);

        if($log){ $this->recordLog($query, $log); }

        $this->query = $this->prepare($query, $args);
        $this->results = mysql_query($this->query, $this->link_id) or $this->recordMessage(mysql_error(), 'WARNING');

        if($this->debug){
            $a = debug_backtrace();
            $file = $a[1];
            if(isset($file['args'])){
                foreach($file['args'] as $k => $v){
                    $file['args'][$k] = (is_array($v) ? json_encode($v) : $v);
                }
            }

            $query = secureMe($this->query);
            $pinpoint = '<br /><div class="content padding">'.
                            '<strong>'.realpath($file['file']).'</strong> @ <strong>'.$file['line'].'</strong>'.
                            '// Affected '.mysql_affected_rows().' rows.. <br /> '.
                            $file['function'].'(<strong>\''.(isset($file['args']) ? secureMe(implode('\', \'', $file['args'])) : null).'\'</strong>);'.
                        '</div>';
            $this->debugtext[] = array('query' => $query.$pinpoint, 'time' => substr((microtime(true) - $this->query_time), 0, 7), 'status' => 'ok');
        }else{
            $this->debugtext[] = array('query' => $query, 'time' => null, 'status' => 'ok');
        }


        return $this->results;
    }

    /**
     * Gets a row count from a table
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $table
     * @param       string      $clause
     * @param       string      $log
     *
     * @return      int
     */
    public function getInfo($table, $clause=null, $log=false){
        $args = array();

        $statement = 'SELECT COUNT(*) as count FROM `#__%s`';
        $args[] = $table;
        if(!is_empty($clause)){
            $statement .= ' WHERE %s';
            $args[] = $this->autoPrepare($clause);
        }

        $line = $this->getLine($statement, $args, $log);
        return $line['count'];
    }

    /**
     * Gets a row count from a table
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $table
     * @param       string     $field
     * @param       string     $clause
     * @param       string     $log
     *
     * @return      string
     */
    public function getValue($table, $field, $clause=null, $log=false){
        $args = array();

        $statement = 'SELECT %1$s FROM `#__%2$s`';
        $args[] = $table;
        if(!is_empty($clause)){
            $statement .= ' WHERE %s';
            $args[] = $this->autoPrepare($clause);
        }
        $statement .= ' LIMIT 1;';

        $line = $this->getLine($statement, $args, $log);
        return $line[$field];
    }

    /**
     * Gets a row from a table
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $query
     * @param       array       $args
     * @param       string      $log
     *
     * @return      array
     */
    public function getLine($query, $args=array(), $log=false) {
        $this->query($query, $args, $log);

        if(!is_resource($this->results)) {
            $this->recordMessage('getLine: ('.$query.')', 'ERROR');
        } else {
            $line = mysql_fetch_assoc($this->results);
            $this->freeResult();
            return $line;
        }

        return false;
    }

    /**
     * Returns query results in the form of an array
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $query
     * @param       array       $args
     * @param       string      $log
     *
     * @return      array
     */
    public function getTable($query, $args=array(), $log=false) {
        $this->query($query, $args, $log);

        if(!is_resource($this->results)) {
            $this->recordMessage('getTable: ('.$query.')', 'ERROR');
        } else {
            $table = array();
            while($line = mysql_fetch_assoc($this->results)) {
                $table[] = $line;
            }
            $this->freeResult();
            return $table;
        }

        return false;
    }

    /**
     * Inserts a row into specified table
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $query
     * @param       array       $array
     * @param       string      $log
     *  
     * @return      int
     */
    public function insertRow($table, $array, $log=false){
        if(is_empty($array)){ return false; }

        $comma = null;
        $listOfValues = null;
        $listOfElements = null;

        foreach($array as $elem => $value) {
            if($value === null){
                $listOfValues .= $comma .'null';
            }else{
                $listOfValues .= $comma .'\''. $this->escape((string)$value) .'\'';
            }

            $listOfElements .= $comma .'`'. $elem .'`';
            $comma = ', ';
        }

        $query = 'INSERT HIGH_PRIORITY INTO `#__%1$s` (%2$s) VALUES (%3$s)';
        $this->query($query, array($table, $listOfElements, $listOfValues), $log);

        return mysql_insert_id($this->link_id);
    }

    /**
     * Updates a table with the array of values
     *
     * @version      1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $query
     * @param       array      $array
     * @param       string     $clause
     * @param       string     $log
     *
     * @return      int
     */
    public function updateRow($table, $array, $clause, $log=false){
        if(is_empty($array)){ return false; }

        $vars = null;
        foreach($array as $index => $value){
            if($value === null){
                $vars .= '`'.$index.'`=null, ';
            }else{
                $vars .= '`'.$index.'`="'.$this->escape($value).'", ';
            }
        }

        $query = 'UPDATE `#__%s` SET %s WHERE %s';
        $this->query($query, array($table, substr($vars, 0, -2), $this->autoPrepare($clause)), $log);

        return mysql_affected_rows($this->link_id);
    }

    /**
     * Deletes row(s) form a table
     *
     * @version     1.1
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $query
     * @param       string      $clause
     * @param       string      $log
     *
     * @return      array
     */
    public function deleteRow($table, $clause, $log=false){
        $query = 'DELETE FROM `#__%s` WHERE %s';
        $this->query($query, array($table, $this->autoPrepare($clause)), $log);

        return mysql_affected_rows($this->link_id);
    }

    /**
     * Records a message in the footer debug
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $message
     * @param       string     $mode
     */
    public function recordMessage($message, $mode=false) {
        $this->failed = true;

        $a = debug_backtrace();
        $file = $a[1];
        if(isset($file['args'])){
            foreach($file['args'] as $k => $v){
                $file['args'][$k] = (is_array($v) ? serialize($v) : $v);
            }
        }

        if($mode != 'INFO'){ $this->recordError($message, $file['file'].':'.$file['line']); }

        if(!$this->debug) { return; }
        $message = secureMe($message);
        $pinpoint = '<br /><div class="content padding"><strong>'.realpath($file['file']).'</strong> @ <strong>'.$file['line'].
                        '</strong> // Affected '.mysql_affected_rows().' rows.. <br /> '.$file['function'].'(<strong>\''.
                        (isset($file['args']) ? secureMe(implode('\', \'', $file['args'])) : null).'\'</strong>); </div>';

        if($mode == 'WARNING'){
            $this->debugtext[] = array(
                'query'  => '<div class="padding"><span style="color:orange"><b>WARNING:</b></span> '.$message.$pinpoint.'</div>',
                'time'   => '---------',
                'status' => 'warning'
            );
        } else if($mode == 'ERROR') {
            $max = count($this->debugtext);
            $this->debugtext[$max - 1] = array(
                'query'  => '<div class="padding"><span style="color:red"><b>ERROR:</b></span> '.$message.$pinpoint.'</div>',
                'time'   => '---------',
                'status' => 'error'
            );
        } else {
            $this->debugtext[] = array(
                'query'  => '<div class="padding"><span style="color:blue"><b>INFO:</b></span> '.$message.'</div>',
                'time'   => '---------',
                'status' => 'info'
            );
        }
    }

    /**
     * Records a sql query in the database with a log message
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string     $query
     * @param       string     $log
     *
     * @return      bool
     */
    public function recordLog($query, $log) {
        if(!$this->logging){ return false; }

        $info['uid']         = (User::$IS_ONLINE ? $this->objUser->grab('id') : '0');
        $info['username']    = (User::$IS_ONLINE ? $this->objUser->grab('username') : 'Guest');
        $info['description'] = $log;
        $info['query']       = $query;
        $info['refer']       = secureMe($_SERVER['HTTP_REFERER']);
        $info['date']        = time();
        $info['ip_address']  = User::getIP();

        return $this->insertRow('logs', $info, false);
    }


    /**
     * Records a sql error in the database for review
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $message
     * @param       string      $fileInfo
     *
     * @return      bool
     */
    public function recordError($message, $fileInfo) {
        if(is_empty($this->query)){ return false; }

        $error = mysql_error();
            if(is_empty($error) || $error == $this->lastError){ return false; }
        $this->lastError = $error;

        if(!is_file(cmsROOT.'cache/ALLOW_LOGGING')){ return false; }

        $info['uid']      = (User::$IS_ONLINE ? $this->objUser->grab('id') : '0');
        $info['date']     = time();
        $info['query']    = $this->query;
        $info['page']     = $this->config('global', 'fullPath');
        
        $vars             = array('get' => $_GET, 'post' => $_POST);
        $info['vars']     = serialize($vars);
        
        $info['error']    = secureMe($error);
        $info['lineInfo'] = secureMe($fileInfo);

        return $this->insertRow('sqlerrors', $info, false);
    }

    /**
     * Gets the Auto Increment value from the Table
     *
     * @version     1.0
     * @since       1.0.0
     * @author      xLink
     *
     * @param       string      $table
     *
     * @return      string
     */
    public function getAI($table) {
        $query = $this->getLine($this->prepare('SHOW TABLE STATUS LIKE `#__'.$table.'`"'));
        return $query['Auto_increment'];
    }

}

?>