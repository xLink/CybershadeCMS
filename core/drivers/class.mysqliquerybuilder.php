<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * SQL Query Builder
 *
 * @version      1.0
 * @since        1.0.0
 * @author       Dan Aldridge
 */
class Core_Drivers_mysqliQueryBuilder extends Core_Classes_coreObj implements Core_Classes_baseQueryBuilder{

    private $queryType = '';
    private $_fields                 = array();
    private $_values                 = array();
    private $_tables                 = array();

    private $_join                   = array();
    private $_using                  = '';
    private $_on                     = array();

    private $_where                  = array();
    private $_limit                  = 0;
    private $_offset                 = 0;

    private $_orderBy                = array();
    private $_order                  = 'ASC';
    private $_groupBy                = array();

    private $_charset                = '';
    private $_engine                 = '';
    private $_AI                     = 0;
    private $_tableConditional      = '';
    private $_columns                = array();

/**
  //
  //-- Query Type Functions
  //
**/
    public function select(){
        $this->setQueryType('select');

        $args = $this->_getArgs(func_get_args());
            foreach($args as $key => $arg){
                $this->addField($arg, $key);
            }

        return $this;
    }

    public function insertInto($table){
        $this->setQueryType('insert');
        $this->_tables[] = $table;

        return $this;
    }

    public function replaceInto($table){
        $this->setQueryType('replace');
        $this->_tables[] = $table;

        return $this;
    }

    public function deleteFrom($table){
        $this->setQueryType('delete');
        $args = $this->_getArgs(func_get_args());
        $this->_tables = $args;

        return $this;
    }

    public function update($table){
        $this->setQueryType('update');
        $this->_tables = array($table);

        return $this;
    }

    public function createTable( $table, $conditional = '' ) {
        $this->_tableConditional = $conditional;
        $this->setQueryType('create_table');
        $this->_tables = array($table);

        return $this;
    }

    public function dropTable( $table, $conditional = '' ) {
        $this->_tableConditional = $conditional;
        $this->setQueryType('drop_table');
        $this->_tables = array($table);

        return $this;
    }
/**
  //
  //-- Helper Functions
  //
**/

    /**
     * Set the Charset for various daatabase actions
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $charset       Character Set recognised by the system
     *
     */
    public function setCharset( $charset ) {
        if( !is_string( $charset ) ) {
            trigger_error( 'Error: Wrong datatype', E_USER_ERROR );
        }

        $this->charset = $charset;

        return $this;
    }

    /**
     * Set Auto Increment when altering or creating tables
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int  $AI       Auto Increment ID
     *
     */
    public function setAI( $AI ) {
        if( !is_number( $AI ) ) {
            trigger_error( 'Error: Wrong datatype', E_USER_ERROR );
        }

        $this->AI = $AI;

        return $this;
    }

    /**
     * Setup the columns when altering or creating a table
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   array  $columns       Array of columns with properties in a child array on each item
     *
     */
    public function setColumns( $columns ) {
        if( !is_array( $columns ) ) {
            trigger_error( 'Error: Wrong datatype', E_USER_ERROR );
        }

        $this->_columns = $columns;

        return $this;
    }

    /**
     * Setup the Engine type
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $type       Type of the MySQL Engine to use
     *
     */
    public function setEngine( $type ) {
        if( !is_string( $type ) ) {
            trigger_error( 'Error: Wrong datatype', E_USER_ERROR );
        }

        $this->engineType = $type;

        return $this;
    }

/**
  //
  //-- Core Functions
  //
**/

    public function from($tables){
        if($this->queryType != 'SELECT'){
            trigger_error('Error: Only SELECT operators.', E_USER_ERROR);
        }

        $this->_tables = $this->_buildTables($tables);

        return $this;
    }

        public function addField($field, $key=null){
            if(!is_string($field)){
                trigger_error('addField; typeOf $field != "string"', E_USER_ERROR);
            }

            if(!in_array($field, $this->_fields)){
                if(empty($key)){
                    $this->_fields[] = $field;
                }else{
                    $this->_fields[$key] = $field;
                }
            }
            return $this;
        }

    public function where($where){
        return $this->_addWhereOn(func_get_args(), '', 'where');
    }

        public function andWhere($where){
            return $this->_addWhereOn(func_get_args(), 'AND', 'where');
        }

        public function orWhere($where){
            return $this->_addWhereOn(func_get_args(), 'OR', 'where');
        }

