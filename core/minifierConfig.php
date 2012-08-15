<?php
$return = array();

//process the scripts
require $cmsROOT.'assets/scripts/config.php';
foreach($scripts as $k => $array){ $return['script_'.$k] = rewrite($array, 'scripts'); }

//and then the CSS
require $cmsROOT.'assets/images/config.php';
foreach($styles as $k => $array){ $return['style_'.$k] = rewrite($array, 'images'); }

return $return;

function rewrite($array, $dir){
    global $cmsROOT;
    $nArray = array();
    foreach($array as $s){ $nArray[] = $cmsROOT.$dir.'/'.$s; }
    return $nArray;
}
?>