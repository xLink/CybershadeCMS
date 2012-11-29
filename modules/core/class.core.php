<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Module_core extends Module{

    public function __construct(){
        $objPage = coreobj::getPage();
        $objPage->setMenu('core');

    }

    public function viewIndex(){
        $this->setView('module/viewIndex/default.tpl');
    }


/**
  //
  //-- Login Stuff
  //
**/

    public function login_form(){
        $objTPL     = coreObj::getTPL();
        $objForm    = coreObj::getForm();
        $objSession = coreObj::getSession();
        $objPage    = coreObj::getPage();
        $objLogin   = coreObj::getLogin();

        if( User::$IS_ONLINE ){
            $objPage->redirect('/'.root());
        }

        $this->setView('module/login_form/default.tpl');

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

    public function block_login( $block ){
        $objTPL     = coreObj::getTPL();
        $objForm    = coreObj::getForm();
        $objSession = coreObj::getSession();
        $objPage    = coreObj::getPage();

        if( User::$IS_ONLINE ){
            $objPage->redirect('/'.root());
        }

        $objTPL->set_filenames(array(
            'block_login' => cmsROOT . 'modules/core/views/module/login_form/block.tpl'
        ));

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
        $objTPL->reset_block_vars('login');
        $objTPL->assign_block_vars('login', $form);

        $objTPL->assign_vars(array( 'TITLE' => $block['title'] ));

        if( isset($_SESSION['login']['errors']) && count($_SESSION['login']['errors']) ){
            foreach($_SESSION['login']['errors'] as $error){
                $objTPL->assign_block_vars('login.errors', array(
                    'ERROR' => $error
                ));
            }

            unset($_SESSION['login']);
        }
        return $objTPL->get_html('block_login');
    }

    public function login_process(){
        $objUser  = coreObj::getUser();
        $objLogin = coreObj::getLogin();
        $objPage  = coreObj::getPage();

        if( $objLogin->process() !== true ){
            $this->login_form();
            return;
        }

        $objPage->redirect(doArgs('referer', '/'.root(), $_SESSION['login']), 0);
    }

    public function logout(){
        $objLogin = coreObj::getLogin();

        $objLogin->logout($_GET['check']);
    }
}
?>