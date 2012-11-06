<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');
$objUser = coreObj::getUser();

echo $objUser->register(array(
	'id' => 1,
	'username'	=> 'DarkMantis',
	'password' => 'chadwick',
	'pin'	=> '',
	'register_date' => time(),
	'last_active' => 0,
	'usercode' => substr(md5(time()), 8, 8),
	'uid' => 1,
	'birthday' => '15/01/1991',
	'sex' => 'pl0x',
	'contact_info' => 'Your mom'
));

?>