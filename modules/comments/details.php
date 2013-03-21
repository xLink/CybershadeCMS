<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Details_comments extends Core_Classes_Module implements Core_Classes_baseDetails{

    public function details(){
        return array(
            'version'              => '1.0.0',
            'since'                => '1.0.0',
            'min_version_required' => '1.0.0',

            'author'               => 'Richard Clifford',
            'homepage_url'         => 'http://cybershade.org',
            'repo_url'             => 'http://github.com/cybershade/cscms/',
            'module_id'             => '14cf620a27f0a3c2df48a09e4edd7139',
        );
    }

    public function getBlocks(){
        return array(

        );
    }

    public function install(){


    }

    public function uninstall(){

    }

}
?>