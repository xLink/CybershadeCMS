<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objSession = coreObj::getSession();

echo $objSession->killAllSessions();
echo $objSession->createSession();

$var = $objSession->session_id;

echo dump( $var, 'Session ID', 'yellow' );

echo $objSession->getSessionById( $var );

echo $objSession->getSessionsByType('active');
?>