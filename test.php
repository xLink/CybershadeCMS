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
?>

<div id="debugBar" class="wrapper">
	<div id="debugBtn" class="socicon-cogs"></div>
	<div class="container" id="errorContent">
		<ul class="tab-nav">
			<li><a href="#console">Console Log</a></li>
			<li><a href="#errors">PHP / CMS Errors</a></li>
			<li><a href="#queries">SQL Queries</a></li>
			<li><a href="#files">Included Files</a></li>
			<li><a href="#tpls">Template Files</a></li>
		</ul>
		<div class="tab-panes">
			<div class="tab-pane">Console Log n'shit</div>
			<div class="tab-pane">PHP Errors yo</div>
			<div class="tab-pane">Query's and shit</div>
			<div class="tab-pane">All those m'fucking included files</div>
			<div class="tab-pane">GEIF ME ALL THE TEMPLATE FILES!!111</div>

		</div>
	</div>
</div>

<?php
//echo $objDebug->output();

$objPage->showFooter();

?>

JKP2YZJ