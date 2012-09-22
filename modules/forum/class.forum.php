<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class forum extends Module{

    public function __call($name, $arguments){
        echo dump($arguments, 'Called forum::'.$name);
    }


}

?>