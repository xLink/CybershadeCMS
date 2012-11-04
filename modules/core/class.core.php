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


    public function login(){
        $objTPL = coreObj::getTPL();
        $objForm = coreObj::getForm();

        $this->setView('default');

        $objTPL->assign_block_vars('login', array(
            'FORM_START'    => $objForm->start('login', array(
                                    'method' => 'POST',
                                    'action' => '/'.root().'login.php?action=check')
                                ),
            'FORM_END'      => $objForm->inputbox('hash', 'hidden', $hash) . $objForm->finish(),

            'L_USERNAME'    => langVar('L_USERNAME'),
            'F_USERNAME'    => $objForm->inputbox('username', 'text', $userValue, array(
                                    'class'    => 'icon username',
                                    'br'       => true,
                                    'disabled' => $acpCheck,
                                    'required' => true
                                )),

            'L_PASSWORD'    => langVar('L_PASSWORD'),
            'F_PASSWORD'    => $objForm->inputbox('password', 'password', '', array(
                                    'class'    => 'icon password',
                                    'br'       => true,
                                    'required' => true
                                )),

            'L_REMME'       => langVar('L_REMME'),
            'F_REMME'       => $objForm->select('remember', array(
                                    '0 '=> 'No Thanks',
                                    '1' => 'Forever'
                                ), array(
                                    'selected' => 0
                                )),

            'SUBMIT'        => $objForm->button('submit', 'Login', array('class'=>'btn btn-success')),
        ));

    }
}

?>