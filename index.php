<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$a = $objSQL->getDebug();
echo dump($a, 'SQL Debug');

 $a = memoryUsage();
 echo dump($a, 'Exec Info');

?>