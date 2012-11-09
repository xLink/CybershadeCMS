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
$objUser = coreObj::getUser();


// Test Selects
$query = $objSQL->queryBuilder()
		->select('*')
		->from('#__users')
		->where('id = 1')
			->andWhere('username', '=', 'xLink')
		->build();

$objSQL->query($query);

// Test Inserts
$data = array(
	'username' => 'test',
	'password' => $objUser->mkPassword('test')
);
$query = $objSQL->queryBuilder()
		->insertInto('#__users')
		->set($data)
		->build();

$objSQL->query($query);

// test update
$data = array(
	'username' => 'newTest'
);
$query = $objSQL->queryBuilder()
		->update('#__users')
		->set($data)
		->where('username', '=', 'test')
		->build();

$objSQL->query($query);

// test update
$query = $objSQL->queryBuilder()
		->deleteFrom('#__users')
		->where('username', '=', 'newTest')
		->build();

$objSQL->query($query);


$objPage->showFooter();
?>