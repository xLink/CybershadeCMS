<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Details_markdown implements Core_Classes_baseDetails{

    public function details(){
        return array(
            'version'              => '1.0.0',
            'since'                => '1.0.0',
            'min_version_required' => '1.0.0',

            'author'               => 'Daniel Noel-Davies',
            'homepage_url'         => 'http://NoelDavies.cybershade.org',
            'repo_url'             => 'http://github.com/cybershade/module_pages/',

            'requirements'         => array(
                ''
            ),
        );
    }

    public function getBlocks(){
        
    }

    public function install(){


    }

    public function uninstall(){

    }

}
?>