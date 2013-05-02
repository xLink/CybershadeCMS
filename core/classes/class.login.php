<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Core_Classes_Login extends Core_Classes_coreObj {

    public $errors = array();

    public function __construct(){
        $this->onlineData = Core_Classes_coreObj::getSession()->getData();
    }

    /**
     * Makes sure all information is valid and logs the user in if needed
     *
     * @version 2.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function process(){

        if( !HTTP_POST ){
            trigger_error('No POST action detected');
            return false;
        }

        $objUser    = Core_Classes_coreObj::getUser();
        $objPlugins = Core_Classes_coreObj::getPlugins();
        $objSession = Core_Classes_coreObj::getSession();

        if( !$objSession->checkToken('hash') ){
            $this->addError(1);
            return false;
        }

        // verify username and password are set and not empty
        $username = doArgs('username', null, $_POST);
        $password = doArgs('password', null, $_POST);

        if( is_empty($username) || is_empty($password) ){
            $this->addError(2);
            return false;
        }

        // make sure the user hasnt already exceeded their login attempt quota
        if( $this->attemptsCheck(true) === false ){
            $this->addError(3);
            return false;
        }

        $this->userData = $objUser->get( '*', $username );
            if( !$this->userData ){
                $this->addError(2);
                return false;
            }

        $this->postData = array(
            'username' => $username,
            'password' => $password,
        );

        //no need to run these if we are in acp mode
        if($acpCheck !== true){
            if( $this->whiteListCheck() === false ){
                $this->addError(4);
            }

            if( $this->activeCheck() === false ){
                $this->addError(5);
            }

            if( $this->banCheck() === false ){
                $this->addError(6);
            }

        }

        // update their quota
        if( $this->attemptsCheck() === false ){
            $this->addError(3);
            return false;
        }

        // make sure the password is valid
        if( $objUser->verifyUserCredentials( $username, $password ) === false ){
            $this->addError(7);
            return false;
        }

        $uniqueKey = substr(md5($this->userData['id'].time()), 0, 5);

        // Add Hooks for Login Data
        $this->userData['password_plaintext'] = $this->postData['password'];

        $objPlugins->hook( 'CMS_LOGIN_SUCCESS', $this->userData );
        unset( $this->userData['password_plaintext'] );

        $objSQL = Core_Classes_coreObj::getDBO();
        $objTime = Core_Classes_coreObj::getTime();

        $query = $objSQL->queryBuilder()
            ->update( '#__sessions' )
            ->set(array(
                'uid' => $objUser->grab('id'),
            ))
            ->where('admin',            '=', (Core_Classes_User::$IS_ADMIN ? '1' : '0'))
                ->andWhere('sid',       '=', md5( session_id() ) )
                ->andWhere('hostname',  '=', Core_Classes_User::getIP() )
            ->build();

        $results = $objSQL->query( $query );

        $user = $this->userData;
        $user['last_active'] = time();

        $_SESSION['user'] = (is_array($_SESSION['user']) && !is_empty($_SESSION['user']) ? array_merge($_SESSION['user'], $user) : $user);

        //make sure we want em to be able to auto login first
        if( $this->config('login', 'remember_me', 'false') ){
            if( doArgs('remember', false, $_POST) === '1' ){
                $objUser->update( $this->userData['id'], array('autologin' => '1') );

                $cookieArray = array(
                    'uData'     => $uniqueKey,
                    'uIP'       => Core_Classes_User::getIP(),
                    'uAgent'    => md5($_SERVER['HTTP_USER_AGENT'].$this->config('db', 'ckeauth'))
                );

                set_cookie('login', serialize($cookieArray), $objTime->mod_time(time(), 0, 0, 24*365*10));
                $cookieArray['uData'] .= ':'.$this->userData['id']; //add the uid into the db

                $query = $objSQL->queryBuilder()
                    ->insertInto('#__userkeys')
                    ->set( $cookieArray )
                    ->build();

                $results = $objSQL->query( $query );

                unset($cookieArray);
            }
        }

        return true;
    }

    /**
     * Tests the remember me cookie for valid details
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @todo Test this func, new port from old sys :P
     */
    public function rememberMe() {

        // site setting needs to be enabled for one
        if( $this->config('login', 'remember_me', 'false') ){
            return false;
        }

        // make sure we have the cookie to begin with
        if( is_empty(doArgs('login', null, $_COOKIE)) ){
            return false;
        }

        // should be non-empty
        $cookie = unserialize($_COOKIE['login']);
            if( is_empty($cookie) ){
                return false;
            }

        // check for the expected keys in the array
        $values = array('uData', 'uIP', 'uAgent');
        foreach($values as $v){
            if( !isset($cookie[$v]) && !is_empty($cookie[$v]) ){
                return false;
            }
        }

        // uData should be 5 chars in length
        if( strlen($cookie['uData']) != 5 ){
            return false;
        }

        // IP lock active, does the IP match what we have on file?
        if( $this->config('login', 'ip_lock', false) && $cookie['uIP'] !== Core_Classes_User::getIP() ){
            return false;
        }

        // make sure the useragent matches too
        if( $cookie['uAgent'] != md5($_SERVER['HTTP_USER_AGENT'].$this->config('db', 'ckeauth')) ){
            return false;
        }

        // query for the userkey
        $objSQL = Core_Classes_coreObj::getDBO();

        $query = $objSQL->queryBuilder()
            ->select('uData')
            ->from('#__userkeys')
            ->where(sprintf( 'uData LIKE "%s"', '%'.secureMe($cookie['uData'], 'sql').'%' ))
                ->andWhere('uAgent', '=', $objSQL->quote(secureMe($cookie['uAgent'], 'sql')) );

        if( $this->config('login', 'ip_lock', false) ){
            $query = $query->andWhere('uIP', '=', $objSQL->quote(secureMe($cookie['uIP'], 'sql')) );
        }

        $query = $query->limit(1);

        // check to see if we have anything
        $query = $objSQL->fetchRow( $query->build() );
            if( $query === fales ){
                return false;
            }

        // untangle the ID & check for it
        $query['uData'] = explode(':', $query['uData']);
            if( !isset($query['uData'][1]) || is_empty($query['uData'][1]) ){
                return false;
            }

        // grab the user data if we can
        $this->userData = $objUser->get( '*', $query['uData'][1] );
            if( !is_array($this->userData) || is_empty($query['uData'][1]) ){
                return false;
            }

        // now run some checks make sure they are able to login etc
        if( !doArgs('autologin', false, $this->userData) ){
            return false;
        }

        if( !$this->activeCheck() ){
            return false;
        }

        if( !$this->banCheck() ){
            return false;
        }

        if( !$this->whitelistCheck() ){
            return false;
        }

        // everything seems fine, gogogo!
        $objSessions = Core_Classes_coreObj::getSession();
        $objSessions->setSessions( $this->userData['uid'], true );
        $objSessions->newSession();

        return true;
    }


    /**
     * Logs the user out
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   string $check    The user code to verify
     */
    public function logout($check){
        $objSQL  = Core_Classes_coreObj::getDBO();
        $objUser = Core_Classes_coreObj::getUser();
        $objTime = Core_Classes_coreObj::getTime();
        $objPage = Core_Classes_coreObj::getPage();

        if( !is_empty($check) && $check == $objUser->grab('usercode') ){

            $objUser->update($objUser->grab('id'), array('autologin'=>'0'));
            $objSQL->deleteRow('online', array('userkey = "%s"', $_SESSION['user']['userkey']));
            unset($_SESSION['user']);

            if(isset($_COOKIE['login'])){
                setCookie('login', '', $objTime->mod_time(time(), 0, 0, ((24*365*10)*1000)*1000, 'MINUS'));
                unset($_COOKIE['login']);
            }

            session_destroy();
            if(isset($_COOKIE[session_name()])){
                setCookie(session_name(), '', time()-42000);
            }

            $objPage->redirect(doArgs('HTTP_REFERER', '/'.root(), $_SERVER), 0);
        }else{
            $objPage->redirect('/'.root(), 0);
            msgDie('FAIL', 'You\'ve Unsuccessfully attempted to logout.<br />Please use the correct procedures.');
        }
    }

    public function updateLoginAttempts(){
        $objUser = Core_Classes_coreObj::getUser();
        $objSQL  = Core_Classes_coreObj::getDBO();

        if( !is_empty($this->userData) ){
            $objUser->update( $this->userData['id'], array('login_attempts' => '(login_attempts + 1)') );
        }

        $query = $objSQL->queryBuilder()
            ->update('#__sessions')
            ->set(array(
                'login_attempts' => '(login_attempts + 1)'
            ))
            ->where('sid', '=', md5( session_id() ))
            ->build();
        $objSQL->query( $query );
    }

    /**
     * Checks whether the user has exceeded the login quota
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   bool    $dontUpdate
     *
     * @return  bool
     */
    public function attemptsCheck( $dontUpdate=false ){
        if( $this->onlineData['login_time'] >= time() ){
            return false;

        }elseif( $this->onlineData['login_attempts'] > $this->config('login', 'max_login_tries') ){

            if($this->onlineData['login_time'] == '0'){

                $objSQL  = Core_Classes_coreObj::getDBO();
                $objTime = Core_Classes_coreObj::getTime();
                $objUser = Core_Classes_coreObj::getUser();

                $query = $objSQL->queryBuilder()
                    ->update('#__sessions')
                    ->set(array(
                        'login_time'     => $objTime->mod_time(time(), 0, 15),
                        'login_attempts' => '0'
                    ))
                    ->where('sid', '=', $objUser->grab('userkey'))
                    ->build();
                $objSQL->query( $query );
            }

            return false;
        }

        if( $dontUpdate === true ){ return true; }

        if( $this->userData['login_attempts'] >= $this->config('login', 'max_login_tries') ){
            if( $this->userData['login_attempts'] === $this->config('login', 'max_login_tries') ){

                //deactivate the users account
                Core_Classes_coreObj::getUser()->toggle( $this->userData['id'], 'active', false );
            }
            return false;
        }

        return true;
    }

    /**
     * Checks the whitelist associated with an account
     *
     * @version 1.2
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @return  bool
     */
    public function whiteListCheck(){
        if( !$this->userData['whitelist'] || is_empty($this->userData['whitelisted_ips']) ){
            return true;
        }

        $ip = Core_Classes_User::getIP();
        $whitelist = json_decode($this->userData['whitelisted_ips']);
            if( !is_array($whitelist) || is_empty($whitelist) ){
                return true;
            }

        foreach( $whitelist as $range ){
            if( checkIPRange($range, $ip) ){
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the active flag of the account
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @return  bool
     */
    public function activeCheck(){
        return (bool)$this->userData['active'];
    }

    /**
     * Returns the ban flag of the account
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @return  bool
     */
    public function banCheck(){
        return !(bool)$this->userData['banned'];
    }


    /**
     * Turns error codes in to human readable errors
     *
     * @version 1.2
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   mixed     $errorCode
     */
    public function addError($errorCode){
        $acpCheck = (isset($_SESSION['acp']['doAdminCheck']) ? true : false);

        switch((int)$errorCode){
            default:
            case 0:
                $L_ERROR = '('.$errorCode.') I Can\'t seem to find the issue, Please contact a system administrator or <a href="mailto:'.
                                $this->config('site', 'admin_email') .'">Email The Site Admin</a>';
            break;

            case 1:
                $L_ERROR = 'There was a problem with the form submission. Please try again.';
                $this->updateLoginAttempts();
            break;

            case 2:
                $L_ERROR = 'Your User name or Password combination was incorrect. Please try again.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case 3:
                $L_ERROR = 'You have attempted to login too many times with incorrect credentials. Therefore you have been locked out.';
            break;

            case 4:
                $L_ERROR = 'The white list check on your account failed. We were unable to log you in.';
                $this->updateLoginAttempts();
            break;

            case 5:
                $L_ERROR = 'Your account is not activated. Please check your emails for the activation Email or Contact an Administrator to get this problem resolved.';
            break;

            case 6:
                $L_ERROR = 'Your account is banned. We were unable to log you in.';
                $this->updateLoginAttempts();
            break;

            case 7:
                $L_ERROR = 'Your User name or Password combination was incorrect. Please try again.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case 8:
                $L_ERROR = 'Your account is now active. If your encounter any problems please notify a member of staff.';
            break;

            case 9:
                $L_ERROR = 'Sorry we cannot verify your PIN at this time.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case 10:
                $L_ERROR = 'You need to set your PIN before your able to login to the admin control panel.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case 11:
                $L_ERROR = 'The PIN you provided was invalid.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;
        }
        $good = array('0x8');

        $this->errors[] = array(
            'code'  => $errorCode,
            'msg'   => $L_ERROR,
            'class' => (in_array($errorCode, $good) ? 'info' : 'error')
        );
    }
}
?>
