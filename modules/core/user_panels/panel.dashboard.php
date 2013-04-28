<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Core ACP Panel
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Daniel Noel-Davies
 */
class User_Modules_core_dashboard extends Admin_Modules_core{

    public function dashboard(){
        $objTPL  = Core_Classes_coreObj::getTPL();
        $objUser = Core_Classes_coreObj::getUser();
        $objPage = Core_Classes_coreObj::getPage();
        $objForm = Core_Classes_coreObj::getForm();

        $objPage->addJSFile(array(
            'src' => '/'.root().'modules/core/assets/javascript/admin/menus/custom.js',
        ), 'footer');

        $uid = $objUser->grab('id');
        $userData = $objUser->get( '*', $uid );

        $editUsername = $this->config('site', 'change_username');
        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));
        $userID = $_SESSION['site']['user_edit']['id'] = $userData['id'];

        $form = $objForm->outputForm(
            array(
                'FORM_START'  => $objForm->start('edit_user', array('method'=>'POST', 'action'=>'/'.root().'user/core/dashboard/save/', 'class'=>'form-horizontal')),
                'FORM_END'    => $objForm->finish(),
                'FORM_TOKEN'  => $formToken . $objForm->inputbox('id', 'hidden', $userID),
                'FORM_INFO'   => 'heh we can throw some info about the form in here, that\'ll do',


                'FORM_TITLE'  => 'User Panel',
                'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
                'FORM_RESET'  => $objForm->button('reset', 'Reset'),
            ),
            array(
                'field' => array(
                    'Required Info'            => '_header_',
                      langVar('L_USERNAME')      => $objForm->inputbox('username', 'text', $userData['username'], array('disabled' => !$editUsername)),
                      langVar('L_EMAIL')         => $objForm->inputbox('email', 'text', $userData['email']),

                      langVar('F_NEW_PASS_CONF') => '_header_',
                      langVar('L_CHANGE_PWDS')   => $objForm->checkbox('pass_conf', '1', false),
                      langVar('L_OLD_PASSWD')    => $objForm->inputbox('old_pass', 'password', ''),
                      langVar('L_NEW_PASSWD')    => $objForm->inputbox('new_pass', 'password', ''),
                      langVar('L_CONF_PASSWD')   => $objForm->inputbox('new_conf_pass', 'password', ''),

                ),
                'desc' => array(
                ),
                'errors' => $_SESSION['errors']['ucp'],
            )
        );

        echo $form;
    }

    public function save(){
        $objPage = Core_Classes_coreObj::getPage();

        if( !HTTP_POST || !Core_Classes_coreObj::getSession()->checkToken('form_token') ){
            $_SESSION['errors']['ucp'][] = 'Please use the form to submit the data.';
            $objPage->redirect( str_replace('save/', '', $this->config('global', 'fullPath')) ); exit;
        }

        if( doArgs('id', 0, $_POST) != $_SESSION['site']['user_edit']['id'] ){
            $_SESSION['errors']['ucp'][] = 'Session Check Failed, Please try again.';
            $objPage->redirect( str_replace('save/', '', $this->config('global', 'fullPath')) ); exit;
        }

        $update = array();

        // check the username if editable & change it
        if( $this->config('site', 'change_username', false) === true ){
            if( $objUser->validateUsername($_POST['username']) ){
                if( $objUser->validateUsername($_POST['username'], true) ){
                    $update['username'] = $_POST['username'];
                }else{
                    $_SESSION['errors']['ucp']['username'] = 'The username already exists in the database. Please pick another.';
                }
            }else{
                $_SESSION['errors']['ucp']['username'] = 'The username you chose contained incorrect characters. Please pick another.';
            }
        }


        $objPage->redirect( str_replace('save/', '', $this->config('global', 'fullPath')), 0, 3 );
        hmsgDie('OK', langVar('PROFILE_UPDATE_SUCCESS'));
    }

}


?>