<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage = coreObj::getPage();
$objPage->setTheme();

$objPage->setTitle('Test');

$objPage->buildPage();
$objPage->showHeader();

$a = array('password' => 'test');
echo dump($a);

$objPage->showFooter();
?>