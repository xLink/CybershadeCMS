<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

echo dump($a);
$objPage->setTheme();

$objPage->setTitle('Test');

$objPage->buildPage();
$objPage->showHeader();


    $objRoute = coreObj::getRoute();

    $route = '/forum/omfg/some-thread-here-12.html';
    $routeTest = $objRoute->findMatch($route);
    echo dump($routeTest);

    echo dump($objRoute->route, $route);

    $pattern = $objRoute->prepareRoute($objRoute->route);
    echo dump($pattern);


$objPage->showFooter();
?>