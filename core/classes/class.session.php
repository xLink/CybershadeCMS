<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Session Class
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Dan Aldridge
 *
 * @todo    This Class is a direct port from the old 0.8 system, UPGRADE AT WILL >.<
 *
 */
class Core_Classes_Session extends Core_Classes_coreObj{

    /**
     * 
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function __construct(){ 

        $this->session_gc();
    }

    /**
     * Has a 25% chance at running the query to remove the old/inactive sessions from the database.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function session_gc(){
        if( rand(1, 100) <= 25 ){
            $objSQL = Core_Classes_coreObj::getDBO();

            $query = $objSQL->queryBuilder()
                ->deleteFrom('#__sessions')
                ->where( sprintf('timestamp < %d', time()-((60*60)*20)) )
                ->build();

            $objSQL->query( $query );
        }
    }

    /**
     * 
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string  $var       Parameter Description
     *
     */
    public function trackerInit(){
        
        $update = false; $rmCookie = false; $logout = false; $action = null;

        $objLogin = Core_Classes_coreObj::getLogin();
        $objSQL   = Core_Classes_coreObj::getDBO();
        $objUse   = Core_Classes_coreObj::getUser();

        // if the user is logged in
        if( Core_Classes_User::$IS_ONLINE ){
            $action = 'check user key, and reset if needed';
            if( is_empty( doArgs('userkey', null, $this->config('global', 'user')) ) ){
                //$this->newKey();
            }

            // force update
            $update = true;
        }else{
            // check for remember me
            if( !is_empty( doArgs('login', null, $_COOKIE) ) ){

                // try and remember who they are, this sometimes is hard, but we try anyway
                if( !$objLogin->rememberMe() ){
                    $action = 'remove remember me cookie';
                    $rmCookie = true;

                // you should be logged in now, so redirect 
                }else{
                    $action = 'remember me worked';

                    $objPage->redirect('', 1);
                    exit;
                }

            // no remember me found, lets treat them as a new guest
            }else{

                $online = $this->getData();
                if( !is_array($online) ){
                    $action = 'register new guest';
                    $this->newSession();
                }else{
                    $action = 'update guest';
                    $update = true;
                }
            }

        }

        if( $update === true ){

            // we haven't got any data about this user, lets get some
            if( !isset($online) ){
                $online = $this->getData();
            }

            // perform an action based on the database switch
            if( isset($online['mode']) ){
                (LOCALHOST ? debugLog($online['mode'], 'mode') : '');
                switch( strtolower($online['mode']) ){
                    default:
                    case 'active':
                        $action = 'update user location';

                        if( IS_ONLINE && $online['username'] == 'Guest' ){
                            $query = $objSQL->queryBuilder->deleteFrom('#__sessions')->where('userkey', '=', $objUser->grab('userkey'));
                            $objSQL->query( $query->build() );

                            $this->newSession();
                        }

                        $this->updateTime();
                    break;

                    case 'kill':
                        $action = 'kill user';

                        // and log em out
                        $logout = true;
                    break;

                    case 'ban':
                        $action = 'ban user';

                        // ban the user account if they are online
                        if( IS_ONLINE ){
                            $objUser->toggle( $objUser->grab('id'), 'ban', true );

                        // ban the ip if they are a guest
                        }else{
                            // TODO: sort this out
                            //$this->banIP( Core_Classes_User::getIP() );
                        }

                        $logout = true;
                    break;

                    case 'update':
                        $action = 'update user info';
                        //so we want to grab a new set of sessions
                        if( IS_ONLINE ){
                            $this->setSessions( $this->grab('id') );
                        }
                    break;
                }   
            }
        }

        (LOCALHOST ? debugLog($action, 'action') : '');
        (LOCALHOST ? debugLog($update, 'update') : '');

        return $this;
    }

