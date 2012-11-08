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

class something {}

$values = array(
	true,
	false,
	'true',
	'false',
	1,
	0,
	0.00,
	0.56,
	1.54,
	-1,
	null,
	array(), 
	'',
	new something
);

$functions = array(
	'is_empty',
	'empty',
	'is_null',
	'isset',
	'count',
);

echo '<table class="table table-bordered table-hover"><tr><td></td>';

foreach( $values as $value ) {
	echo '<th>';

	var_dump($value);

	echo '</th>';
}

echo '</tr>';

// Loop through the functions
foreach( $functions as $function ) {
	printf('<tr><th>%s</th>', $function);

	foreach( $values as $value ) {
		if( $function === 'empty' && empty($value) ) {
			echo '<td style="background-color:green;">&#10004;</td>';

 		} else if( $function === 'empty' && !empty($value) ) {
			echo '<td style="background-color:red;">x</td>';

		} else if( $function === 'isset' && isset($value) ) {
			echo '<td style="background-color:green;">&#10004;</td>';
			
 		 } else if( $function === 'isset' && !isset($value) ) {
			echo '<td style="background-color:red;">x</td>';

		} else if( call_user_func( $function, $value ) == true ) {
			echo '<td style="background-color:green;">&#10004;</td>';

		} else {
			echo '<td style="background-color:red;">x</td>';
		}
	}

	echo '</tr>';
}

echo '</table>';

$objPage->showFooter();
?>