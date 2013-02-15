<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
require_once 'core/core.php';

$objRoute = Core_Classes_coreObj::getRoute();
$objPage  = Core_Classes_coreObj::getPage();
$objTPL   = Core_Classes_coreObj::getTPL();

$objPage->setTheme();

$objPage->setTitle('Test');

$objModule = $objRoute->processURL($_SERVER['QUERY_STRING']);

$objPage->buildPage();
$objPage->showHeader();

if ( $objModule !== false ) {
    $objModule->output();
}

$objPage->showFooter();
?>