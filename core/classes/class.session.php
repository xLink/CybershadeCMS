<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Session extends coreObj{

    public function __construct($store='none', $options = array()){
    }

    public function __destruct(){
    }

    /**
     * Gets the form token for the sessoni
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Dan Aldridge, Richard Clifford
     *
     * @param   bool $forceNew
     *
     * @return  string $token
     */
    public function getFormToken($forceNew=false){
        $objUser = coreObj::getUser();

        return $objUser->mkHash($objUser->get('id', 0) . self::getToken());
    }

    /**
     * Gets the token for the session
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Dan Aldridge, Richard Clifford
     *
     * @param   bool $forceNew
     *
     * @return  string $token
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
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int     $uid      The User ID of the user to create the session for
     * @param   string  $status   The state of the session
     *
     * @return bool
     */
    public function createSession( $uid = 0, $status = 'active'  ){
        if( ( isset($_SESSION['user']['session_id'])
                && isset( $_SESSION['user']['timestamp'] ) )
                && $_SESSION['user']['ts'] > time() ){
            return false;
        }

        $objSQL   = coreObj::getDBO();
        // echo dump( $objSQL );
        $uid = ( !is_number( $uid ) ? false : $uid );

        if( $uid === false ){
            (cmsDEBUG ? memoryUsage( 'Sessions: $uid was not a number, returning false ') : '');
            return false;
        }

        // Checks to see if the user has already got a session
        $userSession = $this->checkUserSession( $uid );

        if( $userSession ){
            return true;
        }

        $session_id = randCode(32);

        // Just a check
        if( empty( $session_id ) ){
            $offset = rand( 1, 86400 );
            $session_id = md5( time() + $offset );
        }

        $check = $objSQL->queryBuilder()
                              ->select('sid')
                              ->from('#__sessions')
                              ->where('sid', '=', $session_id)
                                ->andWhere('hostname', '=', $objUser->getIP())
                              ->build();


        $checkResult = $objSQL->fetchAll( $check );

        // Checks if the result is in the array of sessions
        if( in_array( $session_id, $checkResult ) ){

            $getAllSessions = $objSQL->queryBuilder()
                                           ->select('sid')
                                           ->from('#__sessions')
                                           ->build();

            // Get all sessions
            $sessions = $objSQL->fetchAll( $getAllSessions );

            // Ensure the current session_id is not in use
            while( in_array( $session_id, $sessions ) ){
                (cmsDEBUG ? memoryUsage('Sessions: Generated Session ID was not Unique, Recreating... ') : '');
                $session_id = randCode(32);
            }
        }

        // Set the var
        $this->setVar( 'sid', $session_id );

        // Explicitly set the variable
        $values = array();

        // Values to insert into db
        $values['uid']       = $uid;
        $values['sid']       = $session_id;
        $values['store']     = serialize( $_SESSION );
        $values['hostname']  = $objUser->getIP();
        $values['timestamp'] = time();
        $values['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $values['mode']      = $status;

        (cmsDEBUG ? memoryUsage('Sessions: Lets save the session!') : '');
        $query = $objSQL->queryBuilder()
                        ->insertInto('#__sessions')
                        ->set($values)
                        ->build();

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Executing Query: %s', $query )) : '');
        $result = $objSQL->query( $query );

        // Ensure the result is valid
        if( $result ){
            $_SESSION['user']['session_id'] = $session_id;
            $_SESSION['user']['user_id']    = md5( $uid );
            $_SESSION['user']['timestamp']  = (time() + 3600); // Give it an hour

            return true;
        }

        return false;
    }


    /**
     * Kills all the sessions for whatever reason
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @return bool
     */
    public function killAllSessions(){

        $query = $objSQL->queryBuilder()
                              ->deleteFrom('#__sessions')
                              ->where('1 = 1')
                              ->build();

        (cmsDEBUG ? memoryUsage(sprintf('Sessions: Executing Query: %s', $query) ): '');
        $result = $objSQL->query( $query );

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
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @todo    Rewrite
     *
     * @param   string  $session_id
     *
     * @return  bool
     */
    public function killSession( $session_id ){
        // Check the session id for type
        $session_id = ( ctype_alnum( $session_id ) ? $session_id : false  );

        // If wrong type then return false
        if( $session_id === false ){
            return false;
        }

        // Build the query
        $query = $objSQL->queryBuilder()
                              ->deleteFrom('#__sessions')
                              ->where('sid', '=', $session_id)
                              ->build();

        $result = $objSQL->query( $query );

        if( $query === false ){
            return false;
        }

        // Unset the session id
        unset( $_SESSION['user'][$session_id] ); // Won't Work Needs to be rewritten

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Deleted session: %s ', $session_id ) ) : '');
        return true;
    }

    /**
     * Retrieves a session
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   string  $session_id
     *
     * @return  array
     */
    public function getSessionById( $session_id ){
        $session_id = ( ctype_alnum((string)$session_id) ? $session_id : '' );

        if( empty( $session_id ) ) {
            return false;
        }

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Executing Query: %s', $query ) ) : '');
        // Build the query
        $query = $objSQL->queryBuilder()
                              ->select('uid', 'sid', 'mode', 'store', 'hostname')
                              ->from('#__sessions')
                              ->where('sid', '=', $session_id)
                              ->limit(1)
                              ->build();

        // Execute the query
        $sessions = $objSQL->fetchLine( $query );

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Returning: %s', $return ) ) : '');
        if( $sessions ){
            return $sessions;
        }

        return array();
    }

    /**
     * Checks whether a user has a session record and optionally kill it
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @todo    Rewrite
     *
     * @param   int  $user_id
     *
     * @return  bool
     */
    public function checkUserSession( $user_id ){
        $objSQL  = coreObj::getDBO();
        $objUser = coreObj::getUser();

        $sql = $objSQL->queryBuilder()
                            ->select('sid', 'uid', 'timestamp')
                            ->from('#__sessions')
                            ->where('uid', '=', $user_id)
                                ->andWhere('hostname', '=', $objUser->getIP())
                            ->limit(1)
                            ->build();

        $result = $objSQL->fetchLine( $sql );

        if(count($result) === 0){
            return false;
        }

        return true;
    }

    /**
     * Retrieves a set of active sessions
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int $limit The limit on the query
     *
     * @return  array
     */
    public function getActiveSessions( $limit = 0 ){
        (cmsDEBUG ? memoryUsage('Sessions: Getting all Active Sessions')  : '');
        return $this->getSessionsByType('active', $limit);
    }


    /**
     * Retrieves a set of Banned Sessions
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int $limit The limit on the query
     *
     * @return array
     */
    public function getBannedSessions( $limit = 0 ){
        (cmsDEBUG ? memoryUsage('Sessions: Getting all Banned Sessions')  : '');
        return $this->getSessionsByType('banned', $limit);
    }



    /**
     * Retrieves set of sessions according to the provided type
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   string $type  The type of session
     * @param   int    $limit The limit on the query
     *
     * @return  array
     */
    public function getSessionsByType( $type, $limit = 0 ){
        $sessions = array();

        // Ensure that $limit is a number
        $limit = ( is_number( $limit ) ? $limit : (int)$limit );

        // Check for valid session types
        if( $type != 'banned' && $type != 'active' && $type != 'update' ){
            (cmsDEBUG ? memoryUsage('Sessions: Session type invalid')  : '');
            return $sessions;
        }

        $query = $objSQL->queryBuilder()
                              ->select('uid', 'sid', 'mode', 'store', 'hostname')
                              ->from('#__sessions')
                              ->where('mode', '=', $type)
                              ->limit( $limit )
                              ->build();


        // Get the array of data
        $sessions = $objSQL->fetchAll( $query );
        (cmsDEBUG ? memoryUsage('Sessions: Got me some sessions, I do!')  : '');

        return $sessions;
    }

    /**
     * Cleans all expired sessions
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int $expire The timestamp when the sessions should expire
     *
     * @return  bool
     */
    public function cleanSessions( $expire = 0 ){

        $blOut = false;
        $objSQL = coreObj::getDBO();
        $expire = ( $expire === 0 ? time() : $expire );

        $getSessions = $objSQL->queryBuilder()
                                ->select('sid', 'uid')
                                ->from('#__sessions')
                                ->where('DATE_ADD(`timestamp`, INTERVAL '. $expire .' SECOND) < NOW()')
                                ->build();

        $sessions = $getSessions->fetchAll();

        if( !is_array( $sessions ) || count( $sessions, COUNT_RECURSIVE ) ){
            return $blOut;
        }

        foreach( $sessions as $session ){
            if( isset( $session['uid'] ) && isset( $session['sid'] ) ){
                // Kill the sessions
                $kill = $this->killSession( $session );
                if( $kill ){
                    $blOut = true;
                }
            }
        }

        return $blOut;
    }

    /**
     * Renews a set of sessions specified as an array
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @todo    Rewrite
     *
     * @param   array $session The sessions array (keyed by uid and value as sid)
     *
     * @return  array
     */
    public function renewSessions( $session = array() ){
        $session         = ( !is_array( $session ) ? array( $session ) : $session );
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