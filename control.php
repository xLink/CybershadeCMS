<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objRoute = coreObj::getRoute();

	include_once('forumInstaller.php');
$objRoute->addRoute(array('index' => array(
	'/forum',
	array(
		'module' => 'forum', 
		'method' => 'viewIndex',
	)
)));
exit;
$objRoute->addRoutes( $routes );
/*
echo dump($objRoute);

$a = $objRoute->processRoutes($routes);
echo dump($a);
echo dump($routes);

$objRoute->deleteRoute( 1 );
*/
echo dump($routes, 'Routes', '#00ff00', true);
#$objRoute->cacheRoutes();
?>