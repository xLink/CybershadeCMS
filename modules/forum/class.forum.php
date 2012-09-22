<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class forum extends Module{

    public function __call(){
        $a = func_get_args();
        echo dump($a, 'Arguments Called in forum module');
    }


}

?>