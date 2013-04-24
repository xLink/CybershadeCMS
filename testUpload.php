<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
require_once 'core/core.php';

$objPage  = Core_Classes_coreObj::getPage();
$objForm = Core_Classes_coreObj::getForm();


$objPage->setTheme();
$objPage->tplGlobals();
$objPage->showHeader();

$form = $objForm->outputForm(
    array(
        'FORM_START'  => $objForm->start('upload_test', array(
            'method'=>'POST',  
            'class'=>'form-horizontal',
            'upload'    => true,
        )),
        'FORM_END'    => $objForm->finish(),
        'FORM_TITLE'  => 'Upload Test',
        'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
        'FORM_RESET'  => $objForm->button('reset', 'Reset'),
    ),
    array(
        'field' => array(
            'Required Info'            => '_header_',
                'L_USERNAME1'    => $objForm->inputBox('upload[]', 'file'),
                'L_USERNAME2'    => $objForm->inputBox('upload[]', 'file'),
                'L_USERNAME3'    => $objForm->inputBox('upload[]', 'file'),
                'L_USERNAME4'    => $objForm->inputBox('upload[]', 'file'),
        ),
        'errors' => $_SESSION['errors']['ucp'],
    )
);

echo $form;

$objUpload = Core_Classes_coreObj::getUpload();
$objUpload->setInputName('upload');
$objUpload->setDirectory( cmsROOT. 'themes/smartmove/assets/img/properties', true );

if( HTTP_POST ){
    debugLog($objUpload->doUpload(array('jpg', 'png', 'gif'), 5000000));
}


$objPage->showFooter();


?>