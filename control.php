<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
$GET = $_GET;
require_once 'core/core.php';

$objRoute  = Core_Classes_coreObj::getRoute();
$objPage   = Core_Classes_coreObj::getPage();
$objTPL    = Core_Classes_coreObj::getTPL();
$objUser   = Core_Classes_coreObj::getUser();

$objRoute->modifyGET($GET);
$mode = doArgs('__mode', false, $GET);

// regardless of control panel the user needs to be online.
if ( !Core_Classes_User::$IS_ONLINE || !in_array($mode, array('admin', 'user')) ) {

    // Need to sort out login
    $objRoute->throwHTTP(404);
    $objPage->redirect( $objRoute->generateUrl('core_loginForm'), 1);
    exit;
}


$objPage->addBreadcrumbs(array(
    array('url' => '/'. root() . $mode .'/', 'name' => ucwords($mode).' Control Panel' )
));
if( $mode == 'admin' ){
    $objCPanel = Core_Classes_coreObj::getAdminCP('', $GET);
    if ( !Core_Classes_User::$IS_ADMIN ) {

        // Need to sort out login
        $objRoute->throwHTTP(404);
        $objPage->redirect( $objRoute->generateUrl('core_loginForm'), 1 );
        exit;
    }

    $objPage->setTheme('perfectum-mootools');
}else{
    $objCPanel = Core_Classes_coreObj::getUserCP('', $GET);

    $objPage->setOptions('columns', 2);
    $objPage->setTheme();
}
$objTPL->assign_var('CP_ROOT', root().$mode.'/');

$objPage->setTitle('Control Panel');

// grab the nav and throw the basic tpl setups together
$objPage->tplGlobals();

// sort the route out, see what we need to do
$objCPanel->invokeRoute();

// and then output..something
$objPage->showHeader();
echo $objCPanel->output();
$objPage->showFooter();

?>