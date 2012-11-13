<?php

class Override_viewIndex extends Module_core{

    public function viewIndex(){
        echo dump($a, 'OVERRIDE INITITATED!!');
        $this->setView('viewIndex/default.tpl');

    }
}
?>