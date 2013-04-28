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
class Admin_Modules_core_blocks extends Admin_Modules_core{

    public function __construct() {
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

    }

    /**
     * Lists the blocks for easier access
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *  
     * @return  void
     */
    public function blocks() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'panel' => cmsROOT. 'modules/core/views/admin/blocks/default/block_list.tpl',
        ));

        // List the different types of blocks
        $query = $objSQL->queryBuilder()
            ->select('id', 'label')
            ->from('#__blocks')
            ->groupBy('region_name')
            ->build();

        $blocks = $objSQL->fetchAll( $query, 'id' );

        foreach( $blocks as $block ) {
            $objTPL->assign_block_vars( 'list', array(
                'URL'  => '/' . root() . 'admin/core/blocks/edit/' . secureMe($block['block_name']),
                'NAME' => secureMe($block['menu_name'])
            ));
        }

        $objTPL->parse('panel', false);
        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Block Administration',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }

    /**
     * Adds a new link to the menu
     * 
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * 
     * @return  void
     */
    public function add() {

        $objTPL     = Core_Classes_coreObj::getTPL();
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objPage    = Core_Classes_coreObj::getPage();
        $objForm    = Core_Classes_coreObj::getForm();
        $objModule  = Core_Classes_coreObj::getModule();

        $objTPL->set_filenames(array(
            'panel' => cmsROOT. 'modules/core/views/admin/blocks/default/block_add.tpl',
        ));

        // Get a list of modules we could use

        // Get a list of Menus we could use
        $query = $objSQL->queryBuilder()
            ->select('id', 'menu_name')
            ->from('#__menus')
            ->groupBy('menu_name')
            ->build();

        $menus = $objSQL->fetchAll( $query );

        foreach( $menus as $menu ) {

            $objTPL->assign_block_vars( 'menu', array(
                'VALUE' => $menu['id'],
                'NAME'  => $menu['menu_name']
            ));
        }

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Block Administration',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }}
?>