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

        $name = md5( 'CSCMS' . User::getIP() . cmsROOT );
        session_name( $name );

        session_start();

        $check = $this->checkValidSession();
        if( $check === false ){
            (cmsDEBUG ? memoryUsage('Sessions: User dosent have a valid session... ') : '');

            session_regenerate_id( true );

            $_SESSION = array();
            $_SESSION['session_start'] = time();

            $this->newSession();
        }

        $_SESSION['page_load'] = time();

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
            (cmsDEBUG ? memoryUsage('Sessions: Running Garbage Collector... ') : '');
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
        (cmsDEBUG ? memoryUsage('Sessions: Checking validity of users session... ') : '');
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
        (cmsDEBUG ? memoryUsage('Sessions: Updating timestamp on users session... ') : '');

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

        //$this->regenSessionID();

        $insert = array();
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
            $_SESSION['user']['userkey']    = $insert['sid'];

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
    public function loadSession(){
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

        if( $objSQL->affectedRows > 0 ){
            $_SESSION = unserialize( $results[0]['store'] );


            return true;
        }

        return false;
    }



    /**
     * Gets the form token for the sesson
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