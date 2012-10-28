<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objUser = coreObj::getUser();
$tests = array();

$uid = 'jez';

    $test[$uid][] = $objUser->validateUsername($uid, true);

echo dump($test[$uid][0]);
?>