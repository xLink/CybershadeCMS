<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objUser = coreObj::getUser();
$tests   = array();

$uid = '1';

// User tests
$test[$uid]['validate']                = $objUser->validateUsername($uid, true);
$test[$uid]['grab']                    = $objUser->grab('id');
$test[$uid]['get']                     = $objUser->get( 'id', $uid );
$test[$uid]['getUsernameByID']         = $objUser->getUsernameByID( $uid );
$test[$uid]['getIdByUsername']         = $objUser->getIdByUsername('xLink');
$test[$uid]['ajax']                    = $objUser->getAjaxSetting('all', $uid);
$test[$uid]['IP']                      = $objUser::getIP();
$test[$uid]['update']                  = $objUser->update($uid, array('birthday' => '15/01/1991'));
$test[$uid]['setPassword']             = $objUser->setPassword( $uid, 'test' );
$test[$uid]['toggle']                  = $objUser->toggle($uid, 'show_email', true);
$test[$uid]['mkpassword']              = $objUser->mkPassword('test', 'trollolololol');
$test[$uid]['mkpassword without salt'] = $objUser->mkPassword('test');
$test[$uid]['checkPermissions']        = $objUser->checkPermissions($uid, 3);

// Lets get the results
echo dump( $test );
?>