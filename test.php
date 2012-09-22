<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

if( preg_match( '#/forum/(\d+)/([A-Za-z0-9\-\_]+)-(\d+).html#', '/forum/14/some-thread-here-12.html' ) )
{
	echo 'wwoooo';
}

?>