<?php

interface queryBuilder {

	// Begin Query Type initiators

	/**
	 * Set query type to select, and gather fields that will be returned in the results set
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
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
	 */
	public function where( $where );

	public function andWhere();

	public function orWhere();

	public function join();

	public function leftJoin();

	public function rightJoin();

	public function using();

	public function on();

	public function andOn();

	public function orOn();

	public function args();

	public function fields();

	public function values();

	public function set();

	public function order();

	public function orderBy();

	public function groupBy();

	public function offset();

	public function build();
}