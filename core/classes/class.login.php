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

        if($dontUpdate){ return true; }

        if($this->userData['login_attempts'] >= $this->config('login', 'max_login_tries')){
            if($this->userData['login_attempts'] == $this->config('login', 'max_login_tries')){
                //deactivate the users account

                $objUser = coreObj::getUser();

                $objUser->toggle($this->userData['id'], 'active', false);
            }
            return false;
        }

        return true;
    }

    public function doLogin(){

    }



}
?>