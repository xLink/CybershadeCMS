<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
include_once('core/core.php');

$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();

$objPage->setTheme();

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