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
$objPage->showHeader();


/**
throw some session stuff in here8
+
**/

$var = $objSession->createSession();

echo dump( $var );



$objPage->showFooter();
?>