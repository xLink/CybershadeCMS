<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class session extends coreObj{

    public function __construct($store='none', $options = array()){
        $this->objSQL = coreObj::getDBO();
    }

    public function __destruct(){
        echo dump($a, 'DESTRUCTED!');
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
     * @return bool 
     */
    public function createSession( $status = 'active' ){
        $session_id = randCode(32);

        // Just a check
        if( empty( $session_id ) ){
            $offset = rand( 1, 86400 );
            $session_id = md5( time() + $offset );
        }

        // Set the var
        $this->setVar( 'session_id', $session_id );
        

        $check = $this->objSQL->queryBuilder()
                              ->select('sid')
                              ->from('#__sessions')
                              ->where('sid', '=', $session_id)
                              ->build();

        $checkResult = $this->objSQL->query( $check );

        // Ensure the current session_id is not in use
        if( count( $checkResult ) === 0 ){

            // Explicitly set the variable
            $values = array();

            // Values to insert into db
            $values['uid']       = 0;
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
                return true;
            }

        } else {
            // Recreate the session_id and perform all the previous checks
            // Need $this ?
            (cmsDEBUG ? memoryUsage( 'Sessions: Generated Session ID was not Unique, Recreating... ') : '');
            return $this->createSession( $status );
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

    }

    /**
     * Retrieves a session 
     *
     * @author  Richard Clifford
     * @since   1.0.0
     *
     * @version 1.0.0
     * 
     * @return bool
     */
    public function getSession( $session_id ){
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
        $result = $this->objSQL->query( $query );

        (cmsDEBUG ? memoryUsage( sprintf('Sessions: Returning: %s', $return ) ) : '');
        if( $result ){
            return $result;
        }

        return '';
    }
}

?>