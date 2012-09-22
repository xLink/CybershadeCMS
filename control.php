<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objRoute = coreObj::getRoute();

#include_once('forumInstaller.php');
/*
$currentURL = $_GET['l'];

$routes[] = array(
    'method'        => 'any',
    'pattern'       => '/forum/:cat/:name-:id.html?reply',
    'module'        => 'forum',
    'arguments'     => array(
        'module' => 'forum',
        'method' => 'newReply',
    ),
    'requirements'  => array(
        'cat' => '\w+',
        'id'  => '\d+',
    ),
    'label'         => 'newReply',
    'status'        => null,
    'redirect'      => null
);

$routes[] = array(
    'method'        => 'any',
    'pattern'       => '/forum/:cat/:name-:id.html',
    'module'        => 'forum',
    'arguments'     => array(
        'module' => 'forum',
        'method' => 'viewThread',
    ),
    'requirements'  => array(
        'cat' => '\w+',
        'id'  => '\d+',
    ),
    'label'         => 'viewThread',
    'status'        => null,
    'redirect'      => null
);
*/

$objRoute->processURL( $_GET['l'] );


$a = coreObj::getPlugins()->getVar('hooks');
echo dump($a, 'Hooks');

$a = memoryUsage(' Last :) ');
echo dump($a, 'Exec Info');

?>