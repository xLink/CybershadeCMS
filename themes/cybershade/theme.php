<?php

//check if we got the page object
if(!is_object($objPage)){
    $objPage = self::getPage();
}

if(LOCALHOST){
    $objPage->addCSSFile(array(
        'href'     => '/'.root().self::$THEME_ROOT.'theme.less',
        'rel'      => 'stylesheet/less',
        'priority' => HIGH
    ));
    $objPage->addJSFile(array(
        'src' => '/'.root().'assets/javascript/less.min.js',
        'priority' => HIGH
    ), 'footer');
}else{
    $objPage->addCSSFile(array(
        'href'     => '/'.root().self::$THEME_ROOT.'theme.css',
        'type'     => 'text/css',
        'priority' => HIGH
    ));
}

    $objPage->addJSFile(array(
        'src' => '/'.root().'assets/javascript/bootstrap.mootools.js',
        'priority' => HIGH
    ), 'footer');

?>