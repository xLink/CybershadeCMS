<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Login extends coreObj {

    public function __construct(){
        $objSession = coreObj::getSession();

        $this->onlineData = $objSession->getData();
        echo dump($this->onlineData, 'onlineData');
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
    public function attemptsCheck($dontUpdate=false){
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
                                ->where('userkey', '=', $objUser->grab('userkey'))
                                ->build();
                $objSQL->query( $query );
            }

            return false;
        }

        if( $dontUpdate ){ return true; }

        if( $this->userData['login_attempts'] >= $this->config('login', 'max_login_tries') ){
            if( $this->userData['login_attempts'] == $this->config('login', 'max_login_tries') ){
                //deactivate the users account
                coreObj::getUser()->toggle( $this->userData['id'], 'active', false );
            }
            return false;
        }

        return true;
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
    public function doLogin(){
        (cmsDEBUG ? memoryUsage('Login: doLogin()') : '');

        if( !HTTP_POST ){
            trigger_error('No POST action detected');
            return false;
        }

        $objUser = coreObj::getUser();
        $objPlugins = coreObj::getPlugins();

        // verify username and password are set and not empty
        $username = doArgs('username', null, $_POST);
        $password = doArgs('password', null, $_POST);

        (cmsDEBUG ? memoryUsage('Login: checking user & passy !empty()') : '');
        if( is_empty($username) || is_empty($password) ){
            $this->doError('0x02', $ajax);
            return false;
        }

        // make sure the user hasnt already exceeded their login attempt quota
        (cmsDEBUG ? memoryUsage('Login: making sure they havent gone over their login attempts') : '');
        if( $this->attemptsCheck(true) === false ){
            $this->doError('0x03', $ajax);
            return false;
        }

        (cmsDEBUG ? memoryUsage('Login: making sure the user actually exists') : '');
        $this->userData = $objUser->get( '*', $username );
            if( !$this->userData ){
                $this->doError('0x02', $ajax);
                return false;
            }

        $this->postData = array(
            'username' => $username,
            'password' => $password,
        );

        //no need to run these if we are in acp mode
        //if($acpCheck !== true){
        (cmsDEBUG ? memoryUsage('Login: whitelist check') : '');
            if( $this->whiteListCheck() === false ){
                $this->doError('0x04', $ajax);
            }

        (cmsDEBUG ? memoryUsage('Login: active check') : '');
            if( $this->activeCheck() === false ){
                $this->doError('0x05', $ajax);
            }

        (cmsDEBUG ? memoryUsage('Login: checking if the user is banned') : '');
            if( $this->banCheck() === false ){
                $this->doError('0x06', $ajax);
            }

        //}

        // update their quota
        (cmsDEBUG ? memoryUsage('Login: updating the attempts ') : '');
        if( $this->attemptsCheck() === false ){
            $this->doError('0x03', $ajax);
            return false;
        }

        // make sure the password is valid
        (cmsDEBUG ? memoryUsage('Login: validate the user details') : '');
        if( $objUser->verifyUserCredentials( $username, $password ) === false ){
            $this->doError('0x07', $ajax);
            return false;
        }

        $uniqueKey = substr(md5($this->userData['id'].time()), 0, 5);

        // Add Hooks for Login Data
        $this->userData['password_plaintext'] = $this->postData['password'];
        (cmsDEBUG ? memoryUsage('Login: hooking that shiz yo') : '');
        $objPlugins->hook( 'CMS_LOGIN_SUCCESS', $this->userData );

        $objSQL = coreObj::getDBO();
        $objPage = coreObj::getPage();

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

        $_SESSION['user'] = array_merge($_SESSION['user'], $user);

        (cmsDEBUG ? memoryUsage('Login: redirecting') : '');
        //$objPage->redirect('/'.root(), 0, '5');
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

        if( !is_empty($check)
                && $check == $objUser->grab('usercode')){

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
            $objPage->redirect('/'.root(), 0, '5');
            msgDie('FAIL', 'You\'ve Unsuccessfully attempted to logout.<br />Please use the correct procedures.');
        }
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
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   mixed     $errCode
     * @param   bool     $ajax
     */
    function doError($errCode, $ajax=false){
        $acpCheck = isset($_SESSION['acp']['doAdminCheck']) ? true : false;

        switch($errCode){
            default:
                $L_ERROR = $errCode;
            break;

            case '0x0':
                $L_ERROR = '('.$errCode.') I Can\'t seem to find the issue, Please contact a system administrator or <a href="mailto:'.
                                $this->config('site', 'admin_email') .'">Email The Site Admin</a>';
            break;

            case '0x1':
                $L_ERROR = 'There was a problem with the form submittion. Please try again.';
                $this->updateLoginAttempts();
            break;

            case '0x2':
                $L_ERROR = 'Your Username or Password combination was incorrect. Please try again.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case '0x3':
                $L_ERROR = 'You have attempted to login too many times with incorrect credentials. Therefore you have been locked out.';
            break;

            case '0x4':
                $L_ERROR = 'The whitelist check on your account failed. We were unable to log you in.';
                $this->updateLoginAttempts();
            break;

            case '0x5':
                $L_ERROR = 'Your account is not activated. Please check your emails for the activation Email or Contact an Administrator to get this problem resolved.';
            break;

            case '0x6':
                $L_ERROR = 'Your account is banned. We were unable to log you in.';
                $this->updateLoginAttempts();
            break;

            case '0x7':
                $L_ERROR = 'Your Username or Password combination was incorrect. Please try again.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case '0x8':
                $L_ERROR = 'Your account is now active. If your encounter any problems please notify a member of staff.';
            break;

            case '0x9':
                $L_ERROR = 'Sorry we cannot verify your PIN at this time.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case '0x10':
                $L_ERROR = 'You need to set your PIN before your able to login to the admin control panel.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;

            case '0x11':
                $L_ERROR = 'The PIN you provided was invalid.';
                ($acpCheck ? $this->updateACPAttempts() : $this->updateLoginAttempts());
            break;
        }

        $good = array('0x8');

        $_SESSION['login']['errors'][] = $L_ERROR;
        $_SESSION['login']['class'] = (in_array($errCode, $good) ? 'boxgreen' : 'boxred');

        /*if($ajax){
            die($L_ERROR);
        }else{
            $objPage = coreObj::getPage();
            $objPage->redirect('/'.root().'login', 0);
        }*/
    }
}
?>