<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);

include_once('core/core.php');
$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();

$objPage->setTheme();

$objPage->setTitle('Test');

$objRoute->processURL( $_SERVER['QUERY_STRING'] );


$objUpload = coreObj::getUpload();
$objForm   = coreObj::getForm();

$c = $objUpload->makePublic(1);
echo dump( $c );

echo $objForm->start('test', array( 'method' => 'post', 'upload' => true, 'action' => $_SERVER['PHP_SELF'] ));
echo $objForm->inputBox('file', 'file', 'file');
echo $objForm->inputBox('submit', 'submit', 'Submit!');
echo $objForm->finish();

if( isset( $_POST['submit'] ) ){
	$var = $objUpload->doUpload( array('txt') );
	echo dump( $var );
}

$objPage->buildPage();
$objPage->showHeader();
$objPage->showFooter();


?>