<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);

include_once('core/core.php');

$objUpload = coreObj::getUpload();
$objForm = coreObj::getForm();

echo $objForm->start('test', array( 'upload' => true ));
echo $objForm->finish();

?>