<?php
/**
 *@todo REMOVE $this->_idQuery function
*/
class User extends coreObj {

    protected $objSession;
    protected $objSQL;

    public function __construct( $keys = array() ){
        $this->objSQL = coreObj::getDBO();

        $blackListedKeys = array(
            'id',
            'username',
            'password',
            'email',
        );

        $keys = ( is_array( $keys ) ? $keys : array( $keys ) );

        $blackListedKeys = array_merge( $blackListedKeys, $keys );

        $this->setVar('blackListedKeys', $blackListedKeys );
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

            // Gets all 'safe' data about a user
            $info = $this->objSQL->queryBuilder()
                                 ->select('#__users.id', '#__users.username', '#__users.register_date', '#__users.email',
                                    '#__users.last_active', '#__users.register_date', '#__users.title', '#__users.language',
                                    '#__users.timezone', '#__users.theme', '#__users.hidden', '#__users.userlevel', '#__users.active',
                                    '#__users.banned', '#__users.primary_group', '#__users.login_attempts', '#__users.pin_attempts',
                                    '#__users.autologin', '#__users.reffered_by', '#__users.password_update', '#__users.whitelist',
                                    '#__users.whitelisted_ips', '#__users.warnings', '#__users_extras.birthday',
                                    '#__users_extras.sex', '#__users_extras.contact_info', '#__users_extras.about',
                                    '#__users_extras.interests', '#__users_extras.usernotes', '#__users_extras.signature',
                                    '#__users_extras.ajax_settings', '#__users_extras.notification_settings', '#__users_extras.forum_show_sigs',
                                    '#__users_extras.forum_autowatch', '#__users_extras.forum_quickreply',
                                    '#__users_extras.forum_cat_order', '#__users_extras.forum_tracker', '#__users_extras.pagination_style',
                                    '#__sessions.timestamp', '#__sessions.sid')
                                 ->from('#__users')
                                 ->leftJoin('#__users_extras')
                                    ->on('#__users.id = #__users_extras.uid')
                                 ->leftJoin('#__sessions')
                                    ->on('#__users.id = #__sessions.uid')
                                 ->where($user)
                                 ->limit(1)
                                 ->build();

            $results = $this->objSQL->fetchAll( $info );

            if( count( $results ) === 0 ){
                trigger_error(sprintf('User query failed. Query : %s', $info));
                return false;
            }

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
     * @todo    This will return a false positive if the user is banned, so need to
     * check whether the user has been banned first then get the timestamp
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

        $user   =  '';
        $return = '';

        // Check if number
        if( !is_number( $uid ) ){
            $user = sprintf( 'UPPER(#__users.username) = UPPER("%s")', $uid );
        } else {
            $user = sprintf( '#__users.id = %d', $uid );
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
                                  ->select('username','id')
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
     * @todo fix the _userIdQuery as returns     $ = String(72) "UPPER(#__users.username) = UPPER("UPPER(#__users.username) = UPPER("")")"
     * 
     * @return  bool
     */
    public function banUserId( $uid, $banLen = 0 ){
        $blOut = false;

        $uid = $this->_userIdQuery( $user_id );

        // Ensure $banLen is a number
        $banLen = ( is_number( $banLen ) ? $banLen : 0 );

        $query = $this->objSQL->queryBuilder()
                              ->update('#__users')
                              ->set('banned', 1)
                              ->where($uid)
                              ->build();

        // Ban the user from the users table
        $banUser = $this->objSQL->query($query);

        if( $banUser ){

            $user_id = $this->getUserInfo( $uid, 'id' );

            $sessionQuery = $this->objSQL->queryBuilder()
                                  ->update('#__sessions')
                                  ->set(array(
                                        'mode'      => 'ban',
                                        'timestamp' => $banLen
                                    ))
                                  ->where('uid', $user_id)
                                  ->build();

            // Ban the user from the sessions table
            $banSession = $this->objSQL->query( $sessionQuery );

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

        $user_id = $this->_userIdQuery( $user_id );

        if( !$this->userExists( $user_id ) ){
            return $blOut;
        }

        if( !is_array( $fields ) || empty( $fields ) ){
            return $blOut;
        }

        $columnInfo                 = array();
        $columnInfo['users']        = $this->objSQL->fetchColumnInfo( '#__users' );
        $columnInfo['users_extras'] = $this->objSQL->fetchColumnInfo( '#__users_extras' );

        $usersData = array();

        if( is_empty($columnInfo['users']) || is_empty( $columnInfo['users_extras'] ) ){
            return false;
        }

        $blackListKeys = $this->getVar('blackListedKeys');

        foreach( $fields as $column => $data ){

            // Exclude the blacklisted keys
            if( in_array( $column, $blackListKeys ) ){
                continue;
            }

            if( array_key_exists( $column, $columnInfo['users'] ) ){
                $usersData['users'][$column] = $data;
            }

            if( array_key_exists( $column, $columnInfo['users_extras'] ) ) {
                $usersData['users_extras'][$column] = $data;
            }
        }

        if( is_empty( $fields ) ){
            return false;
        }

        // Updates the user and extras table
        foreach( $fields as $fieldset => $fields ){

            $updateQuery = $this->objSQL->queryBuilder()
                                        ->update(sprintf('#__%s', $fieldset ))
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


    /**
     * Resets the users password and confirms with them via email
     *
     * @version     1.0.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       int     $user_id
     *
     * @return      bool
     */
    public function resetPassword( $user_id ){

        $uid        = $user_id;
        $user_id    = $this->_userIdQuery( $user_id );

        if( !$this->userExists( $user_id ) ){
            return false;
        }


        // Get the users details
        $userDetails = $this->getUserInfo( $uid, 'email', 'username' );


        // Check if the details are valid and not empty
        if( is_empty( $userDetails ) ){
            return false;
        }

        // Everything from here should be for a valid user
        $fields = array();

        $fields['users'] = array(
            'password'  =>  $this->makePassword(),
        );

        $update = $this->updateUser($uid, $fields);

        if( !$update ){
            return false;
        }


        // Generate the URL for the user to click when they receive the email (tokenized)
        $resetLink = '';

        // Setup the email details
        $adminEmail = $this->config( 'site', 'admin_email' );
        $siteName   = $this->config( 'site', 'name' );
        $message    = <<<MSG
            Dear %s,
                Your password has been reset by your request.

                If you did not request your password to be changed, please ignore this email, otherwise, please follow this link:

                %s

                Your link will only be valid for 24 hours, after that you will be required to reset again.
MSG;

        // Replace the $message tokens
        $msgDetails = array(
            $userDetails['username'],
            $resetLink,
        );

        $message    = vsprintf( $message, $msgDetails );
        $mail       = _mailer( $userDetails['email'], $adminEmail, sprintf( 'Password Reset from %s.', $siteName ), $message );

        // send the mail
        if( !$mail ){
            return false;
        }

        return true;
    }

    /**
     * TODO: make the function better XD
     */
    public function getRemoteAddr(){
        return $_SERVER['REMOTE_ADDR'];
    }
}

?>