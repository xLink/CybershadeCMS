<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 *
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Dan Aldridge
 *
 */
class Session extends coreObj{

    public $session_id = false;

    /**
     * Checks whether the session is valid & present in db
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function __construct(){

        //session_start();

        $check = $this->checkValidSession();
        if( $check === false ){
            if( isset($_SESSION)){
                $objSQL = coreObj::getDBO();

                $query = $objSQL->queryBuilder()
                    ->deleteFrom('#__sessions')
                    ->where('sid', '=', md5( session_id() ) )
                    ->build();

                $objSQL->query( $query );
            }

            session_regenerate_id( true );

            /*$_SESSION = array();*/
            $_SESSION['session_start']   = time();
            $_SESSION['user']['userkey'] = md5( session_id() );

            $this->newSession();
        }

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
            $objSQL = coreObj::getDBO();

            $query = $objSQL->queryBuilder()
                ->deleteFrom('#__sessions')
                ->where( sprintf('timestamp < %d', time()-((60*60)*20)) )
                ->build();

            $objSQL->query( $query );
        }
    }

    /**
     * Checks whether the session is valid & present in db
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function checkValidSession(){
        $session_id = session_id();

        if( $session_id ){
            $update = $this->updateTime();

            if( $update === false ){
                $this->setVar('session_id', '');
            }

            return $update;
        }

        return false;
    }

    /**
     * Runs an update on the users session to update their timestamp
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function updateTime(){

        $objSQL = coreObj::getDBO();

        $update = array();
        $update['timestamp'] = time();

        $query = $objSQL->queryBuilder()
            ->update('#__sessions')
            ->set($update)
            ->where('admin',            '=', (User::$IS_ADMIN ? '1' : '0'))
                ->andWhere('sid',       '=', md5( session_id() ) )
                ->andWhere('hostname',  '=', User::getIP() )
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
        $objSQL  = coreObj::getDBO();
        $objUser = coreObj::getUser();

        //$this->regenSessionID();

        $insert = array();
        $insert['uid']       = ( User::$IS_ONLINE ? $objUser->grab('id') : 0);
        $insert['sid']       = md5( session_id() );
        $insert['hostname']  = $objSQL->escape( User::getIP() );
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
        $objSQL = coreObj::getDBO();
        $objUser = coreObj::getUser();

        $query = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__sessions')
            ->where('admin',            '=', (User::$IS_ADMIN ? '1' : '0') )
                ->andWhere('sid',       '=', md5( session_id() ) )
                ->andWhere('hostname',  '=', User::getIP() )
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
        $objUser = coreObj::getUser();

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