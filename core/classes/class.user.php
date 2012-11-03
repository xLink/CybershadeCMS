<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class User extends coreObj {

/**
  //
  //--
  //
**/

    //some static vars
    static  $IS_ONLINE  = false;

    static  $IS_ADMIN   = false,
            $IS_MOD     = false,
            $IS_USER    = false,
            $IS_SPECIAL = false; // For various, custom permissions

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
    public function setIsOnline( $value=true ){
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
        self::$IS_ADMIN     = $this->checkPermissions($this->grab('id'), ADMIN);
        self::$IS_MOD       = $this->checkPermissions($this->grab('id'), MOD);
    }

/**
  //
  //-- Information Functions
  //
**/

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
    public function get( $uid, $fields=array() ){

        // test to see if we already have the info avaliable
        if( isset($this->userInfo[strtolower($uid)]) ){

            $info = $this->userInfo[strtolower($uid)];

            if($info === false){
                $this->setError('$info was set to false.');
                return false;
            }

        // we don't so we shall grab it
        } else {

            $objSQL = coreObj::getDBO();
            //figure out if they gave us a username or a user id
            $user = (is_number($uid) ? 'u.id = %s' : 'upper(u.username) = %s');
            $x    = sprintf($user, strtoupper($uid));

            $query = $objSQL->queryBuilder()
                ->select(array('u.*', 'ux.*', 'id'=>'u.id', 's.timestamp'))
                ->from(array('u' => '#__users'))
                ->leftJoin(array('ux' => '#__users_extras'))
                    ->on('u.id = ux.uid')
                ->leftJoin(array('s' => '#__sessions'))
                    ->on('u.id = s.uid')
                ->where($x)
                ->limit(1)
                ->build();

            $results = $objSQL->fetchLine($query);

            // If we have no results, we will set false & have it cache it too
            // any subsequent checks will be auto failed.
            if( !$results || !count($results) ){
                $this->userInfo[strtolower($uid)] = false;
                $this->setError('Could not retreive information about the user.');
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
            $this->setError('Could not retreive information about the user.');
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
                    $this->setError('Could not find the field you were looking for.');
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
                $this->setError('Could not find the field you were looking for.');
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

        $return = $this->get($uid, 'username');
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

        $return = $this->get($username, 'id');
        return ($return === false ? 0 : $return);
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
            $this->setError('Username dosen\'t fall within usable length parameters. Between 2 and 25 characters long.');
            return false;
        }

        if( preg_match('~[^a-z0-9_\-@^]~i', $username) ){
            $this->setError('Username dosen\'t validate. Please ensure that you are using no special characters etc.');
            return false;
        }

        if( $exists === true && $this->get($username, 'username') === false ){
            $this->setError('Username alerady exists. Please make sure your username is unique.');
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
        $getAjax = $this->get( $uid, 'ajax_settings' );

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
        if      ($_SERVER['HTTP_X_FORWARDED_FOR']){ $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
        else if ($_SERVER['HTTP_X_FORWARDED']){     $ip = $_SERVER['HTTP_X_FORWARDED']; }
        else if ($_SERVER['HTTP_FORWARDED_FOR']){   $ip = $_SERVER['HTTP_FORWARDED_FOR']; }
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
                                ->update( array( 'u' => '#__users' ) )
                                ->set( $userData )
                                ->where( 'u.id', '=', $uid )
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
                                        ->update( array( 'ux' => '#__users_extras') )
                                        ->set( $userExtraData )
                                        ->where( 'ux.uid','=', $uid )
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

    public function setPassword( $uid, $password ){

    }

    public function toggle( $var, $state=null ){

    }

/**
  //
  //-- Functions
  //
**/

    public function register( array $userInfo ){

    }

    public function canLogin( $username, $password ){

    }

    public function mkPassword( $password, $salt ) {
        $objPass = new phpass( 8, true );

        $hashed = $objPass->hashPassword( $salt . $password );

        if( $hashed < 20  ) {

        }
    }

    public function verifyUserCredentials( $username, $password ) {
        $objSQL = coreObj::getDBO();

        // Grab the phpass library
        $phpass = new phpass( 8, true );

        // Grab the user's id
        $uid = $this->getIDByUsername( $username );

        // if the username doesn't exist, return false;
        if( $uid === 0 ) {
            return false;
        }

        // Fetch the hashed password from the database
        $hash = $this->get( $uid, 'password' );
        if( $phpass->CheckPassword( $password, $hash ) ) {
            return true;

        } else {
            return false;

        }
    }
/**
  //
  //-- Auth Functions
  //
**/

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
    public function checkUserAuth( $type, $key, $u_access, $is_admin ){
        $auth_user = 0;

        if(count($u_access)){
            for($j = 0; $j < count($u_access); $j++){
                $result = 0;
                switch($type){
                    case AUTH_ACL:   $result = $u_access[$j][$key]; break;
                    case AUTH_MOD:   $result = $result || $u_access[$j]['auth_mod']; break;
                    case AUTH_ADMIN: $result = $result || $is_admin; break;
                }
                $auth_user = $auth_user || $result;
            }
        }else{
            $auth_user = $is_admin;
        }
        return $auth_user;
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
        if(is_empty($group) || $group == 0 || $group == GUEST){
            return true;
        }

        //check to see whether we have a user id to check against..
        if(is_empty($uid)){
            return false;
        }

        //grab the user level if possible
        $userlevel = GUEST;
        if(self::$IS_ONLINE){
            $userlevel = $this->getUserInfo($uid, 'userlevel');
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