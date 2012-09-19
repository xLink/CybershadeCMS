<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

echo dump($objCache);

                // $objCore->objCache->initCache($file.'_db', 'cache_'.$file.'.php',
                //     'SELECT * FROM `$Pconfig`', $new_file);
$objCache->doCache('config');
$objCache->doCache('routes');
$objCache->doCache('statistics');
?>