    public function join($table){
        $this->_join[] = sprintf('JOIN %s', $this->_buildTables($table));

        return $this;
    }

        public function leftJoin($table){
            $this->_join[] = sprintf('LEFT JOIN %s', $this->_buildTables($table));

            return $this;
        }

        public function rightJoin($table){
            $this->_join[] = sprintf('RIGHT JOIN %s', $this->_buildTables($table));

            return $this;
        }

        public function using($field){
            $this->_using = $field;

            return $this;
        }

    public function on($on){
        return $this->_addWhereOn(func_get_args(), '', 'on');
    }

        public function andOn($on){
            return $this->_addWhereOn(func_get_args(), 'AND', 'on');
        }

        public function orOn($on){
            return $this->_addWhereOn(func_get_args(), 'AND', 'on');
        }

    public function args(){
        if (!in_array($this->queryType, array('INSERT', 'UPDATE', 'REPLACE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());

        $this->fields(array_keys($args));
        $this->values(array_values($args));

        return $this;
    }

    public function fields($fields) {
        if (!in_array($this->queryType, array('INSERT', 'UPDATE', 'REPLACE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());
        $this->_fields = $args;

        return $this;
    }

    public function values($values) {
        if (!in_array($this->queryType, array('INSERT', 'UPDATE', 'REPLACE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());
            if (count($args) != count($this->_fields)) {
                trigger_error('Error: Number of values has to be equal to the number of fields.', E_USER_ERROR);
            }

        if ( in_array($this->queryType, array( 'INSERT', 'REPLACE' ) ) ) {
            $this->_values[] = $args;
        } elseif ($this->queryType == 'UPDATE') {
            $this->_values = $args;
        }

        return $this;
    }

    public function set() {
        $args = func_get_args();
        if (count($args) == 2) {
            $args = array($args[0] => $args[1]);
        } else {
            $args = $this->_getArgs(func_get_args());
        }

        $this->_fields = array_keys($args);
        $this->_values = array_values($args);

        return $this;
    }

    public function limit($limit = 0){
        $args = func_get_args();
        if (count($args) == 2) {
            $limit = $args[1];
            $this->offset( (int)$args[0] );
        } else if( count( $args = explode(',', $limit) ) == 2 ) {
            $limit = $args[1];
            $this->offset( (int)$args[0] );
        }

        $limit =(int)abs($limit);
        $this->_limit = $limit;

        return $this;
    }

    public function order($order){
        $order = strtoupper($order);
        if(in_array($order, array('ASC', 'DESC'))){
            $this->_order = $order;
        }

        return $this;
    }

    public function orderBy(){
        $args = $this->_getArgs(func_get_args());
        $this->_orderBy = $args;

        return $this;
    }

    public function groupBy(){
        $args = $this->_getArgs(func_get_args());
        $this->_groupBy = $args;

        return $this;
    }

    public function offset($offset = 0){
        $offset =(int)abs($offset);
        if($offset){
            $this->_offset = $offset;
        }

        return $this;
    }


/**
  //
  //-- Build Functions
  //
**/
    public function build(){
        $statement = array();

        $this->_buildOperator($statement);
        $this->{'_build'.$this->queryType}($statement);
        $this->_buildJoin($statement);
        $this->_buildWhere($statement, 'where');
        $this->_buildGroupBy($statement);
        $this->_buildOrderBy($statement);
        $this->_buildLimit($statement);

        $statement = implode(' ', $statement);

        return $statement;
    }

        private function _buildJoin(&$statement){
            if(!$this->_join){ return; }

            foreach($this->_join as $idx => $join){
                $statement[] = $join;
                if($this->_using){
                    $statement[] = sprintf('USING(%s)', $this->_using);
                }
                $this->_buildOn($statement, 'on', $idx);
            }
        }

        private function _buildUpdate(&$statement){
            $statement[] = sprintf('%s', $this->_buildTables($this->_tables));
            $statement[] = 'SET';

            $set = array();
            foreach($this->_fields as $k => $f){
                $set[] = sprintf('%s = %s', $f, $this->_values[$k]);
            }

            $statement[] = implode(', ', $set);
        }

        private function _buildDELETE(&$statement){
            $statement[] = sprintf('FROM %s', $this->_buildTables($this->_tables));
        }

        private function _buildSELECT(&$statement){
            $statement[] = $this->_buildFields($this->_fields);
            $statement[] = sprintf('FROM %s', $this->_buildTables($this->_tables));
        }

        private function _buildINSERT(&$statement){
            $statement[] = 'INTO';
            $statement[] = sprintf('%s', $this->_buildTables($this->_tables));
            $this->_buildINSERTFields($statement);

            $statement[] = 'VALUES';
            $this->_buildINSERTValues($statement);
        }

        private function _buildINSERTFields(&$statement){
            $statement[] = sprintf('(`%s`)', implode('`, `', $this->_fields));
        }

        private function _buildINSERTValues(&$statement){
            $values = array();
            foreach($this->_values as $field => $val){
                $val = $this->_sanitizeValue($val);

                $values[] = sprintf('%s', ($val === NULL ? 'NULL' : $val));
            }
            $statement[] = sprintf('(%s)', implode(', ', $values));
        }

        private function _buildREPLACE(&$statement){
            $statement[] = 'INTO';
            $statement[] = sprintf('%s', $this->_buildTables($this->_tables));
            $this->_buildREPLACEFields($statement);

            $statement[] = 'VALUES';
            $this->_buildREPLACEValues($statement);
        }

        private function _buildREPLACEFields(&$statement){
            $statement[] = sprintf('(`%s`)', implode('`, `', $this->_fields));
        }

        private function _buildREPLACEValues(&$statement){
            $values = array();
            foreach($this->_values as $field => $val){
                $val = $this->_sanitizeValue($val);

                $values[] = sprintf('%s', ($val === NULL ? 'NULL' : $val));
            }
            $statement[] = sprintf('(%s)', implode(', ', $values));
        }

        /**
         * Build the Create Table query
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   array  &$statement       Array to put the statement into
         *
         */
        private function _buildCREATE_TABLE( &$statement ) {
            $statement = array('CREATE');

            $this->_buildCreateTableTemporary($statement);

            $statement[] = 'TABLE';

            $this->_buildTableExists($statement);

            $statement[] = sprintf('`%s` (', $this->_buildTables($this->_tables));
            $this->_buildCreateTableColumns( $statement );
            $statement[] = ')' . $this->_buildCreateTableExtras($statement) . ';';
        }

        /**
         * Build the Drop Table query
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   array  &$statement       Array to put the statement into
         *
         */
        private function _buildDROP_TABLE( &$statement ) {
            $statement = array('DROP');

            $statement[] = 'TABLE';

            $this->_buildTableExists($statement);

            $statement[] = $this->_buildTables($this->_tables);
        }

        /**
         * Build the temporary string if the table to be created is only temporary
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   string  $var       Parameter Description
         *
         */
        private function _buildCreateTableTemporary( &$statement ) {
            if( !empty( $this->isTemporaryTable ) ) {
                $statement[] = ' TEMPORARY ';
            }
        }

        /**
         * Build the table columns when creating or altering table
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   array  $statement       Parameter Description
         *
         */
        private function _buildCreateTableColumns( &$statement ) {

            $primaryKey = null;

            foreach( $this->_columns as $column => $attr ) {

                $type       = doArgs( 'type',  null,  $attr );
                $length     = doArgs( 'length',  null,  $attr );
                $null       = doArgs( 'null',    null,  $attr );
                $default    = doArgs( 'default', null,  $attr );
                $ai         = doArgs( 'ai',      null,  $attr );

                $length     = ( $length != null     ? '(' . $length . ')' : NULL );
                $null       = ( $null   === false   ? 'NOT NULL'          : NULL );
                $ai         = ( $ai     !== null    ? 'AUTO_INCREMENT'    : NULL );

                // Do sexy check for default
                if( $default !== null ) {
                    $default = 'DEFAULT \'' . $default . '\'';

                } else if( $attr['null'] === true ) {
                    $default = 'DEFAULT NULL';

                }

                if( isset( $attr['primary'] ) && $primaryKey === null ) {
                    $primaryKey = $column;
                }

                $extra  = ''
                    .       $type
                    .       $length
                    . ' ' . $null
                    . ' ' . $default
                    . ' ' . $ai;


                $statement[] = sprintf('`%s` %s,',
                    $column,
                    trim( $extra )
                );
            }

            // Check for primary key
            if( $primaryKey !== null ) {
                $statement[] = sprintf( 'PRIMARY KEY (`%s`),', $primaryKey );
            }

            end($statement);
            $statement[key($statement)] = rtrim( $statement[key($statement)], ',' );
        }

        /**
         * Build the table extras when creating a table
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   array  $statement       Parameter Description
         *
         */
        private function _buildCreateTableExtras( &$statement ) {
            $output = '';

            // Engine
            if( !empty( $this->engineType ) ) {
                $output .= ' ENGINE=' . $this->engineType;
            }

            // Default Charset
            if( !empty( $this->charset ) ) {
                $output .= ' DEFAULT CHARSET=' . $this->charset;
            }

            // Auto Increment
            if( !empty( $this->charset ) ) {
                $output .= ' AUTO_INCREMENT=' . $this->AI;
            }

            return $output;
        }

        /**
         * Build the Not Exists string for the create table function
         *
         * @version 1.0
         * @since   1.0
         * @author  Daniel Noel-Davies
         *
         * @param   string  $var       Parameter Description
         *
         */
        private function _buildTableExists( &$statement ) {
            if( !empty( $this->_tableConditional ) ) {
                $statement[] = $this->_tableConditional;
            }
        }

        private function _buildOperator(&$statement){
            $statement[] = $this->queryType;
        }

        private function _buildFields($fields){
            $_fields = array();

            if(!is_array($fields)){ $fields = array($fields); }

            foreach($fields as $key => $field){
                $field = explode('.', $field);

                if(count($field) == 1){
                    $field = current($field);

                    if( strtoupper( substr( $field, 0, 5 ) ) == 'COUNT' || ctype_alnum($field) !== true ){
                        $_fields[] = $field;
                    }else{
                        $_fields[] = sprintf('`%s`', $field);
                    }
                    continue;
                }

                if(!is_number($key) && count($field) == 2){
                    $_fields[] = sprintf('%s.%s as %s', $field[0], $field[1], $key);
                }else{
                    $_fields[] = sprintf('%s.%s', $field[0], $field[1]);
                }
            }

            return implode(', ', $_fields);
        }

        private function _buildTables($tables){
            $_tables = array();

            if(is_string($tables)){
                if(strpos($tables, ' ') === false){
                    $tables = array($tables);
                }else{
                    $tables = explode(' ', $tables);
                    $tables = array($tables[2] => $tables[0]);
                }
            }

            foreach( $tables as $key => $table ) {
                $table = str_replace('`', '', $table);

                //check if were querying another db
                $tbl = explode('.', $table);
                if(count($tbl) == 2){
                    $_tables[] = sprintf('%s.%s', $tbl[0], $tbl[1]);
                    continue;
                }


                if( isset( $key ) && !empty( $key ) ) {
                    $_tables[] = sprintf('%s as %s', $table, $key);
                }else{
                    $_tables[] = sprintf('%s', $table);
                }
            }
            return implode(', ', $_tables);
        }

        private function _buildWhere(&$statement, $type){
            if(!in_array($this->queryType, array('UPDATE', 'DELETE', 'SELECT'))){ return; }

            if(!count($this->{'_'.strtolower($type)})){ return; }

            $statement[] = strtoupper($type);
            foreach($this->{'_'.strtolower($type)} as $where){
                $tmp = array($where['type'], $where['cond1'], $where['operand']);
                $tmp[1] = $this->_buildFields($tmp[1]);

                if($where['operand'] != 'IN'){
                    if( $type == 'where' && $where['fields'] === false ){
                        $tmp[] = $this->_sanitizeValue($where['cond2'], $where['operand'] == 'LIKE');
                    }else{
                        $tmp[] = $where['cond2'];
                    }
                }else{

                    $ins = array();
                    if(!is_array($where['cond2'])){
                        $ins = array($where['cond2']);
                    }else{
                        foreach($where['cond2'] as $c2){
                            $ins[] = $this->_sanitizeValue($c2, false);
                        }
                    }

                    $tmp[2] = sprintf('%s ("%s")', $tmp[2], implode(', ', $ins));
                }
                $statement[] = implode(' ', $tmp);
            }
        }

        private function _buildOn(&$statement, $type, $idx=0){
            if(!in_array($this->queryType, array('UPDATE', 'DELETE', 'SELECT'))){ return; }

            if(!count($this->{'_'.strtolower($type)})){ return; }

            if(!isset($this->{'_'.strtolower($type)}[$idx])){ return; }
            $where = $this->{'_'.strtolower($type)}[$idx];

            $statement[] = strtoupper($type);
            //foreach($this->{'_'.strtolower($type)} as $where){
                $tmp = array($where['type'], $where['cond1'], $where['operand']);
                $tmp[1] = $this->_buildFields($tmp[1]);

                if($where['operand'] != 'IN'){
                    if($type == 'where'){
                        $tmp[] = $this->_sanitizeValue($where['cond2'], $where['operand'] == 'LIKE');
                    }else{
                        $tmp[] = $where['cond2'];
                    }
                }else{

                    $ins = array();
                    if(!is_array($where['cond2'])){
                        $ins = array($where['cond2']);
                    }else{
                        foreach($where['cond2'] as $c2){
                            $ins[] = $this->_sanitizeValue($c2, false);
                        }
                    }

                    $tmp[2] = sprintf('%s ("%s")', $tmp[2], implode(', ', $ins));
                }
                $statement[] = implode(' ', $tmp);
            //}
        }

        private function _buildGroupBy(&$statement){
            if($this->queryType != 'SELECT'){ return; }

            if(!count($this->_groupBy)){ return; }

            $statement[] = 'GROUP BY';
            $gbs = array();
            foreach($this->_groupBy as $gb){
                $gbs[] = sprintf('%s', $gb);
            }
            $statement[] = implode(', ', $gbs);
        }

        private function _buildOrderBy(&$statement){
            if($this->queryType != 'SELECT'){ return; }

            if(!count($this->_orderBy)){ return; }

            $statement[] = 'ORDER BY';

            $obs = array();
            foreach($this->_orderBy as $ob){
                if(in_array(strtoupper($ob), array('ASC', 'DESC'))){ $this->_order = strtoupper($ob); continue; }

                $obs[] = $this->_buildFields($ob);
            }

            $statement[] = implode(', ', $obs);
            $statement[] = $this->_order;
        }

        private function _buildLimit(&$statement){
            if($this->_offset > 0 && $this->_limit > 0){
                $statement[] = sprintf('LIMIT %s, %s', $this->_offset, $this->_limit);

            }elseif($this->_offset > 0){
                $statement[] = sprintf('OFFSET %d', $this->_offset);

            }elseif($this->_limit > 0){
                $statement[] = sprintf('LIMIT %d', $this->_limit);
            }
        }


/**
  //
  //-- Extra Functions
  //
**/

    private function _NormalizeArgs($args){
        if(count($args) == 1 && is_string($args[0])){
            $args = current($args);
            $args = explode(' ', $args);
        }
        if(!is_string($args) && !is_array($args)){
            trigger_error('Error: $args == '.gettype($args).'; Not of type String || Array',
                E_USER_ERROR);
        }

        return $args;
    }

    private function _addWhereOn($cond, $type, $property){
        $oCond = $cond;
        $cond = $this->_getArgs($cond);
        $cond = $this->_NormalizeArgs($cond);

        if(count($cond) != 3){
            trigger_error(dump($cond, count($cond)).'Error: Not enough args for Clause. '.count($cond),
                E_USER_ERROR);
        }

        $operand = strtoupper($cond[1]);
        if(!in_array($operand, array('=', '>', '<', '<>', '!=', '<=', '>=', 'LIKE', 'IN'))){
            trigger_error('Error: Unsupported operand:'.$operand, E_USER_ERROR);
        }
        $this->{'_'.$property}[] = array(
            'cond1'   => $cond[0],
            'cond2'   => $cond[2],
            'operand' => $operand,
            'type'    => $type,
            'fields'  => ( sizeOf( $oCond ) === 1 ? true : false ),
        );
        return $this;
    }

    protected function _sanitizeValue($val) {
        if( in_array( $val, array( 'NULL', 'true', 'false', null ) ) ) {
            return $val;
        }

        if( !is_string( $val ) && is_number( $val ) ) {
            return $val;
        }

        return '"' . $val . '"'; ///addslashes($val);
    }

    private function setQueryType($queryType){
        if($this->queryType){
            trigger_error('Can\'t modify the operator.', E_USER_ERROR);

        }elseif(!in_array($queryType, array('select', 'insert', 'replace', 'delete', 'update', 'create_table', 'drop_table'))){
            trigger_error('Unsupported operator:'.strtoupper($queryType), E_USER_ERROR);

        }else{
            $this->queryType = strtoupper($queryType);
        }
    }

}