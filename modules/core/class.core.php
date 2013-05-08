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
    /**
     * Registration Form for the user registration process
     *
     * @author Richard Clifford
     * @version 1.0.0
     * @since 1.0.0
     *
     * @todo Add disclaimer into the database and ensure it gets output here
     *
     * @return void
     */
    public function registerUser(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objSession = Core_Classes_coreObj::getSession();
        $objPage    = Core_Classes_coreObj::getPage();
        $objRoute   = Core_Classes_coreObj::getRoute();

        if( Core_Classes_User::$IS_ONLINE ){
            // $objPage->redirect( $objRoute->generateUrl('core_viewIndex') );
        }

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

    /**
     * Registration Form processor for the User Registration Process
     *
     * @author Richard Clifford
     * @version 1.0.0
     * @since 1.0.0
     *
     * @return bool
     */
    public function registerUserProcess(){
        $objTPL = Core_Classes_coreobj::getTPL();

        $objTPL->set_filenames(array(
            'body'  => '/'.root().'modules/core/views/register_form/default.tpl'
        ));

        $objTPL->assign_block_vars('register.errors', array());

        $requiredFields = array(
            'username',
            'password',
            'password_confirm',
            'email',
            'email_confirm',
        );

        foreach( $requiredFields as $requiredKey ){
            if( !array_key_exists($requiredKey, $_POST) || is_empty( $_POST[$requiredKey] ) ){
                $objTPL->assign_block_vars('register.errors', array(
                    'CLASS' => 'warning',
                    'ERROR' => 'There seems to be something wrong with the form, one or more fields were not filled in',
                ));
                trigger_error('Missing required field, please go back and try again');

                $objTPL->parse('body', true);
                return false;
            }
            ${$requiredKey} = $_POST[$requiredKey];
        }

        $objSQL  = Core_Classes_coreobj::getDBO();
        $objUser = Core_Classes_coreobj::getUser();
        $objPage = Core_Classes_coreObj::getPage();

        $checkUserStatus = $objUser->validateUsername( $username, true );

        if( !$checkUserStatus ){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'There seems to be something wrong with the username choice, it could possibly be taken',
            ));

            trigger_error('There seems to be something wrong with the username choice, it could possibly be taken');

            // Redirect back
            $objTPL->parse('body', true);
            // $objPage->redirect($_SERVER['HTTP_REFERER'], 5, 2);
            return false;
        }

        // Check passwords match
        if( ( $password !== $password_confirm ) ){
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'Passwords don\'t match or invalid complexity',
            ));

            trigger_error('Passwords don\'t match or invalid complexity');

            // Redirect back
            $objTPL->parse('body', true);
            // $objPage->redirect($_SERVER['HTTP_REFERER'], 5, 3);
            return false;
        }

        // Check emails match and is valid email
        if( (preg_match( '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/', $email ) === false)
            || ( $email !== $email_confirm ) ){

            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'warning',
                'ERROR' => 'Email addresses did not match or they were invalid',
            ));

            trigger_error('Email addresses did not match or they were invalid');

            $objTPL->parse('body', true);
            // $objPage->redirect($_SERVER['HTTP_REFERER'], 5, 3);
            return false;
        }

        // All good, lets go
        $userRegister = $objUser->register($_POST);

        // If we successfully registered the user
        if( $userRegister ){

            // Message thanks for registering
            $objTPL->assign_block_vars('register.errors', array(
                'CLASS' => 'success',
                'ERROR' => 'Successfully registered, Redirecting you back in 5 seconds...',
            ));

            $redirectSuccess = (isset($_SESSION['userRegister']['redirect']) && (!is_empty($_SESSION['userRegister']['redirect']))
                ? $_SESSION['userRegister']['redirect']
                : '/' . root());

            /**
             //
             // -- Todo: Force Login?
             //
             */

            $objTPL->parse('body', true);
            // $objPage->redirect($redirectSuccess, 5, 3);
            return true;
        }

        $objTPL->parse('body', true);
        return false;
    }

    /**
     * Forgot Password Form
     *
     * @author Richard Clifford
     * @version 1.0.0
     * @since 1.0.0
     *
     * @return void
     */
    public function forgotPasswordForm(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objSession = Core_Classes_coreObj::getSession();
        $objPage    = Core_Classes_coreObj::getPage();
        $objRoute   = Core_Classes_coreObj::getRoute();

        $form = $objForm->outputForm(
            array(
                'FORM_START'    => $objForm->start('forgot_password', array(
                    'method' => 'POST',
                    'action' => $objRoute->generateUrl('core_forgotPasswordForm_process'),
                    'class'  => 'form-horizontal'
                )),
                'FORM_END'      => $objForm->finish(),
                'HIDDEN'        => $objForm->inputbox('hash', 'hidden', $objSession->getFormToken(true)),

                'FORM_TITLE'    => 'Forgot Password',
                'FORM_RESET'    => $objForm->button('reset', 'Reset'),
                'FORM_SUBMIT'   => $objForm->button('submit', 'Submit', array('class' => 'btn btn-success')),
            ),
            array(
                'field' => array(
                     langVar('L_USERNAME') => $objForm->inputbox('username', 'text', '', array(
                        'class'    => 'icon username',
                        'required' => true,
                    )),
                ),
                'desc'      => array(),
                'errors'    => $_SESSION['errors']['registration'],
            ),
            array(
                'header' => '<h4>%s</h4>'
            )
        );

        echo $form;
    }

    public function forgotPasswordFormProcess(){
        // Grab the username from the post vars
        $username = doArgs('username', null, $_POST);

        if( is_empty( $username ) ){
            trigger_error('No username given');
            return false;
        }

        $objSQL  = Core_Classes_coreObj::getDBO();
        $isEmail = preg_match( '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $username );
        $uid = 0;
        if( $isEmail ){
            $uid = $objSQL->queryBuilder()
                ->select('id')
                ->from('#__users')
                ->where('email', '=', $username)
                ->limit(1);

            $uid = $objSQL->fetchLine($uid->build());
            $uid = (is_array( $uid ) && !is_empty( $uid ) ? array_shift( $uid ) : 0);
        } else {
            $objUser = Core_Classes_coreObj::getUser();
            $uid = $objUser->get( 'id', $username );
        }

        if( $uid === 0 ){
            return false;
        }

        $updatePasswordFlag = $objSQL->queryBuilder()
            ->update('#__users')
            ->set(array(
                'password_update' => 1,
            ))
            ->where('id', '=', $uid)
            ->limit(1);

        $updatePasswordFlag = $objSQL->query( $updatePasswordFlag->build() );

        if($updatePasswordFlag){
            // Defaults to the passed in param
            $userInfo = array();

            // Will make this more sexified
            if( !$isEmail ){
                $userInfo = $objUser->get('*', $uid);
            } else {
                $userInfo = $objUser->get('*', $username);
            }

            if( is_empty( $userInfo ) ){
                return false;
            }

            $username = doArgs('username', '', $userInfo);
            $email    = doArgs('email', '', $userInfo);
            $subject  = 'Password Reset for ' . $username;
            $body     = $this->config('login', 'forgot_password_email');
            $replyTo = $this->config('site', 'reply_to_address');

            return _mailer($email, 'no-reply@cybershade.org', $subject, $body, array(
                'isHTML' => true,
                'bcc' => array(
                    'Richard Clifford' => 'darkmantis@cybershade.org',
                    'Dan Aldrige' => 'xlink@cybershade.org',
                ),
            ));
        }
    }
}
?>