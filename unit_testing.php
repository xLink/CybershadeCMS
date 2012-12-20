<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
set_time_limit(0);
require_once 'core/core.php';

$objRoute = coreObj::getRoute();
$objPage  = coreObj::getPage();
$objTPL   = coreObj::getTPL();

$objPage->setTheme();

$objPage->setTitle('Test');

$objModule = $objRoute->processURL($_SERVER['QUERY_STRING']);

$objPage->buildPage();
$objPage->showHeader();

if ( $objModule !== false ) {
    $objModule->output();
}

$objUnit = coreObj::getUnit();

$a = '1';
$b = array('test');
$c = NULL;

function test( $ab ){
  return $ab;
}

echo $objUnit->useStrict()
    ->test( test( $a ), 'is_string')
    ->test( $b, 'is_bool', 'test', 'Array me pl0x')
    ->test( $c, 'is_bool', 'test', 'NULLIFY')
    ->run();

?>
