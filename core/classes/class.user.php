<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class User extends coreObj {

    //some static vars
    static  $IS_ONLINE  = false;

    static  $IS_ADMIN   = false,
            $IS_MOD     = false,
            $IS_USER    = false,
            $IS_SPECIAL = false; // For various, custom permissions


    public function __construct(){

        $guest['user'] = array(
            'id'        => 0,
            'username'  => 'Guest',
            'theme'     => $this->config('site', 'theme'),
            'timezone'  => isset($_SESSION['user']) ? doArgs('timezone', $this->config('time', 'timezone'), $_SESSION['user']) : $this->config('time', 'timezone'),
            'userkey'   => doArgs('userkey', null, $_SESSION['user']),
        );

        self::addConfig(array(
            'global' => array(
                'user'      => ( isset($_SESSION['user']['id']) ? $_SESSION['user'] : $guest['user']),
                'ip'        => User::getIP(),
                'useragent' => doArgs('HTTP_USER_AGENT', null, $_SERVER),
                'browser'   => getBrowser($_SERVER['HTTP_USER_AGENT']),
                'platform'  => Page::getCSSSelectors($_SERVER['HTTP_USER_AGENT']),
                'language'  => 'en', //$language,
                'secure'    => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? true : false ),
                'referer'   => doArgs('HTTP_REFERER', null, $_SERVER),
                'realPath'  => realpath('').'/',
                'rootPath'  => '/'.root(),
                'fullPath'  => $_SERVER['REQUEST_URI'],
                'rootUrl'   => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/'.root(),
                'url'       => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            )
        ), 'user');

        $user = $this->config('global', 'user');

        $this->setIsOnline(!($user['id'] == 0 ? true : false));
        $this->initPerms();
    }

    /**
     * Sets the current user to online
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   bool $value
     *
     * @return  bool
     */
    public function setIsOnline( $value = true ){
        return self::$IS_ONLINE = $value;
    }

    /**
     * Defines global CMS permissions
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    public function initPerms(){
        self::$IS_USER      = $this->checkPermissions($this->grab('id'), USER);
        self::$IS_MOD       = $this->checkPermissions($this->grab('id'), MOD);
        self::$IS_ADMIN     = $this->checkPermissions($this->grab('id'), ADMIN);
    }

/**
  //
  //-- Information Functions
  //
**/

    /**
     * Returns a setting's value set on the current user
     *
     * @version 2.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $setting
     *
     * @return  mixed
     */
    public function grab($setting){
        return doArgs($setting, false, self::$_config['global']['user']);
    }


    /**
     * Retreives information about the $uid.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   mixed   $uid        Can either be the User ID, or Username.
     * @param   mixed   $fields     Can either be the single field.
     *
     * @return  mixed
     */
    public function get( $fields=array(), $uid=0 ){
        if( $uid === 0 ){
            return 0;
        }

        // test to see if we already have the info avaliable
        if( isset($this->userInfo[strtolower($uid)]) ){

            $info = $this->userInfo[strtolower($uid)];

            if($info === false){
                return false;
            }

        // we don't so we shall grab it
        } else {

            $objSQL = coreObj::getDBO();

            //figure out if they gave us a username or a user id
            $user   = (is_number($uid) ? 'u.id = %s' : 'upper(u.username) = "%s"');
            $clause = sprintf($user, strtoupper($uid));

            $query  = $objSQL->queryBuilder()
                ->select(array('u.*', 'ux.*', 'id'=>'u.id', 's.timestamp'))
                ->from(array('u' => '#__users'))
                ->leftJoin(array('ux' => '#__users_extras'))
                    ->on('u.id = ux.uid')
                ->leftJoin(array('s' => '#__sessions'))
                    ->on('u.id = s.uid')
                ->where( $clause )
                ->limit(1)
                ->build();

            $results = $objSQL->fetchLine($query);

            // If we have no results, we will set false & have it cache it too
            // any subsequent checks will be auto failed.
            if( $results === false || !count($results) ){
                $this->userInfo[strtolower($uid)] = false;
                trigger_error('Could not retreive information about the user - '.$uid);
                return false;
            }

            // cache it under the uid && the username
            // so if they request 0 || admin, it is already cached :)
            $this->userInfo[strtolower($results['username'])] = $results;
            $this->userInfo[$results['id']] = $results;

            unset($query);
            $info = $results;

        }

        if( !count($info) ){
            trigger_error('Could not retreive information about the user - '.$uid);
            return false;
        }

        if( is_array($fields) && count($fields) ){

            if( count($fields) == 1 ){

                // if we have * as the first array value, return all the things!
                if( $fields[0] == '*' ){
                    return $info;
                }

                // make sure the field is set in the array
                if( isset( $info[$fields[0]] ) ){
                    return $info[$fields[0]];
                }else{
                    trigger_error('Could not find the field you were looking for.');
                    return false;
                }

            }else{
                $return = array();
                foreach( $fields as $field ){
                    if( !isset($info[$field]) ){
                        continue;
                    }

                    $return[$field] = $info[$field];
                }

                return $return;
            }

        }

        // if $fields is a string, and its *, then return all the things also!
        if( is_string($fields) ){

            if( $fields == '*' ){
                return $this->userInfo[strtolower($uid)];
            }

            if( isset($info[$fields]) ){
                return $info[$fields];
            }else{
                trigger_error('Could not find the field you were looking for.');
                return false;
            }

        }

        // if we get this far, may aswell return everything
        return $info;
    }


    /**
     * Retreives a Username by the given ID.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int   $uid
     *
     * @return  string
     */
    public function getUsernameByID( $uid=0 ){
        if( is_empty($uid) || !is_number($uid) || $uid == 0){
            return false;
        }

        $return = $this->get('username', $uid);
        return ($return === false ? 'Guest' : $return);
    }


    /**
     * Retreives a ID by the given username.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string   $username
     *
     * @return  int
     */
    public function getIDByUsername( $username ){
        if( is_empty($username) || !$this->validateUsername($username, true) ){
            return 0;
        }

        $return = $this->get('id', $username);
        return ($return === false ? 0 : $return);
    }

    /**
     //
     // -- FINISH FUNCTION
     //
     */
    public function getLanguage(){
        /*
            $language = doArgs('language', 'en', $config['site']);
            $langDir = cmsROOT.'languages/';

            if(isset($_SESSION['user']['language'])){
                if(is_dir($langDir.$_SESSION['user']['language'].'/') &&
                   is_readable($langDir.$_SESSION['user']['language'].'/main.php')){
                        $language = $_SESSION['user']['language'];
                }
            }

            if(is_dir($langDir.$language.'/') || is_readable($langDir.$language.'/main.php')){
                translateFile($langDir.$language.'/main.php');
            }else{
                msgDie('FAIL', sprintf($errorTPL, 'Fatal Error',
                    'Cannot open '.($langDir.$language.'/main.php').' for include.'));
            }
        */
    }

    /**
     * Validates a Username to ensure it's the correct charset and to ensure it doesn't already exist
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string   $username
     * @param   bool     $exists
     *
     * @return  int
     */
    public function validateUsername( $username, $exists=false ){

        if( strlen($username) > 25 || strlen($username) < 2 ){
            trigger_error('Username dosen\'t fall within usable length parameters. Between 2 and 25 characters long.');
            return false;
        }

        if( preg_match('~[^a-z0-9_\-@^]~i', $username) ){
            trigger_error('Username dosen\'t validate. Please ensure that you are using no special characters etc.');
            return false;
        }

        if( $exists === true && $this->get('username', $username) === false ){
            trigger_error('Username alerady exists. Please make sure your username is unique.');
            return false;
        }

        return true;
    }


    /**
     * Recieves specified ajax settings
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   string   $setting   The key name of the setting ('all' if all is required)
     * @param   int      $uid       The User id to get the settings for
     *
     * @return  array
     */
    public function getAjaxSetting( $setting, $uid = null ){
        if( is_empty( $setting ) ){
            return array();
        }

        $objSQL = coreObj::getDBO();

        $uid = ( is_null( $uid ) ? $objSession->getCurrentUser() : $uid );

        // Do the query
        $getAjax = $this->get( 'ajax_settings', $uid );

        // If the query was successful and the array is not empty
        if( $getAjax && !is_empty( $getAjax ) ){

            // Retrieve all settings
            if( $setting === 'all' ){
                return unserialize($getAjax);
            }

            // Retrieved specified key of settings
            return ( isset( $getAjax[$setting] ) ? unserialize($getAjax[$setting]) : array() );
        }

        return array();
    }

    /**
     * Gets the remote external IP address
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  array
     */
    public static function getIP(){
        if      ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
        else if ( array_key_exists( 'HTTP_X_FORWARDED',     $_SERVER ) ) {  $ip = $_SERVER['HTTP_X_FORWARDED'];     }
        else if ( array_key_exists( 'HTTP_FORWARDED_FOR',   $_SERVER ) ) {  $ip = $_SERVER['HTTP_FORWARDED_FOR'];   }
        else{                                       $ip = $_SERVER['REMOTE_ADDR']; }

        if( $ip == '::1' ){ $ip = '127.0.0.1'; }
        return $ip;
    }

