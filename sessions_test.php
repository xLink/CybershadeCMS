<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();
$objPage->setTitle('Test');
$objPage->buildPage();
$objPage->showHeader(); // lol i am editing that o.O

echo $objSession->killAllSessions();
echo $objSession->createSession();

$var = $objSession->session_id;

echo dump( $var, 'Session ID', 'yellow' );

echo $objSession->getSessionById( $var );

echo $objSession->getSessionsByType('active');
echo dump($_SESSION);
?>