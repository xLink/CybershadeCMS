<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
use CSCMS\Core\Classes as CoreClasses;
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
require_once 'core/core.php';

$objRoute = CoreClasses\coreObj::getRoute();
$objPage  = CoreClasses\coreObj::getPage();
$objTPL   = CoreClasses\coreObj::getTPL();
$objDebug = CoreClasses\coreObj::getDebug();

$objPage->setTheme();

$objPage->setTitle('Test');
$objPage->addBreadcrumbs(array(
    array('url' => '/'.root(), 'name' => 'Home'),
));


$objPage->tplGlobals();
$objModule = $objRoute->processURL($_SERVER['QUERY_STRING']);

$objPage->showHeader();

if ( $objModule !== false ) {
    echo $objModule->output();
}

$objPage->showFooter();
?>