/**
  //
  //-- Update Infomation Functions
  //
**/

    /**
     * Updates user settings in both 'users' and 'users_extras' tables
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int     $uid
     * @param   array   $settings
     *
     * @return  bool
     */
    public function update( $uid, array $settings ){
        unset($settings['id'], $settings['uid'], $settings['password']);

        if( !count($settings) ){
            trigger_error('No settings detected, the follwing columns are blacklisted from being'
                        .'updated within this function: id, uid, password, pin');
            return false;
        }

        $objSQL = coreObj::getDBO();

        // get the column names to check against
        $userColumnData      = $objSQL->fetchColumnData( '#__users', 'Field' );
        $userExtraColumnData = $objSQL->fetchColumnData( '#__users_extras', 'Field' );
        $keys                = array_keys( $settings );

        $userData = $userExtraData = array();

        if( is_empty( $keys ) ){
            return false;
        }

        // Loop through the settings keys
        foreach( $keys as $key ){

            // Check if the keys belong to users_extras table or users_extras table
            if( in_array( $key, $userColumnData ) ){
                $userData[$key] = $settings[$key];
            }

            if( in_array( $key, $userExtraColumnData ) ){
                $userExtraData[$key] = $settings[$key];
            }
        }

        // Check if the userData is empty
        // If it isn't then update the table
        if( !is_empty( $userData ) ){

            $insert = $objSQL->queryBuilder()
                ->update( '#__users' )
                ->set( $userData )
                ->where( 'id', '=', $uid )
                ->build();

            $userInsertResult = $objSQL->query( $insert );

            if( !$userInsertResult ){
                trigger_error( 'Could not update the users table' );
                return false;
            }
        }
        // Check if the userExtraData is empty
        // If it isn't then update the table
        if( !is_empty( $userExtraData ) ){

            $insertExtras = $objSQL->queryBuilder()
                ->update( '#__users_extras' )
                ->set( $userExtraData )
                ->where( 'uid', '=', $uid )
                ->build();

            $userExtrasInsertResult = $objSQL->query( $insertExtras );

            if( !$userExtrasInsertResult ){
                trigger_error( 'Could not update the users extras table' );
                return false;
            }
        }

        // return true if everything goes well
        return true;
    }


    /**
     * Sets a user password to a new value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int    $uid
     * @param   string $password
     *
     * @return  bool
     */
    public function setPassword( $uid, $password ){
        if( is_empty( $password ) ){
            return false;
        }
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
            ->update('#__users')
            ->set( array(
                'password' => $this->mkPassword( $password )
            ))
            ->where('id', '=', $uid)
            ->build();

        $result = $objSQL->query( $query );

        if( $result ){
            return true;
        }

        return false;
    }

    /**
     * Toggles a boolean setting in the user row.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   int    $uid
     * @param   string $var
     * @param   bool   $state
     *
     * @return  bool
     */
    public function toggle( $uid, $var, $state = null){
        $objSQL = coreObj::getDBO();

        $userColumnData      = $objSQL->fetchColumnData( '#__users', 'Field' );
        $userExtraColumnData = $objSQL->fetchColumnData( '#__users_extras', 'Field' );

        // if state hasnt been set, then lets toggle its value
        if( $state === null){
            $state = sprintf('IF(%s=1, 0, 1)', $var);

        // if we want to toggle it to a specific value then we need to set it
        } else {
            $state = ($state === true ? '1' : '0');
        }

        if( in_array( $var, $userColumnData ) ){
            $query = $objSQL->queryBuilder()
                ->update('#__users')
                ->set(array(
                    $var => $state
                ))
                ->where('id', '=', $uid)
                ->build();

            $result = $objSQL->query( $query );

            if( $result === false ){
                return false;
            }
        }

        if( in_array( $var, $userExtrasColumnData ) ){
            $query = $objSQL->queryBuilder()
                ->update('#__users_extras')
                ->set(array(
                    $var => $state
                ))
                ->where('id', '=', $uid)
                ->build();

            $result = $objSQL->query( $query );

            if( $result === false ){
                return false;
            }
        }

        return true;
    }

