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
        $objLogin   = Core_Classes_coreObj::getLogin();
        $objRoute   = Core_Classes_coreObj::getRoute();
        $objTPL     = $this->setView('module/register_form/default.tpl');
        if( Core_Classes_User::$IS_ONLINE ){
            // $objPage->redirect( $objRoute->generateUrl('core_viewIndex') );
        }

        $disclaimer = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                        cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                        proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $form = array(
            'FORM_START'    => $objForm->start('register', array(
                                    'method' => 'POST',
                                    'action' => $objRoute->generateUrl('core_registerForm'),
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

            'L_PASSWORD_CONFIRM'    => langVar('L_PASSWORD_CONFIRM'),
            'F_PASSWORD_CONFIRM'    => $objForm->inputbox('password_confirm', 'password', '', array(
                                        'class'    => 'icon password',
                                        'required' => true
                                    )),
            'L_EMAIL_ADDRESS'    => langVar('L_EMAIL_ADDRESS'),
            'F_EMAIL_ADDRESS'    => $objForm->inputbox('email', 'text', '', array(
                                        'class'    => 'icon email',
                                        'required' => true
                                    )),
            // 'L_REFERER'    => langVar('L_REFERER'),
            // 'F_REFERER'    => $objForm->inputbox('referer', 'text', '', array(
            //                             'class'    => 'icon email',
            //                             'required' => true
            //                 )),
            'L_EMAIL_ADDRESS_CONFIRM'    => langVar('L_EMAIL_ADDRESS_CONFIRM'),
            'F_EMAIL_ADDRESS_CONFIRM'    => $objForm->inputbox('email_confirm', 'text', '', array(
                                            'class'    => 'icon email',
                                            'required' => true,
                                        )),
            'L_RECEIVE_EMAILS_ADMINS'    => langVar('L_RECEIVE_EMAILS_ADMINS'),
            'F_RECEIVE_EMAILS_ADMINS'    => $objForm->inputbox('admin_emails', 'checkbox', '', array(
                                            'class'    => 'icon tick',
                                            'required' => true,
                                        )),
            'L_RECEIVE_EMAILS_USERS'    => langVar('L_RECEIVE_EMAILS_USERS'),
            'F_RECEIVE_EMAILS_USERS'    => $objForm->inputbox('user_emails', 'checkbox', '', array(
                                            'class'    => 'icon tick',
                                            'required' => true,
                                        )),

            // 'L_REMME'       => langVar('L_REMME'),
            // 'F_REMME'       => $objForm->select('remember', array(
            //                         '0' => 'No Thanks',
            //                         '1' => 'Forever'
            //                     ), array(
            //                         'selected' => 0
            //                     )),

            'SUBMIT'        => $objForm->button('submit', 'Register', array('class' => 'btn btn-success')),
        );

        $objTPL->assign_block_vars('register', $form);
    }
}
?>