    /**
     * Sets the sessions for the user
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   mixed  $uid         Username or ID
     * @param   bool   $autoLogin   
     *
     * @return  bool
     */
    public function setSessions( $uid, $autoLogin=false ) {
        
        // grab the user info
        $userInfo = $this->get( '*', $uid );
            if( !is_array($userInfo) || empty($userInfo) ){
                return false;
            }

        // grab the timestamp before we reset the session array
        $timestamp = doArgs('last_active', time(), $_SESSION['user']);

        $_SESSION['user'] = array();
        $_SESSION['user'] = $userInfo;
        $_SESSION['user']['last_active'] = $timestamp;

        // if we are auto logging in, then update last_active
        if( $autoLogin !== false ){
            $update['last_active'] = time();
            $objUser->update( $uid, $update );
        }
        return true;
    }

    /**
     * Runs an update on the users session to update their timestamp
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function updateTime(){

        $objSQL = Core_Classes_coreObj::getDBO();

        $update = array();
        $update['timestamp'] = time();

        $query = $objSQL->queryBuilder()
            ->update('#__sessions')
            ->set($update)
            ->where('admin',            '=', (Core_Classes_User::$IS_ADMIN ? '1' : '0'))
                ->andWhere('sid',       '=', md5( session_id() ) )
                ->andWhere('hostname',  '=', Core_Classes_User::getIP() )
            ->build();

        $results = $objSQL->query( $query );

        return ($objSQL->affectedRows() ? true : false);
    }


    /**
     * Creates a new user session
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function newSession(){
        $objSQL  = Core_Classes_coreObj::getDBO();
        $objUser = Core_Classes_coreObj::getUser();

        //$this->regenSessionID();

        $insert = array();
        $insert['uid']       = ( Core_Classes_User::$IS_ONLINE ? $objUser->grab('id') : 0);
        $insert['sid']       = md5( session_id() );
        $insert['hostname']  = $objSQL->escape( Core_Classes_User::getIP() );
        $insert['store']     = $objSQL->escape( serialize( $_SESSION ) );
        $insert['timestamp'] = time();
        $insert['useragent'] = $objSQL->escape( htmlspecialchars( $_SERVER['HTTP_USER_AGENT'] ) );
        $insert['mode']      = 'active';

        $query = $objSQL->queryBuilder()
            ->insertInto('#__sessions')
            ->set( $insert )
            ->build();

        $results = $objSQL->query( str_replace('INSERT INTO', 'REPLACE INTO', $query) );

        // Ensure the result is valid
        if( $results ){
            $_SESSION['user']['timestamp']  = (time() + 3600); // Give it an hour

            return true;
        }

        return false;
    }

    /**
     * Loads an already active session for this user
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function getData(){
        $objSQL = Core_Classes_coreObj::getDBO();

        $query = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__sessions')
            ->where('admin',            '=', (Core_Classes_User::$IS_ADMIN ? '1' : '0') )
                ->andWhere('sid',       '=', md5( session_id() ) )
                ->andWhere('hostname',  '=', Core_Classes_User::getIP() )
            ->build();

        $results = $objSQL->fetchLine( $query );

        if( $objSQL->affectedRows() > 0 ){
            return $results;
        }

        return false;
    }

    /**
     * Gets the form token for the session
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   bool $forceNew
     *
     * @return  string $token
     */
    public function getFormToken($forceNew=false){
        $objUser = Core_Classes_coreObj::getUser();

        return self::getToken($forceNew);
    }

    /**
     * Gets the token for the session
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   bool $forceNew
     *
     * @return  string $token
     */
    public function getToken($forceNew=false){
        if(empty($_SESSION['token']) || $forceNew){
            $token = randCode(12);
            $_SESSION['token'] = md5($token);
        }

        return $_SESSION['token'];
    }

    /**
     * Checks to the token against the session
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $formKey
     *
     * @return  bool
     */
    public function checkToken( $formKey ){
        // check if we are in post mode
        if( !HTTP_POST ){ return false; }

        // make sure the key they given us is there and not empty
        if( !isset($_POST[$formKey]) || is_empty($_POST[$formKey]) ){
            return false;
        }

        if( $_POST[$formKey] == $this->getToken() ){
            return true;
        }

        return false;
    }
}

?>