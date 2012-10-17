<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();

$objPage->setTitle('Test');

$objPage->addJSFile(array('src' => '/'.root().'assets/javascript/tabs.js'), 'footer');
$objPage->addJSFile(array('src' => '/'.root().'assets/javascript/debug.js'), 'footer');


$objPage->buildPage();
$objPage->showHeader();


$objPage->showFooter();

?>