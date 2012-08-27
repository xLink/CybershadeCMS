<?php
$return = array();

//and then the CSS
require cmsROOT.'assets/images_config.php';
foreach($styles as $k => $array){ $return['style_'.$k] = rewrite($array, 'images'); }

//process the scripts
require cmsROOT.'assets/javascript_config.php';
foreach($scripts as $k => $array){ $return['script_'.$k] = rewrite($array, 'javascript'); }

return $return;

function rewrite($array, $dir){
    $nArray = array();
    foreach($array as $s){ $nArray[] = cmsROOT.'assets/'.$dir.'/'.$s; }
    return $nArray;
}
?>