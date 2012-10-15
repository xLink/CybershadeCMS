<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();

$objPage->setTitle('Test');

$objRoute = coreObj::getRoute();
$objRoute->processURL( $_SERVER['QUERY_STRING'] );

// $a = memoryUsage();
// echo dump($a, 'Exec Info');

// $a = $objTPL->_tpldata;
// echo dump($a);

$objPage->buildPage();
$objPage->showHeader();
    /*if(!$objTPL->get_html('body')){
        msgDie('FAIL', 'No output received from module.');
    }else{
        echo $objTPL->get_html('body');
    }*/
$objPage->showFooter();
?>