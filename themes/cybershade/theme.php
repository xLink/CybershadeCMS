<?php

//check if we got the page object
if(!is_object($objPage)){
    $objPage = self::getPage();
}

//if(LOCALHOST){
    $objPage->addCSSFile(array(
        'href'     => '/'.root().self::$THEME_ROOT.'theme.less',
        'type'     => 'text/css',
        'rel'      => 'stylesheet/less',
        'priority' => MED
    ));
    $objPage->addJSFile(array(
        'src' => '/'.root().'assets/javascript/less.min.js',
        'priority' => HIGH
    ), 'footer');
//}else{
//    $objPage->addCSSFile('/'.root().self::$THEME_ROOT.'theme.css', 'text/css');
//}

?>