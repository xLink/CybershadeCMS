<?php

function recache(){
    $a = $_GET;
    echo dump($a, 'RECACHE BOOM!');
}

$this->addHook('CMS_START', 'recache');
?>