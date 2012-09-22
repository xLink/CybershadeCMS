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
    'pattern'      => '/backup',
    'arguments'	   => array(
    	'module'	=> 'backup',
    	'method'	=> 'go'
    ),
    'label'        => 'backup',
    'status'       => '0',
) );

?>