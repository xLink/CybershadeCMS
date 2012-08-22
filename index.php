<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->addCSSFile('/'.root().'assets/styles/default.css', 'text/css');

$objPage->addCSSFile(array(
    'src'  => '/'.root().'assets/styles/default.css', 
    'type' => 'text/css',
    'rel'  => 'stylesheet',
));
?>