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

        Core_Classes_coreObj::getTPL()->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

    }

    public function cache(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'panel'  => cmsROOT . 'modules/core/views/admin/cache/content.tpl',
        ));

        if( isset($_SESSION['errors']['cache']) ){
            $objTPL->assign_block_vars('_msg', array(
                'MSG'  => '<strong>Error!</strong> '. implode('<br />', $_SESSION['errors']['cache']),
                'TYPE' => 'alert-error',
            ));
            unset($_SESSION['errors']['cache']);
        }else{

            $msg = 'This page will clear the various CMS Caches, to do so, hit the button representing the cache you want to clear.';
                $objTPL->assign_block_vars('_msg', array(
                    'MSG'  => '<strong>Info!</strong> '.( isset($_SESSION['msgs']['cache']) ? implode('<br />', $_SESSION['msgs']['cache']) : $msg),
                    'TYPE' => 'alert-info',
                ));
            if( isset($_SESSION['msgs']['cache']) ){
                unset($_SESSION['msgs']['cache']);
            }

        }

        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   => 'Cache Control',
                'CONTENT' => $objTPL->get_html('panel', false),
                'ICON'    => 'icon-th-list',
            ),
        ));
    }


    public function clear(){
        $objPage = Core_Classes_coreObj::getPage();
        $item    = doArgs('clear', false, $_GET);

        if( $item === false || is_empty($item) ){
            $_SESSION['errors']['cache'][] = 'Please use buttons below to clear cache.';

            $this->cache();
            return;
        }

        $objCache = Core_Classes_coreObj::getCache();

        switch($item){
            case 'stores':
                $cacheFiles = glob(cmsROOT.'cache/cache_*.php');
            break;

            case 'media':
                $cacheFiles = glob(cmsROOT.'cache/media/minify_*');
            break;

            case 'template':
                $cacheFiles = glob(cmsROOT.'cache/template/tpl_*');
            break;
        }

        if( $objCache->remove( $item ) ){
            $_SESSION['msgs']['cache'][] = 'Cache Cleared: <br />'.implode('<br />', $cacheFiles);
        }else{
            $_SESSION['errors']['cache'][] = 'Could not clear cache, maybe it has already been cleared.';
        }

        $this->cache();
    }

}


?>