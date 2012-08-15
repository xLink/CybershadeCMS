<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class session extends coreObj{

    public function __construct($store='none', $options = array()){

        //kill whatever session crap PHP has going
        if(session_id()){
            session_unset();
            session_destroy();
        }

    }

    public function __destruct(){
        echo dump($a, 'DESTRUCTED!');
    }





    public function getFormToken($forceNew=false){
        $objUser = coreObj::getUser();

        return $objUser->mkHash($objUser->get('id', 0) . self::getToken());
    }

    public function getToken($forceNew=false){
        $token = $this->getvar('session', 'token');

        if(empty($token) || $forceNew){
            $token = randCode(12);
            $this->setVar('session', 'token', $token);
        }

        return $token;
    }
}

?>