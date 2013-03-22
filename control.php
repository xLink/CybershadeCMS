<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
$GET = $_GET;
require_once 'core/core.php';

$objRoute = Core_Classes_coreObj::getRoute();
$objPage  = Core_Classes_coreObj::getPage();
$objTPL   = Core_Classes_coreObj::getTPL();
$objAdmin = Core_Classes_coreObj::getAdminCP($GET);
$objUser  = Core_Classes_coreObj::getUser();

$objRoute->modifyGET($GET);

if ( !Core_Classes_User::$IS_ONLINE || !Core_Classes_User::$IS_ADMIN ) {

	// Need to sort out login
    // $objRoute->throwHTTP(404);
    $objPage->redirect('/'.root().'login');
	exit;
}

$objPage->setTheme('perfectum-jquery', true);
$objPage->addBreadcrumbs(array(
    array('url' => '/'. root() . $objAdmin->mode .'/', 'name' => ucwords($objAdmin->mode).' Control Panel' )
));

$objPage->setTitle('Cybershade CMS Administration Panel');

// Output the dashboad
$objAdmin->dashboard();

?>