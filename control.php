<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
use CSCMS\Core\Classes as CoreClasses;
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
$GET = $_GET;
require_once 'core/core.php';

$objRoute  = CoreClasses\coreObj::getRoute();
$objPage   = CoreClasses\coreObj::getPage();
$objTPL    = CoreClasses\coreObj::getTPL();
$objUser   = CoreClasses\coreObj::getUser();

$objRoute->modifyGET($GET);
$mode = doArgs('__mode', false, $GET);

// regardless of control panel the user needs to be online.
if ( !CoreClasses\User::$IS_ONLINE || !in_array($mode, array('admin', 'user')) ) {

    // Need to sort out login
    $objRoute->throwHTTP(404);
    $objPage->redirect( $objRoute->generateUrl('core_loginForm'), 1);
    exit;
}


$objPage->addBreadcrumbs(array(
    array('url' => '/'. root() . $mode .'/', 'name' => ucwords($mode).' Control Panel' )
));
if( $mode == 'admin' ){
    $objCPanel = CoreClasses\coreObj::getAdminCP('', $GET);
    if ( !CoreClasses\User::$IS_ADMIN ) {

        // Need to sort out login
        $objRoute->throwHTTP(404);
        $objPage->redirect( $objRoute->generateUrl('core_loginForm'), 1 );
        exit;
    }

    $objPage->setTheme('perfectum-mootools');
}else{
    $objCPanel = CoreClasses\coreObj::getUserCP('', $GET);

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