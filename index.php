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
$theme_test = file_get_contents('theme_test.html');
$objTPL->assign_var('THEME_TESTER', $theme_test);

$objPage->showHeader();

$objPage->showFooter();
?>