<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class core extends Module{


    public function login(){
        $objForm = coreObj::getForm();

        echo dump($a, 'called');

    }
}

?>