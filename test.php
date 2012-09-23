<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objRoute = coreObj::getRoute();

$objRoute->addRoute( array(
    'method'       => 'get',
    'pattern'      => '/:group',
    'arguments'    => array(
		'module' => 'group',
		'method' => 'index',
    ),
    'label'        => 'group-index',
    'status'       => '1',
));

$objRoute->addRoute( array(
    'method'       => 'get',
    'pattern'      => '/:group/forum/:cat/:name-:id.html',
    'arguments'    => array(
		'module'	=> 'forum',
		'method'	=> 'viewThread'
    ),
    'label'        => 'group-view-thread',
    'status'       => '1',
));

$objRoute->addRoute( array(
    'method'       => 'get',
    'pattern'      => '/:group/users/list',
    'arguments'    => array(
     'module' => 'group',
     'method' => 'userList'
    ),
    'label'        => 'group-user-list',
    'status'       => '1',
));

$objRoute->addRoute( array(
    'method'       => 'get',
    'pattern'      => '/:group/about-us',
    'arguments'    => array(
		'module' => 'content',
		'method' => 'render',
		'page'	 => 'about-us'
    ),
    'label'        => 'group-page-about',
    'status'       => '1',
));

/*
/cybershade/
	-/about-us
	-/users/list
	-/forum/:cat/:name-:id.html
*/
?>