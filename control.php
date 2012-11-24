<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
$GET = $_GET;
include_once('core/core.php');

$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();
$objAdmin = coreObj::getAdminCP();

$objRoute->modifyGET($GET);

if( !User::$IS_ONLINE || !User::$IS_ADMIN ){
    $objRoute->throwHTTP(404);
}

$objPage->setTheme('perfectum', true);
$objPage->addBreadcrumbs(array(
    array('url' => '/'.root().$mode.'/', 'name' => ucwords($mode).' Control Panel' )
));

$objPage->setTitle('Cybershade CMS Administration Panel');

$objPage->buildPage();

$objAdmin->getNav();

$objPage->showHeader();


    $objAdmin->invokeRoute();

    $objAdmin->output();

$objPage->showFooter();
echo dump($objTPL);
?>