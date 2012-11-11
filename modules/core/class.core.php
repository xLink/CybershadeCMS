<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class core extends Module{

    public function __construct(){
        $objPage = coreobj::getPage();
        $objPage->setMenu('core');

    }

    public function viewIndex(){
        $this->login_form();
    }

    public function login_form(){
        $objTPL     = coreObj::getTPL();
        $objForm    = coreObj::getForm();
        $objSession = coreObj::getSession();

        $this->setView('login_form/default.tpl');

        $form = array(
            'FORM_START'    => $objForm->start('login', array(
                                    'method' => 'POST',
                                    'action' => '/'.root().'login?'
                                )),
            'FORM_END'      => $objForm->finish(),
            'HIDDEN'        => $objForm->inputbox('hash', 'hidden', $objSession->getFormToken(true)),

            'L_USERNAME'    => langVar('L_USERNAME'),
            'F_USERNAME'    => $objForm->inputbox('username', 'text', '', array(
                                    'class'    => 'icon username',
                                    'required' => true
                                )),

            'L_PASSWORD'    => langVar('L_PASSWORD'),
            'F_PASSWORD'    => $objForm->inputbox('password', 'password', '', array(
                                    'class'    => 'icon password',
                                    'required' => true
                                )),

            'L_REMME'       => langVar('L_REMME'),
            'F_REMME'       => $objForm->select('remember', array(
                                    '0' => 'No Thanks',
                                    '1' => 'Forever'
                                ), array(
                                    'selected' => 0
                                )),

            'SUBMIT'        => $objForm->button('submit', 'Login', array('class'=>'btn btn-success')),
        );

        $objTPL->assign_block_vars('login', $form);

        if( isset($this->errors) && count($this->errors) ){
            foreach($this->errors as $error){
                $objTPL->assign_block_vars('login.errors', array(
                    'ERROR' => $error
                ));
            }
        }

    }

    public function login_process(){
        $objSession = coreObj::getSession();
        $objUser    = coreObj::getUser();
        $objLogin   = coreObj::getLogin();
        $errors = array();

        if( !$objSession->checkToken('hash') ){
            $errors[] = 'There was an issue with submitting the form, please try again.';
        }

        /*
        if( !$objUser->attemptsCheck() ){
            $errors[] = 'You have attempts to login to this account have been logged & blocked. Please wait 15 mins before trying again.';
        }
        */

        if( !$objUser->verifyUserCredentials( $_POST['username'], $_POST['password'] ) ){
            $errors[] = 'User Credentials are incorrect';
        }


        if( !count($errors) ){
            $objSessions->doLogin();
        }else{
            $this->errors = $errors;
            $this->login_form();
        }

        echo dump($errors, 'Login Errors :D');
    }
}

?>