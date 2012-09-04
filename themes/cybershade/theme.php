<?php

//check if we got the page object
if(!is_object($objPage)){
    $objPage = self::getPage();
}

//if(LOCALHOST){
    $objPage->addCSSFile('/'.root().self::$THEME_ROOT.'theme.less', 'text/css', 'stylesheet/less');
    $objPage->addJSFile('/'.root().'assets/javascript/less.min.js', 'footer');
//}else{
//    $objPage->addCSSFile('/'.root().self::$THEME_ROOT.'theme.css', 'text/css');
//}

?>