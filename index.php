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
$objDebug = Core_Classes_coreObj::getDebug();

$objPage->setTheme();

$objPage->setTitle('Test');
$objPage->addBreadcrumbs(array(
    array('url' => '/'.root(), 'name' => 'Home'),
));


$objPage->tplGlobals();
$objModule = $objRoute->processURL($_SERVER['QUERY_STRING']);

$objPage->showHeader();

if ( $objModule !== false ) {
    $objModule->output();
}

$objDebug->log( 'aaaa', 'title' );
$objDebug->log( 'bbb', 'title', 'error' );
$objDebug->log( 'cccc', 'title', 'success' );

$objPage->showFooter();
?>