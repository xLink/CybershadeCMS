<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

interface Core_Classes_baseQueryBuilder {

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