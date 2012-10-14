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
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int $user_id
     *
     * @return  array
     */
    public function getUserById( $user_id ){
        if( is_empty( $user_id ) ){
            return array();
        }

        $userDetails = $this->getUserInfo( $user_id );

        return $userDetails;
    }

    /**
     * Gets users details by their Username
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   string $username
     *
     * @return  array
     */
    public function getUserByName( $username ){
        if( is_empty( $username ) ){
            return array();
        }

        $userDetails = $this->getUserInfo( $username );

        return $userDetails;
    }


    /**
     * Gets info on the specified user by ID or username
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford, Dan Aldridge
     *
     * @param   int     $uid
     * @param   string  $field1
     * @param   string  $field2 [etc]
     *
     * @return  array
     */
    public function getUserInfo(){

        $args = func_get_args();
        $uid  = array_shift($args);

        $fieldList = $args;

        $cachedInfo = $this->getVar( 'userInfo' );
        $username = (!is_number( $uid ) ? strtolower($uid) : $uid );

        if( !isset( $cachedInfo[$uid] ) ){
            // username or user ID?
            $user = $this->_userIdQuery( $uid );

            // Optimize this query!
            $info = $this->objSQL->queryBuilder()
                                 ->select(array('u.*', 'e.*', 'id' => 'u.uid', 's.timestamp', 's.sid'))
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

            // No need for uid as the user id is 'id'
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
     * Generates a user password with the given length
     *
     * @access  Protected
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford, Dan Aldridge
     *
     * @param   string  $string
     * @param   string  $salt
     * @param   string  $pepper
     *
     * @return  string
     */
    protected function makePassword( $string = '', $length = 8, $salt = '', $pepper = '' ){

        // Store the salt and pepper
        $this->setVar('salt', $salt);
        $this->setVar('pepper', $pepper);

        if( is_empty( $string ) ){
            $string = randCode($length); // Generate a random string
        }

        // Instanciate the Portable password hashing framework
        $objPass = new phpass(8, true);

        // Generate the new password with salt
        $password   = $salt . $string . $pepper;
        $hashed     = $objPass->HashPassword( $password );

        // Clean up
        unset($objPass, $string, $password);
        return $hashed;
    }

    /**
     * Determines whether a user is online or not
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int     $uid
     *
     * @return  bool
     */
    public function isUserOnline( $uid ){
        $ts = $this->getUserInfo( $uid, 'timestamp' );

        return ( is_empty( $ts ) ? false : true );
    }

    /**
     * Checks if the given user id is a number (id) or a string (username)
     * and returns a sql formatted string (only to be used within this class)
     *
     * @access  Protected
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $uid
     *
     * @return  string  SQL formatted string
     */
    protected function _userIdQuery( $uid ){

        $user = '';
        $return = null;

        // Check if number
        if( !is_number( $uid ) ){
            $user = sprintf( 'UPPER(#__users.username) = UPPER("%s")', $uid );
        } else {
            $user = sprintf( '#__users.uid = %d', $uid );
        }

        return $user;
    }

    /**
     * Checks whether a user exists with the given ID/Username
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $uid
     *
     * @return  string  SQL formatted string
     */
    public function userExists( $uid ){

        $user = $this->_userIdQuery( $uid );
        // Build the query
        $userQuery = $this->objSQL->queryBuilder()
                                  ->select('username','uid')
                                  ->from('#__users')
                                  ->where($user)
                                  ->limit(1)
                                  ->build();

        // Get the return values
        $results = $this->objSQL->fetchLine( $userQuery );

        if( !count( $results ) ){
            return false;
        }

        return true;
    }

    public function resetPassword( $user_id, $password = '' ){

    }

    public function editPassword( $user_id, $password, $salt = '', $pepper = '' ){
        // Edits the user password from the UCP
    }

    /**
     * Bans a user from the users table and the sessions table
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $uid
     * @param   int     $banLen
     *
     * @return  bool
     */
    public function banUserId( $uid, $banLen = 0 ){
        $blOut = false;

        $uid = $this->_userIdQuery( $user_id );

        $query = $this->objSQL->queryBuilder()
                              ->update('#__users')
                              ->set('banned', 1)
                              ->where($uid)
                              ->build();

        // Ban the user from the users table
        $banUser = $this->objSQL->query($query);

        if( $banUser ){
            // Unset query to reuse it
            unset( $query );

            $user_id = $this->getUserInfo( $uid, 'id' );

            $query = $this->objSQL->queryBuilder()
                                  ->update('#__sessions')
                                  ->set(array(
                                        'mode'      => 'ban',
                                        'timestamp' => $banLen
                                    ))
                                  ->where('uid', $user_id)
                                  ->build();

            // Ban the user from the sessions table
            $banSession = $this->objSQL->query( $query );

            if( $banSession ){
                $blOut = true;
            }
        }

        // Tidy up
        unset( $banUser, $query, $uid, $banSession );

        return $blOut;
    }


    /**
     * Update the users tables with the given details
     *
     * @version     1.0.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       int     $user_id
     * @param       array   $field      array('users' => array('fields' => 'fieldVal'), 'users_extras' => array('fields' => 'fieldVal'))
     * 
     * @return      bool
     */
    public function updateUser( $user_id, $fields = array() ){

        $blOut = false;
        /* Example Param                                
            $fields = array(
                'users' => array(
                    'id'        => 1,
                    'username'  => 'DarkMantis',
                    'email'     => 'dm@cs.org',
                ),
                'users_extras'  => array(
                    'age'   => 21,
                    'sex'   => 'M'
                )
            );*/


        $user_id = $this->_userIdQuery( $user_id );

        if( !$this->userExists( $user_id ) ){
            return $blOut;
        }

        if( !is_array( $fields ) || empty( $fields ) ){
            return $blOut;
        }


        // Updates the user and extras table
        foreach( $fields as $fieldset => $fields ){        
            $updateQuery = $this->objSQL->queryBuilder()
                                        ->update(sprintf('#__%s', $fieldset )
                                        ->set($fields)
                                        ->where('id', '=', $user_id)
                                        ->build();

            $result = $this->objSQL->query( $updateQuery );
            if( !$result ){
                $blOut = false;
            }
        }

        return $blOut;
    }
}

?>