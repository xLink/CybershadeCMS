<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Core ACP Panel
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Dan Aldridge
 */
class Admin_Modules_core_cache extends Admin_Modules_core{

    public function __construct(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/cache/', 'name' => 'Cache Control' )
        ));
        
    }

    public function cache(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . 'modules/core/views/admin/cache/index/block.tpl',
        ));


        $objTPL->parse('body', false);
    }

}


?>