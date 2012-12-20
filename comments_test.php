<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
require_once('core/core.php');

$objComments = Module::getComments();

$test = $objComments->start('TPL_VAR', 'PAGINATION', 'comments', '14cf620a27f0a3c2df48a09e4edd7139');

// happy linky? XD &lt;3!
echo dump( $test );

?>

<div id="tplVar">
  {TPL_VAR}
</div>
<div id="pagination">
  {PAGINATION}
</div>

