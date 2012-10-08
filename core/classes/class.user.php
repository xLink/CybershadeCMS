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

		// Get the result from the previous query
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

	/**
	 * Generates a user password with the given length
	 * 
	 * @author 	Richard Clifford, Dan Aldridge
	 * @version 1.0.0
	 * @access  Protected
	 * 
	 * @since 	1.0.0
	 *
	 * @param 	int $len
	 *
	 * @return 	string
	 */
	protected function makePassword( $len = 12 ){
		return randCode( $len );
	}

	public function resetPassword( $user_id, $password = '' ){
		// Used to send a reset email to the user
		$userDetails = $this->getUserById( $user_id );

		$userDetails = (is_array( $userDetails ) && !is_empty( $userDetails )
						? $userDetails 
						: array());

		$email = $userDetails['email'];

		// Needs some major work xD
		if( !is_empty( $email ) ){
			$adminEmail = 'admin@cybershade.org'; // test email address
			$subject 	= sprintf( 'Password Reset for %s', $userDetails['username'] ); 
			$message	= sprintf( "Dear %s \n\r The password link to reset your password is: %s", $userDetails['username'], $this->resetPassURL( $user_id ) );
			$mail 		= _mailer($email, $adminEmail, $subject, $message);

			if( $mail ){
				return truel
			}
		}
		return false;
	}

	public function editUserPassword( $user_id, $password ){
		// Edits the user password from the UCP
	}

	public function banUserId( $user_id, $len = 0 ){

	}

	public function updateUser( $user_id, $fields = array() ){

	}
}


?>