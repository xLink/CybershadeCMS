<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

interface Core_Classes_baseDetails{
    public function details();
    public function getBlocks();
    public function install();
    public function uninstall();
}
?>