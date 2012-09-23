<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

// $objPage->setTheme();

// $objPage->setTitle('Test');

// $objPage->buildPage();
// $theme_test = file_get_contents('theme_test.html');
// $objTPL->assign_var('THEME_TESTER', $theme_test);

$url = explode('?', $_SERVER['REQUEST_URI']);
$objRoute = coreObj::getRoute();
$objRoute->processURL( $url[0] );

// $objPage->showHeader();

// $objPage->showFooter();
?>