<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objRoute = coreObj::getRoute();

include_once('forumInstaller.php');

$currentURL = $_GET['l'];

$routes = array('newReply' => array(
		'/forum/:cat/:name-:id.html',
		array(
			'module' => 'forum', 
			'method' => 'newReply',
		), 
		array(
			'cat' => '\d+',
			'id'  => '\d+',
		),
	));

$objRoute->setVar( 'routes', $routes );
$objRoute->processURL( $currentURL );

?>