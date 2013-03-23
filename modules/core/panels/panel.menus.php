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
class Admin_Modules_core_menus extends Admin_Modules_core{

    public function menus() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/menus/default/menu_list.tpl',
        ));

        // List the different types of menus
        $query = $objSQL->queryBuilder()
            ->select('id', 'menu_name')
            ->from('#__menus')
            ->groupBy('menu_name')
            ->build();

        $menus = $objSQL->fetchAll( $query, 'id' );

        foreach( $menus as $menu ) {
            $objTPL->assign_block_vars( 'list', array(
                'URL'  => '/' . root() . 'admin/core/menus/edit/' . secureMe($menu['menu_name']),
                'NAME' => secureMe($menu['menu_name'])
            ));
        }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Menu Administration',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }

    public function edit($args = array()) {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objPage    = Core_Classes_coreObj::getPage();


        // Check we have the menu name
        if( !is_array( $args ) || !is_string( $args[1] ) || strlen( $args[1] ) == 0 ) {
            // error
            trigger_error('Error: Could not get menu name');
            $this->menus();
            return;
        }

        /** Menu JS **/
        $objPage->addCSSFile(array(
            'href' => '/'.root().'modules/core/assets/styles/admin/menus/Tree.css',
            'type' => 'text/css',
        ));

        $objPage->addCSSFile(array(
            'href' => '/'.root().'modules/core/assets/styles/admin/menus/Collapse.css',
            'type' => 'text/css',
        ));

        $objPage->addJSFile(array(
            'src' => '/'.root().'modules/core/assets/javascript/admin/menus/Tree.js',
        ), 'footer');

        $objPage->addJSFile(array(
            'src' => '/'.root().'modules/core/assets/javascript/admin/menus/custom.js',
        ), 'footer');


        $menuName = $args[1];

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT . 'modules/core/views/admin/menus/default/menu_link_list.tpl',
        ));

        $queryList =  $objSQL->queryBuilder()
            ->select('*')
            ->from('#__menus')
            ->where('menu_name', '=', $menuName)
            ->orderBy('`parent_id`, `order`', 'ASC')
            ->build();

        $links = $objSQL->fetchAll( $queryList );
            if( !is_array( $links ) ) {
                // Trigger error
                // Add error to tpl
                return false;
            }

        $args = array( 'title' => 'link_title', 'id' => 'id', 'parent' => 'parent_id' );
        $tree = $this->generateTree($links, $args);
        $objTPL->assign_var( 'tree_menu', str_replace('<ul>', '<ul id="tree" class="tree">', $tree) );

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Menu Administration - <strong>'.secureMe($links[0]['name']).'</strong>',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

            $objTPL->assign_vars('block.custom.BUTTON', '<a href="javascript:;" class="btn btn-success" id="save"><i class="icon icon-save"></i> Save Menu</a>');

        $objTPL->parse('body', false);
    }


    /**
     * Generates a Tree from an multidimensional array
     * http://sandeepsamajdar.blogspot.co.uk/2011/05/generate-tree-from-php-array.html
     *
     * @version 1.0.0
     * @since   1.0.0
     * @author  Modified by Dan Aldridge
     *
     * @return  string
     */
    function generateTree($array, $args, $parent= 0, $level= 0) {
        $has_children = false; $output = null;
        foreach($array as $key => $value){
            if ($value[ $args['parent'] ] == $parent){
                if ($has_children === false){
                    $has_children = true;

                    $output .= '<ul>';
                    $level++;
                }
                $output .= '<li'. ($level>900 ? ' class="nodrop"' :'') .' id="'. $value[ $args['id'] ] .'"><span>' . '('.$value[ $args['id'] ].') '. $value[ $args['title'] ] . '</span>';
                $output .= $this->generateTree($array, $args, $value[ $args['id'] ], $level);
                $output .= '</li>';
            }
        }
        if ($has_children === true){
            $output .= '</ul>';
        }
        return $output;
    }

}


?>