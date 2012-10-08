<?php

class User extends coreObj {
	
	protected $objSession;
	protected $objSQL;

	public function __construct(){
		$this->objSession 	= coreObj::getSessions(); // Wrong function?
		$this->objSQL 		= coreObj::getDBO();
	}

	public function __destruct(){
		// Kill the class vars
		unset( $this->objSession, $this->objSQL );
	}

	/**
	 * Gets users details by their User ID
	 * 
	 * @author Richard Clifford
     * @version 1.0.0
     *
 	 * @since 1.0.0
	 *	
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function getUserById( $user_id ){
		if( is_empty( $user_id ) || !is_number( $user_id ) ){
			return 0;
		}

        (cmsDEBUG ? memoryUsage( 'Users: Building the user select query') : '');
		$userQuery = $this->objSQL->queryBuilder()
								  ->select('username', 'register_date', 'last_active', 'email', 'show_email', 'userlevel', 'warnings')
								  ->from('#__users')
								  ->where('id', '=', $user_id)
								  ->limit(1)
								  ->build();

		$result = $this->objSQL->getLine( $userQuery );

		if( count( $result ) > 0 ){
			return $result;
		}

        (cmsDEBUG ? memoryUsage( 'Users: Requested User ID could not be found') : '');
		return array();
	}

	/**
	 * Gets users details by their Username
	 * 
	 * @author Richard Clifford
     * @version 1.0.0
     *
 	 * @since 1.0.0
	 *	
	 * @param string $username
	 *
	 * @return array
	 */
	public function getUserIdByName( $username ){

	}

	public function assignSession( $user_id, $session_id ){

	}

	protected function makePassword( $len = 12 ){

	}

	public function resetPassword( $user_id, $password = '' ){

	}

	public function banUserId( $user_id, $len = 0 ){

	}

	public function updateUser( $user_id, $fields = array() ){

	}
}


?>