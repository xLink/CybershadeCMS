<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Session extends coreObj{

    public function __construct($store='none', $options = array()){
        $this->objSQL = coreObj::getDBO();
    }

    public function __destruct(){

    }

    /**
     * Gets the form token for the sessoni
     *
     * @author Dan Aldridge, Richard Clifford
     * @version 1.0.0
     *
     * @since 1.0.0
     *
     * @param bool $forceNew
     *
     * @return string $token
     */
    public function getFormToken($forceNew=false){
        $objUser = coreObj::getUser();

        return $objUser->mkHash($objUser->get('id', 0) . self::getToken());
    }

    /**
     * Gets the token for the session
     *
     * @author Dan Aldridge, Richard Clifford
     * @version 1.0.0
     *
     * @since 1.0.0
     *
     * @param bool $forceNew
     *
     * @return string $token
     */
    public function getToken($forceNew=false){
        $token = $this->getVar('session', 'token');

        if(empty($token) || $forceNew){
            $token = randCode(12);
            $this->setVar('session', 'token', $token);
        }

        return $token;
    }


    /**
     * Gets the token for the session
     *
     * @author Richard Clifford
     * @version 1.0.0
     *
     * @since 1.0.0
     *
     * @param $uid      int     The User ID of the user to create the session for (Default 0 - Anonymous)
     * @param $status   string  The state of the session (Default 'active')
     *
     * @return bool
     */
    public function createSession( $uid = 0, $status = 'active'  ){
        if( ( isset($_SESSION['sid']) && isset( $_SESSION['ts'] ) ) && $_SESSION['ts'] > time() ){
            return false;
        }

        $uid = ( !is_number( $uid ) ? false : $uid );

        if( $uid === false ){
            (cmsDEBUG ? memoryUsage( 'Sessions: $uid was not a number, returning false ') : '');
            return false;
        }

        $session_id = randCode(32);
        // Just a check
        if( empty( $session_id ) ){
            $offset = rand( 1, 86400 );
            $session_id = md5( time() + $offset );
        }

        $check = $this->objSQL->queryBuilder()
                              ->select('sid')
                              ->from('#__sessions')
                              ->where('sid', '=', $session_id)
                              ->build();


        $checkResult = $this->objSQL->fetchAll( $check );

        // Checks if the result is in the array of sessions
        if( in_array( $session_id, $checkResult ) ){

            $getAllSessions = $this->objSQL->queryBuilder()
                                           ->select('sid')
                                           ->from('#__sessions')
                                           ->build();

            // Get all sessions
            $sessions = $this->objSQL->fetchAll( $getAllSessions );

            // Ensure the current session_id is not in use
            while( in_array( $session_id, $sessions ) ){
                (cmsDEBUG ? memoryUsage( 'Sessions: Generated Session ID was not Unique, Recreating... ') : '');
                $session_id = randCode(32);
            }
        }

        // Set the var
        $this->setVar( 'session_id', $session_id );

        // Explicitly set the variable
        $values = array();

        // Values to insert into db
        $values['uid']       = $uid;
        $values['sid']       = $session_id;
        $values['store']     = serialize( $_SESSION );
        $values['hostname']  = $_SERVER['REMOTE_ADDR'];
        $values['timestamp'] = time();
        $values['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $values['mode']      = $status;

        (cmsDEBUG ? memoryUsage('Sessions: Lets save the session!') : '');
        $query = $this->objSQL->queryBuilder()
                        ->insertInto('#__sessions')
                        ->set($values)
                        ->build();

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Executing Query: %s', $query )) : '');
        $result = $this->objSQL->query( $query );

        // Ensure the result is valid
        if( $result ){
            $_SESSION['id']  = $session_id;
            $_SESSION['sid'] = md5( $uid );
            $_SESSION['ts']  = (time() + 3600); // Give it an hour

            return true;
        }

        return false;
    }

    /**
     * Kills all the sessions for whatever reason
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @return bool
     */
    public function killAllSessions(){

        $query = $this->objSQL->queryBuilder()
                              ->deleteFrom('#__sessions')
                              ->where('1 = 1')
                              ->build();

        (cmsDEBUG ? memoryUsage(sprintf('Sessions: Executing Query: %s', $query) ): '');
        $result = $this->objSQL->query( $query );

        if( $result ) {

            // Unset the sessions
            unset( $_SESSION );
            return true;
        }

        return false;
    }

    /**
     * Kills the specified session
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @return bool
     */
    public function killSession( $session_id ){
        // Check the session id for type
        $session_id = ( ctype_alnum( $session_id ) ? $session_id : false  );

        // If wrong type then return false
        if( !$session_id ){
            return false;
        }

        // Build the query
        $query = $this->objSQL->queryBuilder()
                              ->deleteFrom('#__sessions')
                              ->where('session_id', '=', $session_id)
                              ->build();

        $result = $this->objSQL->query( $query );

        if( !$query ){
            return false;
        }

        // Unset the session id
        unset( $_SESSION[$session_id] );

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Deleted session: %s ', $session_id ) ) : '');
        return true;
    }

    /**
     * Retrieves a session
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @return array
     */
    public function getSessionById( $session_id ){
        $session_id = ( ctype_alnum((string)$session_id) ? $session_id : '' );

        if( $session_id === '' || empty( $session_id ) ) {
            return false;
        }

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Executing Query: %s', $query ) ) : '');
        // Build the query
        $query = $this->objSQL->queryBuilder()
                              ->select('uid', 'sid', 'mode', 'store', 'hostname')
                              ->from('#__sessions')
                              ->where('sid', '=', $session_id)
                                ->limit(1)
                              ->build();

        // Execute the query
        $sessions = $this->objSQL->fetchLine( $query );

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Returning: %s', $return ) ) : '');
        if( $sessions ){
            return $sessions;
        }

        return array();
    }


    /**
     * Retrieves a set of active sessions
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @param int $limit The limit on the query
     *
     * @return array
     */
    public function getActiveSessions( $limit = 0 ){
        (cmsDEBUG ? memoryUsage( 'Sessions: Getting all Active Sessions')  : '');
        return $this->getSessionsByType( 'active', $limit );
    }


    /**
     * Retrieves a set of Banned Sessions
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @param int $limit The limit on the query
     *
     * @return array
     */
    public function getBannedSessions( $limit = 0 ){
        (cmsDEBUG ? memoryUsage( 'Sessions: Getting all Banned Sessions')  : '');
        return $this->getSessionsByType( 'banned', $limit );
    }



    /**
     * Retrieves set of sessions according to the provided type
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     *
     * @param string $type  The type of session
     * @param int    $limit The limit on the query
     *
     * @return array
     */
    public function getSessionsByType( $type, $limit = 0 ){
        $sessions = array();

        // Ensure that $limit is a number
        $limit = ( is_number( $limit ) ? $limit : (int)$limit );

        // Check for valid session types
        if( $type != 'banned' && $type != 'active' && $type != 'update' ){
            (cmsDEBUG ? memoryUsage( 'Sessions: Session type invalid')  : '');
            return $sessions;
        }

        $query = $this->objSQL->queryBuilder()
                              ->select('uid', 'sid', 'mode', 'store', 'hostname')
                              ->from('#__sessions')
                              ->where('mode', '=', $type)
                              ->limit( $limit )
                              ->build();


        // Get the array of data
        $sessions = $this->objSQL->fetchAll( $query );
        (cmsDEBUG ? memoryUsage( 'Sessions: Got me some sessions, I do!')  : '');

        return $sessions;
    }

    /**
     * Cleans all expired sessions
     *
     * @author Richard Clifford
     * @since 1.0.0
     *
     * @version 1.0.0
     *
     * @param int $expire The timestamp when the sessions should expire
     *
     * @return bool
     */
    public function cleanSessions( $expire = 0 ){

        $expire = ( $expire === 0 ? time() : $expire );

        $query = $this->objSQL->queryBuilder()
                              ->deleteFrom('#__sessions')
                              ->where( sprintf( 'DATE_ADD(`timestamp`, INTERVAL %d SECOND) < NOW()', $expire ) )
                              ->build();

        $result = $this->objSQL->query($query);

        return $result;
    }

    /**
     * Renews a set of sessions specified as an array
     *
     * @author Richard Clifford
     * @since 1.0.0
     *
     * @version 1.0.0
     *
     * @param array $session The sessions array (keyed by uid and value as sid)
     *
     * @return array
     */
    public function renewSessions( $session = array() ){
        $session = ( !is_array( $session ) ? array( $session ) : $session );
        $renewedSessions = array();

        foreach( $session as $uid => $sid ){
            $this->killSession( $sid );

            // Ensure there is an array key and value
            if( is_empty( $sid ) || is_empty( $uid ) ){
                continue;
            }

            if( $this->getVar( $sid ) === false ){
                $renewedSessions[$uid] = $this->createSession( $uid );
            }
        }
        return $renewedSessions;
    }
}

?>