/**
  //
  //-- Functions
  //
**/

    public function register( array $userInfo ){
        if( is_empty( $userInfo ) ){
            return false;
        }

        $objSQL = coreObj::getDBO();

        $userColumnData      = $objSQL->fetchColumnData( '#__users', 'Field' );
        $userExtraColumnData = $objSQL->fetchColumnData( '#__users_extras', 'Field' );
        $keys                = array_keys( $settings );

        $userData = $userExtraData = array();

        if( is_empty( $keys ) ){
            return false;
        }

        // Loop through the settings keys
        foreach( $keys as $key ){

            // Check if the keys belong to users_extras table or users_extras table
            if( in_array( $key, $userColumnData ) ){
                $userData[$key] = sprintf( getTokenType( $settings[$key] ), $settings[$key]);
            }

            if( in_array( $key, $userExtraColumnData ) ){
                $userExtraData[$key] = sprintf( getTokenType( $settings[$key] ), $settings[$key]);
            }
        }

        // Check if the userData is empty
        // If it isn't then update the table
        if( !is_empty( $userData ) ){

            $insert = $objSQL->queryBuilder()
                ->insertInto('#__users')
                ->set( $userData )
                ->build();

            $userInsertResult = $objSQL->query( $insert );

            if( !$userInsertResult ){
                trigger_error( 'Could not update the users table' );
                return false;
            }
        }

        // Check if the userExtraData is empty
        // If it isn't then update the table
        if( !is_empty( $userExtraData ) ){

            $insertExtras = $objSQL->queryBuilder()
                ->insertInto('#__users_extras')
                ->set( $userExtraData )
                ->build();

            $userExtrasInsertResult = $objSQL->query( $insertExtras );

            if( !$userExtrasInsertResult ){
                trigger_error( 'Could not update the users extras table' );
                return false;
            }
        }

        // Send Confirmation mail
        $siteName  = $this->config( 'site', 'title' ); // Needs to be updated correctly
        $siteEmail = sprintf('no-reply@%s', $this->config( 'site', 'url' ));
        $message   = sprintf( "Dear %s,\n\r
            Thank you for registering for %s\n\r",
            $userData['username'],
            $siteName );

        $mail = _mailer( $userData['email'], $siteEmail, sprintf('Registration Details From %s', $siteName ), $message  );

        return $mail;
    }


    /**
     * Makes a secure password
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public function mkPassword( $password, $salt='' ) {
        $objPass = new phpass( 8, true );

        $hashed = $objPass->hashPassword( $salt . $password );

        if( strlen($hashed) >= 20 ) {
            return $hashed;
        }

        // Not sure if this is what is wanted
        return '';
    }


    /**
     * Verifies a Users Credentials to ensure they are valid
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function verifyUserCredentials( $username, $password ) {
        $objSQL = coreObj::getDBO();

        // Grab the user's id
        $uid = $this->getIDByUsername( $username );

        // if the username doesn't exist, return false;
        if( $uid === 0 ) {
            return false;
        }

        // Grab the phpass library
        $phpass = new phpass( 8, true );

        // Fetch the hashed password from the database
        $hash = $this->get( 'password', $uid );
        if( $phpass->CheckPassword( $password, $hash ) ) {
            return true;
        }

        return false;
    }

    /**
     * Returns permission state for given user and group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int     $uid        UserID
     * @param   int     $group      GUEST, USER, MOD, or ADMIN
     *
     * @return  bool    True/False on successful check, -1 on unknown group
     */
    public function checkPermissions( $uid, $group=0 ) {
        $group = (int)$group;

        //make sure we have a group to check against
        if( is_empty($group) || $group == 0 || $group == GUEST ){
            return true;
        }

        //check to see whether we have a user id to check against..
        if (is_empty($uid) ){
            return false;
        }

        //grab the user level if possible
        $userlevel = GUEST;
        if( self::$IS_ONLINE ){
            $userlevel = $this->grab('userlevel');
        }

        //see which group we are checking for
        switch($group){
            case GUEST:
                if(!self::$IS_ONLINE){
                    return true;
                }
            break;

            case USER:
                if(self::$IS_ONLINE){
                    return true;
                }
            break;

            case MOD:
                if($userlevel == MOD){
                    return true;
                }
            break;

            case ADMIN:
                if($userlevel == ADMIN){
                    if(LOCALHOST || doArgs('adminAuth', false, $_SESSION['acp'])){
                        return true;
                    }
                }
            break;

            //no idea what they tried to check for, so we'll return something unexpected too
            default: return -1; break;
        }

        //if we are an admin then give them mod powers regardless
        if(($group == MOD || $group == USER) && $userlevel == ADMIN){
            return true;
        }

        //apparently the checks didnt return true, so we'll go for false
        return false;
    }

}

?>