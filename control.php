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
$objAdmin = Core_Classes_coreObj::getAdminCP('', $GET);
$objUser  = Core_Classes_coreObj::getUser();

$objRoute->modifyGET($GET);

if ( !IS_ONLINE || !IS_ADMIN ) {

	// Need to sort out login
    // $objRoute->throwHTTP(404);
    $objPage->redirect('/'.root().'login');
	exit;
}

$objPage->setTheme('perfectum-mootools', true);
$objPage->addBreadcrumbs(array(
    array('url' => '/'. root() . $objAdmin->mode .'/', 'name' => ucwords($objAdmin->mode).' Control Panel' )
));

$objPage->setTitle('Cybershade CMS Administration Panel');

// grab the nav and throw the baic tpl setups together
$objAdmin->getNav();
$objPage->tplGlobals();

// sort the route out, see what we need to do
$objAdmin->invokeRoute();

// and then output..something
$objPage->showHeader();
$objAdmin->output();
$objPage->showFooter();

?>