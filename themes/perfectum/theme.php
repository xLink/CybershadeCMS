<?php

//check if we got the page object
if( !isset( $objPage ) ) {
    $objPage = self::getPage();
}
$browserCSSSelectors = explode('/', $browserCSSSelectors);

$objPage->addCSSFile(array(
    'href'     => '/'.root().self::$THEME_ROOT.'assets/styles/bootstrap.min.css',
    'type'     => 'text/css',
    'priority' => HIGH
));

$objPage->addCSSFile(array(
    'href'     => '/'.root().self::$THEME_ROOT.'assets/styles/bootstrap-responsive.min.css',
    'type'     => 'text/css',
    'priority' => HIGH
));

// $objPage->addCSSFile(array(
//     'href'     => '/'.root().'assets/styles/default.css',
//     'type'     => 'text/css',
//     'priority' => HIGH
// ));

$objPage->addCSSFile(array(
    'href'     => '/'.root().self::$THEME_ROOT.'assets/styles/style.css',
    'type'     => 'text/css',
    'priority' => HIGH
));

$objPage->addCSSFile(array(
    'href'     => '/'.root().self::$THEME_ROOT.'assets/styles/style-responsive.css',
    'type'     => 'text/css',
    'priority' => HIGH
));

if( in_array('ie', $browserCSSSelectors) && !in_array('ie9', $browserCSSSelectors) ){
    $objPage->addCSSFile(array(
        'href'     => '/'.root().'assets/styles/style-ie.css',
        'type'     => 'text/css',
        'priority' => HIGH
    ));
}

$objPage->addJSFile(array(
    'src' => '/'.root().'assets/javascript/bootstrap.mootools.js',
    'priority' => HIGH
), 'footer');

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery-1.9.1.min.js',
    'priority' => HIGH
), 'footer' ); 
$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery-migrate-1.0.0.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery-ui-1.10.0.custom.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.ui.touch-punch.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/bootstrap.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.cookie.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/fullcalendar.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.dataTables.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/excanvas.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.flot.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.flot.pie.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.flot.stack.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.flot.resize.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.chosen.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.uniform.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.cleditor.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.noty.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.elfinder.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.raty.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.iphone.toggle.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.uploadify-3.1.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.gritter.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.imagesloaded.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.masonry.min.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.knob.js',
    'priority' => HIGH
), 'footer' ); 

$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/jquery.sparkline.min.js',
    'priority' => HIGH
), 'footer' ); 
$objPage->addJSFile(array(
    'src' => '/' . root() . self::$THEME_ROOT . 'assets/javascript/custom.js',
    'priority' => HIGH
), 'footer' ); 

?>