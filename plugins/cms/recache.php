<?php

function recache(){ 
    if(isset($_GET['_recache'])){
        echo dump($_GET, 'RECACHE BOOM!');
        $objCache = coreObj::getCache();

        $objCache->remove('stores');
        $objCache->remove('media');
        $objCache->remove('template');
    }
}

$this->addHook('CMS_PRE_SETUP_COMPLETE', 'recache');
?>