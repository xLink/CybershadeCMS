<?php

class User extends coreObj {

    protected $objSession;
    protected $objSQL;

    public function __construct(){
        $this->objSession   = coreObj::getSessions(); // Wrong function?
        $this->objSQL       = coreObj::getDBO();
    }

    public function __destruct(){
        // Kill the class vars
        unset( $this->objSession, $this->objSQL );
    }

    /**
     * Gets users details by their User ID
     *
     * @since   1.0.0
     * @version 1.0.0
     * @author  Richard Clifford
     *
     * @param   int $user_id
     *
     * @return  array
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
     * @since   1.0.0
     * @version 1.0.0
     * @author  Richard Clifford
     *
     *
     * @param   string $username
     *
     * @return  array
     */
    public function getUserIdByName( $username ){

    }


    /**
     * Gets info on the specified user by ID or username
     *
     * @author Richard Clifford, Dan Aldridge
     * @since  1.0.0
     * @author 1.0.0
     *
     * @param   int     $uid
     * @param   string  $field1
     * @param   string  $field2 [etc]
     *
     * @return array
     */
    public function getUserInfo(){

        $args = func_get_args();
        $uid  = array_shift($args);

        $fieldList = $args;

        $cachedInfo = $this->getVar( 'userInfo' );
        $username = (!is_number( $uid ) ? strtolower($uid) : $uid );

        if( !isset( $cachedInfo[$uid] ) ){
            // username or user ID?
            $user = (is_number( $uid ) 
                        ? sprintf('u.uid = %d', $uid)  
                        : sprintf('UPPER(u.username) = UPPER("%s")', $uid));

            $info = $this->objSQL->queryBuilder()
                                 ->select('u.*, e.*, u.uid AS id, s.timestamp, s.sid')
                                 ->from('#__users')
                                 ->leftJoin('#__users_extras')
                                    ->on('u.uid = e.uid')
                                 ->leftJoin('#__sessions')
                                    ->on('s.uid = u.uid')
                                 ->where($user);

            $results = $this->objSQL->fetchAll( $info );

            if( count( $results ) === 0 ){
                trigger_error(sprintf('User query failed. Query : %s', $info));
                return false;
            }

            unset( $results['uid'] );

            $this->userInfo[$username] = $results;
        }

        if( count( $fieldList ) > 0 ){
            foreach( $fieldList as $field ){
                if( !array_key_exists( $field, $this->userInfo[$username] ) ){
                    continue;
                }

                $arrOut[$uid][] = $this->userInfo[$username][$field];
            }

            return $arrOut;
        }
        return $this->userInfo[$username];
    }


    /**
     * Assigns a session to a specified User ID
     *
     * @since   1.0.0
     * @version 1.0.0
     * @author  Richard Clifford
     *
     *
     * @param   int  $user_id 
     *
     * @return  bool
     */
    public function assignSession( $user_id ){
        $userSession = $this->objSession->checkUserSession( $user_id, false );

        if( !is_number( $user_id ) || $userSession ){
            return false;
        }

        $assignedSession = $this->objSession->createSession( $user_id );

        return $assignedSession;
    }

    /**
     * Generates a user password with the given length
     *
     * @access  Protected
     * @since   1.0.0
     * @version 1.0.0
     * @author  Richard Clifford, Dan Aldridge
     *
     *
     * @param   string  $string (Default: '')
     * @param   int     $length (Default: 8)
     * @param   string  $salt   (Default: '')
     * @param   string  $pepper (Default: '')
     *
     * @return  string
     */
    protected function makePassword( $string = '', $length = 8, $salt = '', $pepper = '' ){

        // Store the salt and pepper
        $this->setVar('salt', $salt);
        $this->setVar('pepper', $pepper);

        if( is_empty( $string ) ){
            $string = randCode($lenght); // Generate a random string
        }

        // Instanciate the Portable password hashing framework
        $objPass = new phpass( $length, true );

        // Generate the new password with salt
        $password   = sprintf( '%s%s%s', $salt, $string, $pepper );
        $hashed     = $objPass->HashPassword( $password );

        // Clean up
        unset( $objPass, $string, $password );
        return $hashed;
    }

    public function resetPassword( $user_id, $password = '' ){
   
    }

    public function editPassword( $user_id, $password, $salt = '', $pepper = '' ){
        // Edits the user password from the UCP
    }

    public function banUserId( $user_id, $len = 0 ){

    }

    public function updateUser( $user_id, $fields = array() ){

    }
}


?>