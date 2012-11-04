<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();

$objPage->setTitle('Test');

<<<<<<< Updated upstream
$objPage->buildPage();
$objPage->showHeader();

//$_SESSION['rawr'] = 1;

echo dump($_SESSION);

=======

$objPage->buildPage();
$objPage->showHeader();

>>>>>>> Stashed changes
$objPage->showFooter();
?>