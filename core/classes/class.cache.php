<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class cache extends coreObj{

    public $cacheToggle = false,
           $output      = array(),
           $cacheDir    = '',
           $fileTpl     = '';

    public function __construct($name='', $args=array()){
        $a = func_get_args();
        echo dump($a);
        $this->setVars(array(
            'cacheToggle' => doArgs('useCache', false, $args),
            'cacheDir'    => doArgs('cacheDir', '', $args),
            'fileTpl'     => cmsROOT.'cache/cache_%s.php',
        ));
    }

}
?>