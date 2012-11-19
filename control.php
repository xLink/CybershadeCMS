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

$objRoute->modifyGET($GET);

$mode   = doArgs('__mode',      null,   $_GET);
$module = doArgs('__module',    'core', $_GET);
$action = doArgs('__action',    null,   $_GET);
$extra  = doArgs('__extra',     null,   $_GET);

if( !User::$IS_ONLINE ){
    $objRoute->throwHTTP(404);
}

//make sure they are getting at the right panel
$checkMode = array('admin', 'mod', 'user');
if(!in_array($mode, $checkMode)){
    hmsgDie('FAIL', 'Error: Unknown Panel Group');
}


$objPage->setTheme('perfectum');
$objPage->addBreadcrumbs(array(
    array('url' => '/'.root().$mode.'/', 'name' => ucwords($mode).' Control Panel')
));


$objPage->setTitle('Test');

$objRoute->processURL( $_SERVER['QUERY_STRING'] );

$objPage->buildPage();
$objPage->showHeader();
    if(!$objTPL->isHandle('body')){
        msgDie('FAIL', 'No output received from module.');
    }else{
        echo $objTPL->get_html('body');
    }
$objPage->showFooter();

?>