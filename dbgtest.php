<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$debugMe = $objSQL;
?>
<table class="table table-bordered table-striped">
    <tr>
        <td width="33%" valign="top"><?php echo debug::dump($debugMe); ?></td>
<!--         <td width="33%" valign="top"><?php echo dump($debugMe); ?></td>
        <td width="33%" valign="top"><?php echo '<pre>'.print_r($debugMe, true).'</pre>'; ?></td>
 -->    </tr>
</table>
