<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();

$objPage->setTitle('Test');



$objPage->buildPage();
$objPage->showHeader();

$pass = new phpass(8,true);
echo $pass->HashPassword('test');

$objPage->showFooter();

?>