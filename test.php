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

$form = new form();

echo $form->start('testForm');

echo '<label for="text">Text:</label>';

echo $form->inputbox('text');

echo $form->button('submit', 'submit', array('type' => 'submit'));

echo $form->finish();

$objPage->showFooter();

?>
