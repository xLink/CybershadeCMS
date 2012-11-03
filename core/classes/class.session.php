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

        $name = md5( 'CSCMS' . coreObj::getUser()->getIP() . cmsROOT );
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

    }

    /**
     * Has a 5% chance at running the query to remove the old/inactive sessions from the database.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function __destruct(){
        if( rand(1, 100) <= 25 ){
            (cmsDEBUG ? memoryUsage('Sessions: Running Garbage Collector... ') : '');
            $objSQL = coreObj::getDBO();

            $query = $objSQL->queryBuilder()
                            ->deleteFrom('#__sessions')
                            ->where('timestamp', '<', time()-((60*60)*20))
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
        $objUser = coreObj::getUser();

        $update = array();
        $update['timestamp'] = $objSQL->quote(time());

        $query = $objSQL->queryBuilder()
                        ->update('#__sessions')
                        ->set($update)
                        ->where('admin',            '=', ($objUser::$IS_ADMIN ? '1' : '0'))
                            ->andWhere('sid',       '=', sprintf('"%s"', md5( session_id() )) )
                            ->andWhere('hostname',  '=', sprintf('"%s"', $objUser->getIP()) )
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
        $objSQL = coreObj::getDBO();
        $objUser = coreObj::getUser();

        //$this->regenSessionID();

        $insert = array();
        $insert['`sid`']       = md5( session_id() );
        $insert['`hostname`']  = $objSQL->escape( $objUser->getIP() );
        $insert['`store`']     = $objSQL->escape( serialize( $_SESSION ) );
        $insert['`timestamp`'] = time();
        $insert['`useragent`'] = $objSQL->escape( htmlspecialchars( $_SERVER['HTTP_USER_AGENT'] ) );
        $insert['`mode`']      = 'active';

        $query = $objSQL->queryBuilder()
                        ->insertInto('#__sessions')
                        ->set( $insert )
                        ->build();

        $results = $objSQL->query( str_replace('INSERT INTO', 'REPLACE INTO', $query) );

        // Ensure the result is valid
        if( $result ){
            $_SESSION['user']['session_id'] = session_id();
            $_SESSION['user']['user_id']    = md5( $uid );
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
    public function loadSession(){
        $objSQL = coreObj::getDBO();
        $objUser = coreObj::getUser();

        $query = $objSQL->queryBuilder()
                        ->select('*')
                        ->from('#__sessions')
                        ->where('admin',            '=', ($objUser::$IS_ADMIN ? '1' : '0') )
                            ->andWhere('sid',       '=', md5( session_id() ) )
                            ->andWhere('hostname',  '=', $objUser->getIP() )
                        ->build();

        $results = $objSQL->fetchLine( $query );

        if( $objSQL->affectedRows > 0 ){
            $_SESSION = unserialize( $results[0]['store'] );


            return true;
        }

        return false;
    }

}

?>