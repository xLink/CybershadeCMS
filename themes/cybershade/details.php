<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Details_cybershade extends Core_Classes_coreObj implements Core_Classes_baseDetails {

    public function details(){
        return array(
            'version'              => '1.0',
            'since'                => '1.0',
            'min_version_required' => '1.0',

            'name'                 => 'Cybershade',
            'mode'                 => 'user',
            'author'               => 'xLink',
            'homepage_url'         => 'http://cybershade.org',
            'repo_url'             => 'http://github.com/cybershade/cscms/',
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