<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
$GET = $_GET;
require_once 'core/core.php';

$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();
$objAdmin = coreObj::getAdminCP($GET);

$objRoute->modifyGET($GET);

if ( !User::$IS_ONLINE || !User::$IS_ADMIN ) {
    $objRoute->throwHTTP(404);
}

$objPage->setTheme('perfectum', true);
$objPage->addBreadcrumbs(array(
    array('url' => '/'. root() . $objAdmin->mode .'/', 'name' => ucwords($objAdmin->mode).' Control Panel' )
));

$objPage->setTitle('Cybershade CMS Administration Panel');

$objAdmin->getNav();
$objAdmin->invokeRoute();

$objPage->buildPage();
$objPage->showHeader();

    $objAdmin->output();

$objPage->showFooter();
?>