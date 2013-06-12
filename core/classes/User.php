<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
namespace CSCMS\Core\Classes;
use Hautelook\Phpass\PasswordHash;
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class User extends coreObj {

    //some static vars
    static  $IS_ONLINE  = false;

    static  $IS_ADMIN   = false,
            $IS_USER    = false,
            $IS_SPECIAL = false; // For various, custom permissions


    public function __construct(){

        $guest['user'] = array(
            'id'        => 1,
            'username'  => 'Guest',
            'theme'     => $this->config('site', 'theme'),
            'timezone'  => isset($_SESSION['user']) ? doArgs('timezone', $this->config('time', 'timezone'), $_SESSION['user']) : $this->config('time', 'timezone'),
            'userkey'   => doArgs('userkey', null, $_SESSION['user']),
        );

        // Get the Page Object
        $objPage = coreObj::getPage();

        self::addConfig(array(
            'global' => array(
                'user'      => ( isset($_SESSION['user']['id']) ? $_SESSION['user'] : $guest['user']),
                'ip'        => User::getIP(),
                'useragent' => doArgs('HTTP_USER_AGENT', null, $_SERVER),
                'browser'   => getBrowser($_SERVER['HTTP_USER_AGENT']),
                'platform'  => $objPage->getCSSSelectors($_SERVER['HTTP_USER_AGENT']),
                'language'  => $this->config('site', 'language'),
                'secure'    => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? true : false ),
                'referer'   => doArgs('HTTP_REFERER', null, $_SERVER),
                'realPath'  => realpath('').'/',
                'rootPath'  => '/'.root(),
                'fullPath'  => doArgs('REQUEST_URI', null, $_SERVER),
                'siteDomain'=> doArgs('HTTP_HOST', null, $_SERVER),
                'siteUrl'   => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'],
                'rootUrl'   => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/'.root(),
                'url'       => ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === true ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            )
        ), 'user');

        $user = $this->config('global', 'user');

        $this->setIsOnline(!($user['id'] == 1 ? true : false));
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
     * Retrieves information about the $uid.
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

        // if we get this far, may as well return everything
        return $info;
    }

    /**
     * Makes a Username up from the ident, optionally links it to profile
     *
     * @version 1.0
     * @since   0.0.0
     * @author  Dan Aldridge
     *
     * @param   mixed $ident    Either a Username, or a UserID
     * @param   const $mode     0 | LINK                => Username colored and linked to profile.
     *                          1 | NOLINK              => Username colored but not linked.
     *                          3 | RETURN_USERNAME     => Return the raw username.
     *                          4 | RETURN_UID          => Return the raw user id.
     *
     * @return  string
     */
    public function makeUsername( $ident, $mode=null ){
        $objSQL = coreObj::getDBO();

        // do a check to see if we already have this beast in the array
        if( isset($this->ident[$mode][$ident]) ){
            return $this->ident[$mode][$ident];
        }

        // check to see if the ident is guest-worthy
        if( $ident !== GUEST ){

            $where = is_numeric($ident) ? 'u.id = '.$ident : 'u.username = "'.$ident.'"';

            // see if we can load the user
            if( !isset($this->cacheUsers[$ident]) ){
                $uquery = $objSQL->queryBuilder()
                    ->select(array( 'u.id', 'u.username', 'u.banned', 'g.name', 'g.description', 'g.color' ))
                    ->from(array( 'u' => '#__users' ))
                    ->where( $where )

                    ->leftJoin(array( 'g' => '#__groups'))
                        ->on('g.id', '=', 'u.primary_group')

                    ->limit(1);

                $uquery = $objSQL->fetchLine( $uquery->build() );
            }

            // if we haven't got the user loaded, then we will try and get the bare details
            if( !isset($uquery['username']) ){
                $uquery = $objSQL->queryBuilder()
                    ->select(array( 'u.id', 'u.username', 'u.banned' ))
                    ->from(array( 'u' => '#__users' ))
                    ->where( $where )
                    ->limit(1);

                $uquery = $objSQL->fetchLine( $uquery->build() );
            }

            // we didn't get anything we can use, so we will just stop here
            if( $uquery === false ){
                $this->ident[$mode][$ident] = ( $mode == RETURN_USERNAME ? $ident : 'Guest' );
            }

            $this->cacheUsers[$ident]['username'] = $uquery['username'];

            // if we already have the color sorted, we don't need to do much
            if( isset($uquery['color'])){
                $this->cacheUsers[$ident]['group'] = array(
                    'name'        => $uquery['name'],
                    'description' => $uquery['description'],
                    'color'       => $uquery['color'],
                );

            }else{
                $where = is_numeric($ident) ? 'u.id = '.$ident : 'u.username = "'.$ident.'" AND ug.uid = u.id';

                $query = $objSQL->queryBuilder()
                    ->select(array( 'g.*' ))
                    ->from(array( 'g' => '#__groups' ))
                    ->where( $where )
                        ->andWhere('ug.pending', '=', '0')

                    ->leftJoin(array( 'ug' => '#__groups_subs' ))
                        ->on('ug.group_id', '=', 'g.id')

                    ->leftJoin(array( 'u' => '#__users' ))
                        ->on('ug.user_id', '=', 'u.id')

                    ->orderBy('g.`order`', 'ASC');

                $query = $objSQL->fetchAll( $query->build() );

                $current = 100000000000;
                if( is_array($query) && count($query) ){
                    foreach($query as $row){
                        // If our new group in the list is a higher order number, it's color takes precedence
                        if( $row['order'] < $current ){
                            $current = $row['order'];
                            $this->cacheUsers[$ident]['group'] = array(
                                'name'        => $uquery['name'],
                                'description' => $uquery['description'],
                                'color'       => $uquery['color'],
                            );

                        }
                    }
                }

                if( !isset($this->cacheUsers[$ident]['group']) && count($this->groups) ){
                    foreach($this->groups as $group){
                        if( (int)$group['id'] === (int)$this->config('site', 'user_group') ){
                            $userGroup = array(
                                'name'        => $group['name'],
                                'description' => $group['description'],
                                'color'       => $group['color'],
                            );
                        }

                        $this->cacheUsers[$ident]['group'] = $userGroup;
                    }
                }
            }


            // setup the output for this method
            $user = $this->cacheUsers[$ident]['username'];
            $group = $this->cacheUsers[$ident]['group'];

            $username   = '<font title="%s" class="username" style="color: %s;">%s</font>';
            $link       = '<a href="/'.root().'profile/view/%s" rel="nofollow">%s</a>';

            $banned     = sprintf($username, $group['description'], $group['color'].'; text-decoration: line-through', $user);
            $user_link  = sprintf($link, $user, sprintf($username, $group['description'], $group['color'], $user));
            $user_nlink  = sprintf($username, $group['description'], $group['color'], $user);

            switch($mode){
                default:
                case LINK:
                    $this->ident[$mode][$ident] = $user_link;
                break;

                case NOLINK:
                    $this->ident[$mode][$ident] = $user_nlink;
                break;

                case RETURN_USERNAME:
                case RAW:
                    $this->ident[$mode][$ident] = $user;
                break;

                case RETURN_UID:
                    $this->ident[$mode][$ident] = $ident;
                break;

            }

        }else{
            $this->ident[$mode][$ident] = ( $mode == RETURN_USERNAME ? $ident : 'Guest' );
        }

        $this->ident[$mode][ $uquery[( is_numeric($ident) ? 'username' : 'id' )] ] = $this->ident[$mode][$ident];
        $this->cacheUsers[$mode][ $uquery[( is_numeric($ident) ? 'username' : 'id' )] ] = $this->cacheUsers[$ident];
        return $this->ident[$mode][$ident];
    }

    /**
     //
     // -- Todo: FINISH FUNCTION
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
     * Validates a Username to ensure it's the correct char set and to ensure it doesn't already exist
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string   $username
     * @param   bool     $exists
     *
     * @return  bool
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
        if( $exists === true && $this->get('username', $username) ){
            trigger_error('Username already exists. Please make sure your username is unique.');
            return false;
        }

        return true;
    }


    /**
     * Receives specified ajax settings
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
    public function toggle( $uid, $var, $state = null ){
        $objSQL = coreObj::getDBO();

        $userColumnData      = $objSQL->fetchColumnData( '#__users', 'Field' );
        $userExtraColumnData = $objSQL->fetchColumnData( '#__users_extras', 'Field' );

        // if state hasnt been set, then lets toggle its value
        if( $state === null){
            $state = sprintf('IF(%s=1, 0, 1)', $var);

        // if we want to toggle it to a specific value then we need to set it
        } else {
            $state = ( (bool)$state === true ? '1' : '0');
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
        $keys                = array_keys( $userInfo );


        $userData = $userExtraData = array();

        if( is_empty( $keys ) ){
            return false;
        }

        // Loop through the settings keys
        foreach( $keys as $key ){

            // Check if the keys belong to users_extras table or users_extras table
            if( in_array( $key, $userColumnData ) ){
                $userData[$key] = $userInfo[$key];
            }

            if( in_array( $key, $userExtraColumnData ) ){
                $userExtraData[$key] = $userInfo[$key];
            }
        }

        // Check if the userData is empty
        // If it isn't then update the table
        if( !is_empty( $userData ) ){

            // Generate some extra vars
            $userData['password'] = $this->mkPassword( $userData['password'] );
            $userData['usercode'] = randCode();

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
        $message   = $this->config('login', 'user_registration_email');

        /**
        //
        // -- Todo
        // --- Finish this to grab and parse the correct email stuff
        //
        */
        _mailer( $userData['email'], $siteEmail, sprintf('Registration Details From %s', $siteName ), $message  );

        return true;
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
        $objPass = new PasswordHash( 8, true );

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
        $uid = $this->get('id', $username );

        // if the username doesn't exist, return false;
        if( $uid === 0 ) {
            return false;
        }

        // Grab the phpass library
        $objPass = new PasswordHash( 8, true );

        // Fetch the hashed password from the database
        $hash = $this->get( 'password', $uid );

        if( $objPass->CheckPassword( $password, $hash ) ) {
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
                if( !self::$IS_ONLINE ){
                    return true;
                }
            break;

            case USER:
                if( self::$IS_ONLINE ){
                    return true;
                }
            break;

            case ADMIN:
                if( $userlevel == ADMIN ){
                    //if(LOCALHOST || doArgs('adminAuth', false, $_SESSION['acp'])){
                        return true;
                    //}
                }
            break;

            // no idea what they tried to check for, so we'll return something unexpected too
            default: return -1; break;
        }

        // apparently the checks didn't return true, so we'll go for false
        return false;
    }

}

?>