<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
include_once('core/core.php');

$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();

$objPage->setTheme();

$objPage->setTitle('Test');

$objModule = $objRoute->processURL( $_SERVER['QUERY_STRING'] );

$objPage->buildPage();
$objPage->showHeader();

    $objModule->output();

$objPage->showFooter();
?>