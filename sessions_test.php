<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

// $objPage->setTheme();
// $objPage->setTitle('Test');
// $objPage->buildPage();
// $objPage->showHeader(); // lol i am editing that o.O

$objUser = coreObj::getUser(); // Not  being instanciated in core

$array = array(
    'usercode' => 'g6dwtw', // Was g6dtwt
    'show_email' => 1,
    'avatar' => 'trololol.png'
);

$var = $objUser->updateUser( 1, $array );

echo dump( $var );
?>