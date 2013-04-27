<?php

//check if we got the page object
if( !isset( $objPage ) ) {
    $objPage = self::getPage();
}

// load bootstrap with the framework extras
$objPage->addCSSFile(array(
    'href'     => '/'.root().'assets/styles/bootstrap-min.css',
    'priority' => HIGH
));
$objPage->addCSSFile(array(
    'href'     => '/'.root().'assets/styles/extras-min.css',
    'priority' => HIGH
));


if(false){
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
    'src' => '/'.root().self::$THEME_ROOT.'extras.js',
    'priority' => HIGH
), 'footer');


?>