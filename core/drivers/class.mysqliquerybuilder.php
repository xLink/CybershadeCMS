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
    private $_fields   = array();
    private $_values   = array();
    private $_tables   = array();

    private $_join     = array();
    private $_using    = '';
    private $_on       = array();

    private $_where    = array();
    private $_limit    = 0;
    private $_offset   = 0;

    private $_orderBy  = array();
    private $_order    = 'ASC';
    private $_groupBy  = array();

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
        if (!in_array($this->queryType, array('INSERT', 'UPDATE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());

        $this->fields(array_keys($args));
        $this->values(array_values($args));

        return $this;
    }

    public function fields($fields) {
        if (!in_array($this->queryType, array('INSERT', 'UPDATE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());
        $this->_fields = $args;

        return $this;
    }

    public function values($values) {
        if (!in_array($this->queryType, array('INSERT', 'UPDATE'))) {
            trigger_error('Error: Only INSERT and Update operations.', E_USER_ERROR);
        }

        $args = $this->_getArgs(func_get_args());
            if (count($args) != count($this->_fields)) {
                trigger_error('Error: Number of values has to be equal to the number of fields.', E_USER_ERROR);
            }

        if ($this->queryType == 'INSERT') {
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

            foreach($tables as $key => $table){
                $table = str_replace('`', '', $table);

                //check if were querying another db
                $tbl = explode('.', $table);
                if(count($tbl) == 2){
                    $_tables[] = sprintf('%s.%s', $tbl[0], $tbl[1]);
                    continue;
                }


                if(isset($key) && !empty($key)){
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

        }elseif(!in_array($queryType, array('select', 'insert', 'delete', 'update'))){
            trigger_error('Unsupported operator:'.strtoupper($queryType), E_USER_ERROR);

        }else{
            $this->queryType = strtoupper($queryType);
        }
    }

}

interface Core_Classes_queryBuilder {

	// Begin Query Type initiators

	/**
	 * Set query type to select, and gather fields that will be returned in the results set
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param 	string 	$field 	Field Name(s)
	 */
	public function select();

	/**
	 * Set query type to insert, and get the table name we're inserting data into
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table    Table Name
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function insertInto( $table );

	/**
	 * Set query type to delete, and get the table name we're delete data from
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table    Table Name
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function deleteFrom( $table );

	/**
	 * Set query type to update, and get the table name we're updating data from
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table    Table Name
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function update( $table );

	/**
	 * Set query type to create, and get the table name we're creating
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table    Table Name
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function createTable( $table );


	// Begin Core functions

	/**
	 * Determine what tables we're selecting data from
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $tables    Table Name(s)
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function from( $tables );

	/**
	 * Add a field to the query in it's respective role
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $field     Field Name(s)
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function addField( $field );

	/**
	 * Add a where clause to the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $where       Where clause
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function where( $where );

	/**
	 * Adds an 'AND' conditional to the where clause within the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $where       Where Clause
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function andWhere( $where );

	/**
	 * Adds an 'OR' conditional to the where clause within the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $where       Where Clause
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function orWhere( $where );

	/**
	 * Adds a Join to the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table      Table Name(s)
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function join( $table );

	/**
	 * Adds a Left Join to the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table      Table Name(s)
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function leftJoin( $table );

	/**
	 * Adds a Right Join to the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $table      Table Name(s)
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function rightJoin( $table );

	/**
	 * Force the Join Clause to join on various columns
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $field      Field Name
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function using( $field );

	/**
	 * Add On clause to the Join clause
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $condition         Condition
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function on( $condition );

	/**
	 * Add an ADD On clause to the Join clause
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $condition         Condition
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function andOn( $condition );

	/**
	 * Add an OR On clause to the Join clause
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $condition         Condition
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function orOn( $condition );

	/**
	 * Add fields and values to an insert query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   array  $data       Column => Value data
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function args( $data );

	/**
	 * Add fields to be used in insert / update query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   array  $fields     Array of fields
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function fields( $fields );

	/**
	 * Add Values to be used in insert / update query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   array  $values     Array of values
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function values( $values );

	/**
	 * Sets the fields and values to be used in the query (Raw version of args)
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $data      Field => Value data
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function set();

	/**
	 * Add a limit clause to the query
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   int  $limit        Max Number of results to return
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function limit( $limit );

	/**
	 * Order the results set
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $order     ASC / DESC
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function order( $order );

	/**
	 * Set what fields to order the results by
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $fields  Fields to order by
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function orderBy( $fields );

	/**
	 * Group the results by field(s)
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   mixed  $fields  Fields to group by
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function groupBy();

	/**
	 * Offset the result set
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   int  $offset    Number of results to offset against
	 *
	 * @return 	object 			Query builder object for chaining
	 */
	public function offset( $offset );

	/**
	 * Build the query and return the query as a string
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @return string
	 */
	public function build();
}