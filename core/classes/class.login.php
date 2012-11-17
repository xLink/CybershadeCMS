<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Login extends coreObj {

    public $errors = array();

    public function __construct(){
        $objSession = coreObj::getSession();

        $this->onlineData = $objSession->getData();
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

        (cmsDEBUG ? memoryUsage('Login: doLogin()') : '');

        if( !HTTP_POST ){
            trigger_error('No POST action detected');
            return false;
        }

        $objUser = coreObj::getUser();
        $objPlugins = coreObj::getPlugins();
        $objSession = coreObj::getSession();

        if( !$objSession->checkToken('hash') ){
            $this->addError(1);
            return false;
        }

        // verify username and password are set and not empty
        $username = doArgs('username', null, $_POST);
        $password = doArgs('password', null, $_POST);

        (cmsDEBUG ? memoryUsage('Login: checking user & passy !empty()') : '');
        if( is_empty($username) || is_empty($password) ){
            $this->addError(2);
            return false;
        }

        // make sure the user hasnt already exceeded their login attempt quota
        (cmsDEBUG ? memoryUsage('Login: making sure they havent gone over their login attempts') : '');
        if( $this->attemptsCheck(true) === false ){
            $this->addError(3);
            return false;
        }

        (cmsDEBUG ? memoryUsage('Login: making sure the user actually exists') : '');
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
        (cmsDEBUG ? memoryUsage('Login: whitelist check') : '');
            if( $this->whiteListCheck() === false ){
                $this->addError(4);
            }

        (cmsDEBUG ? memoryUsage('Login: active check') : '');
            if( $this->activeCheck() === false ){
                $this->addError(5);
            }

        (cmsDEBUG ? memoryUsage('Login: checking if the user is banned') : '');
            if( $this->banCheck() === false ){
                $this->addError(6);
            }

        }

        // update their quota
        (cmsDEBUG ? memoryUsage('Login: updating the attempts ') : '');
        if( $this->attemptsCheck() === false ){
            $this->addError(3);
            return false;
        }

        // make sure the password is valid
        (cmsDEBUG ? memoryUsage('Login: validate the user details') : '');
        if( $objUser->verifyUserCredentials( $username, $password ) === false ){
            $this->addError(7);
            return false;
        }

        $uniqueKey = substr(md5($this->userData['id'].time()), 0, 5);

        // Add Hooks for Login Data
        $this->userData['password_plaintext'] = $this->postData['password'];

        (cmsDEBUG ? memoryUsage('Login: hooking that shiz yo') : '');
        $objPlugins->hook( 'CMS_LOGIN_SUCCESS', $this->userData );

        $objSQL = coreObj::getDBO();
        $objTime = coreObj::getTime();

        $query = $objSQL->queryBuilder()
                        ->update( '#__sessions' )
                        ->set(array(
                            'uid' => $objUser->grab('id'),
                        ))
                        ->where('admin',            '=', (User::$IS_ADMIN ? '1' : '0'))
                            ->andWhere('sid',       '=', md5( session_id() ) )
                            ->andWhere('hostname',  '=', User::getIP() )
                        ->build();

        $results = $objSQL->query( $query );

        (cmsDEBUG ? memoryUsage('Login: Setting the session') : '');
        $user = $this->userData;
        $user['last_active'] = time();

        $_SESSION['user'] = (is_array($_SESSION['user']) && !is_empty($_SESSION['user']) ? array_merge($_SESSION['user'], $user) : $user);

        //make sure we want em to be able to auto login first
        if($this->config('login', 'remember_me')){
            if(doArgs('remember', false, $_POST) === '1'){
                $objUser->update( $this->userData['id'], array('autologin' => '1') );

                $cookieArray = array(
                    'uData'     => $uniqueKey,
                    'uIP'       => User::getIP(),
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
     * Logs the user out
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   string $check    The user code to verify
     */
    public function logout($check){
        $objSQL = coreObj::getDBO();
        $objUser = coreObj::getUser();
        $objTime = coreObj::getTime();
        $objPage = coreObj::getPage();

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
        $objUser = coreObj::getUser();
        $objSQL  = coreObj::getDBO();

        if( !is_empty($this->userData) ){
            $objUser->update( $this->userData['id'], array('login_attempts' => '(login_attempts + 1)') );
        }

        $query = $objSQL->queryBuilder()
                        ->update('#__sessions')
                        ->set(array(
                            'login_attempts' => '(login_attempts + 1)'
                        ))
                        ->where('sid', '=', $objUser->grab('userkey'))
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

                $objSQL  = coreObj::getDBO();
                $objTime = coreObj::getTime();
                $objUser = coreObj::getUser();

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
                (cmsDEBUG ? memoryUsage('Login: deactivating the '.$this->userData['username'].'\'s account') : '');
                // coreObj::getUser()->toggle( $this->userData['id'], 'active', false );
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

        $ip = User::getIP();
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