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

$v = true;

echo $objUnit->assertTrue($v)
    ->assertFalse($v)
    ->test(array(), 'is_array', 'Array Test', 'Some notes')
    ->test(array(), 'is_string', 'Array Test', 'Some notes')
    ->test(new stdClass(), 'is_array', 'Object Test', 'Some notes')
    ->test('test', 'is_string')
    ->test(new stdClass(), 'is_object')
    ->test(new stdClass(), 'is_array')
    ->run();


?>
