<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Login extends coreObj {

    public function __construct(){
        $objSession = coreObj::getSession();

        $this->onlineData = $objSession->getData();
        echo dump($this->onlineData);
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

                $query = $objSQL->queryBuilder()
                                ->update('#__sessions')
                                ->set(array(
                                    'login_time'     => $objTime->mod_time(time(), 0, 15),
                                    'login_attempts' => '0'
                                ))
                                ->where('userkey', '=', $_SESSION['user']['userkey'])
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
     * @author  Daniel Aldridge
     *
     * @return  bool
     */
    public function doLogin(){

        if( !HTTP_POST ){
            trigger_error('No POST action detected');
            return false;
        }

        //verify username and password are set and not empty
        $username = doArgs('username', null, $_POST);
        $password = doArgs('password', null, $_POST);
        if( is_empty($username) || is_empty($password) ){
            $this->doError('0x02', $ajax);
            return false;
        }

        //make sure the user hasnt already exceeded their login attempt quota
        if( !$this->attemptsCheck(true) ){
            $this->doError('0x03', $ajax);
        }


    }




    /**
     * Turns error codes in to human readable errors
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Jesus
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

        $_SESSION['login']['error'] = $L_ERROR;
        $_SESSION['login']['class'] = (in_array($errCode, $good) ? 'boxgreen' : 'boxred');

        if($ajax){
            die($L_ERROR);
        }else{
            $objPage->redirect('/'.root().'login', 0);
        }
    }
}
?>