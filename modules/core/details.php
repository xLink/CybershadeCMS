<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Details_core extends Core_Classes_Module implements Core_Classes_baseDetails{

    public function details(){
        return array(
            'version'              => '1.0',
            'since'                => '1.0.0',
            'min_version_required' => '1.0.0',

            'name'                 => 'Core',
            'description'          => 'Core Module',
            'author'               => 'xLink',
            'homepage_url'         => 'http://cybershade.org',
            'repo_url'             => 'http://github.com/cybershade/cscms/',
        );
    }

    public function registerBlocks(){
        return array(
            'login' => 'login_block'
        );
    }

    public function registerRoutes(){
        return array(
            'core_viewIndex' => array(
                'label' => 'core_viewIndex',
                'pattern' => '/',

            ),
        );
    }

    public function registerPlugins(){
        return array(
            'plugins/core.php'
        );
    }

    public function install(){


    }

    public function uninstall(){

    }

}
?>