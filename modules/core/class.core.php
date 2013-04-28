<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Modules_core extends Core_Classes_Module{

    public function __construct(){
        $objPage = Core_Classes_coreobj::getPage();
        $objPage->setMenu('core');

    }

    public function viewIndex(){
        $this->setView('bootstrap_kitchensink.tpl');


    }


/**
  //
  //-- Login Stuff
  //
**/

    public function loginForm(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objSession = Core_Classes_coreObj::getSession();
        $objPage    = Core_Classes_coreObj::getPage();
        $objLogin   = Core_Classes_coreObj::getLogin();
        $objRoute   = Core_Classes_coreObj::getRoute();
        $objTPL     = $this->setView('module/login_form/default.tpl');

        if( Core_Classes_User::$IS_ONLINE ){
            $objPage->redirect( $objRoute->generateUrl('core_viewIndex') );
        }

        if( $this->config('global', 'referer') != $this->config('site', 'siteUrl').$objRoute->generateUrl('core_loginForm') ){
            $_SESSION['login']['referer'] = $this->config('global', 'referer');
        }
        $form = array(
            'FORM_START'    => $objForm->start('login', array(
                                    'method' => 'POST',
                                    'action' => $objRoute->generateUrl('core_loginForm_process'),
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

            'SUBMIT'        => $objForm->button('submit', 'Login', array('class' => 'btn btn-success')),
        );

        $objTPL->assign_block_vars('login', $form);

        if( isset($objLogin->errors) && count($objLogin->errors) ){
            foreach($objLogin->errors as $error){
                $objTPL->assign_block_vars('login.errors', array(
                    'ERROR' => $error['msg'],
                    'CLASS' => $error['class'],
                ));
            }

            unset($objLogin->errors);
        }

    }

    public function blockLogin( $block ){
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objForm    = Core_Classes_coreObj::getForm();
        $objSession = Core_Classes_coreObj::getSession();
        $objPage    = Core_Classes_coreObj::getPage();
        $objRoute   = Core_Classes_coreObj::getRoute();

        return '<div class="progress progress-success progress-striped active" style="margin: 0;">
        <div class="bar" style="width: 45%"></div>
      </div>';

        if( Core_Classes_User::$IS_ONLINE ){
            $objPage->redirect($objRoute->generateUrl('core_viewIndex'));
        }

        $objTPL->set_filenames(array(
            'block_login' => cmsROOT . 'modules/core/views/module/login_form/block.tpl'
        ));

        $form = array(
            'FORM_START'    => $objForm->start('login', array(
                                    'method' => 'POST',
                                    'action' => $objRoute->generateUrl('core_loginForm_process'),
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
        $objTPL->reset_block_vars('login');
        $objTPL->assign_block_vars('login', $form);

        $objTPL->assign_vars(array( 'TITLE' => $block['title'] ));

        if( isset($objLogin->errors) && count($objLogin->errors) ){
            foreach($objLogin->errors as $error){
                $objTPL->assign_block_vars('login.errors', array(
                    'ERROR' => $error['msg'],
                    'CLASS' => $error['class'],
                ));
            }

            unset($objLogin->errors);
        }
        return $objTPL->get_html('block_login');
    }

    public function loginForm_process(){
        $objUser  = Core_Classes_coreObj::getUser();
        $objLogin = Core_Classes_coreObj::getLogin();
        $objPage  = Core_Classes_coreObj::getPage();
        $objRoute = Core_Classes_coreObj::getRoute();

        if( $objLogin->process() !== true ){
            $this->loginForm();
            return;
        }

        $objPage->redirect(doArgs('referer', $objRoute->generateUrl('core_viewIndex'), $_SESSION['login']), 0);
    }

    public function logout(){
        $objLogin = Core_Classes_coreObj::getLogin();

        $objLogin->logout($_GET['check']);
    }


/**
//
//-- Registration Processes
//
*/
    public function registerUser(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objSession = Core_Classes_coreObj::getSession();
        $objPage    = Core_Classes_coreObj::getPage();
        $objRoute   = Core_Classes_coreObj::getRoute();
        $objTPL     = Core_Classes_coreObj::getTPL();

        if( Core_Classes_User::$IS_ONLINE ){
            // $objPage->redirect( $objRoute->generateUrl('core_viewIndex') );
        }

        $disclaimer = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                        cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                        proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $form = $objForm->outputForm(
            array(
                'FORM_START'    => $objForm->start('register', array(
                    'method' => 'POST',
                    'action' => $objRoute->generateUrl('core_registerForm_process'),
                    'class'  => 'form-horizontal'
                )),
                'FORM_END'      => $objForm->finish(),
                'HIDDEN'        => $objForm->inputbox('hash', 'hidden', $objSession->getFormToken(true)),

                'FORM_TITLE'    => 'User Registration',
                'FORM_RESET'    => $objForm->button('reset', 'Reset'),
                'FORM_SUBMIT'   => $objForm->button('submit', 'Register', array('class' => 'btn btn-success')),
            ),
            array(
                'field' => array(

                    'User Information' => '_header_',
                        langVar('L_USERNAME') => $objForm->inputbox('username', 'text', '', array(
                            'class'    => 'icon username',
                            'required' => true
                        )),
                        langVar('L_PASSWORD') => $objForm->inputbox('password', 'password', '', array(
                            'class'    => 'icon password',
                            'required' => true
                        )),
                        langVar('L_PASSWORD_CONFIRM') => $objForm->inputbox('password_confirm', 'password', '', array(
                            'class'    => 'icon password',
                            'required' => true
                        )),

                    'Personal Information'  => '_header_',
                        langVar('L_EMAIL_ADDRESS') => $objForm->inputbox('email', 'text', '', array(
                            'class'    => 'icon email',
                            'required' => true
                        )),
                        langVar('L_EMAIL_ADDRESS_CONFIRM') => $objForm->inputbox('email_confirm', 'text', '', array(
                            'class'    => 'icon email',
                            'required' => true,
                        )),

                    'Settings' => '_header_',
                        langVar('L_RECEIVE_EMAILS_ADMINS') => $objForm->inputbox('admin_emails', 'checkbox', '', array(
                            'class'    => 'icon tick',
                            'required' => false,
                        )),
                        langVar('L_RECEIVE_EMAILS_USERS') => $objForm->inputbox('user_emails', 'checkbox', '', array(
                            'class'    => 'icon tick',
                            'required' => false,
                        )),
                ),
                'desc'      => array(

                ),
                'errors'    => $_SESSION['errors']['registration'],
            ),
            array(
                'header' => '<h4>%s</h4>'
            )
        );

        echo $form;
    }

    public function registerUserProcess(){
        $objTPL     = $this->setView('module/register_form/default.tpl');

        $requiredFields = array(
            'username',
            'password',
            'password_confirm',
            'email',
            'email_confirm',
        );

        foreach( $requiredFields as $requiredKey ){
            if( !array_key_exists($requiredKey, $_POST) || is_empty( $_POST[$requiredKey] ) ){
                trigger_error('Missing required field, please go back and try again');
                return false;
            }
            ${$requiredKey} = $_POST[$requiredKey];
        }

        $objSQL  = Core_Classes_coreobj::getDBO();
        $objUser = Core_Classes_coreobj::getUser();

        $checkUserStatus = $objUser->validateUsername( $username, true );

        if( !$checkUserStatus ){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'There seems to be something wrong with the username choice, it could possibly be taken',
            ));

            trigger_error('There seems to be something wrong with the username choice, it could possibly be taken');
            // Redirect back
            return false;
        }

        if( ( $password !== $password_confirm )  /* || ( Password does not meet requirements  )*/ ){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'Passwords don\'t match or invalid complexity',
            ));
            trigger_error('Passwords don\'t match or invalid complexity');
            return false;
        }

        if( ( $email !== $email_confirm ) /* || (  Email doesnt match Regex  ) */){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'Email addresses did not match or they were invalid',
            ));
            trigger_error('Email addresses did not match or they were invalid');
            return false;
        }

        // All good, lets go
        $userRegister = $objUser->register($_POST);

        if( $userRegister ){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'success',
                'ERROR' => 'Successfully registered, Redirecting you back now',
            ));
            // $objPage->redirect();
            // Message thanks for registering
            // Redirect to referer
            return true;
        }

        return false;
    }
}
?>