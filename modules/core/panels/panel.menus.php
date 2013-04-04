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

    public function __construct() {
        Core_Classes_coreObj::getTPL()->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

    }

    /**
     * Lists the menus for easier access
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *  
     * @return  void
     */
    public function menus() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'panel' => cmsROOT. 'modules/core/views/admin/menus/menu_list.tpl',
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

    /**
     * Adds a new link to the menu
     * 
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * 
     * @return  void
     */
    public function newlink() {
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objPage    = Core_Classes_coreObj::getPage();
        $objForm    = Core_Classes_coreObj::getForm();

        $objPage->addJSFile(array(
            'src' => '/'.root().'modules/core/assets/javascript/admin/menus/custom.js',
        ), 'footer');

        // List the different types of menus
        $query = $objSQL->queryBuilder()
            ->select('id', 'menu_name')
            ->from('#__menus')
            ->groupBy('menu_name')
            ->build();

        $menus = $objSQL->fetchAll( $query, 'id' );

        $options = array();
        foreach( $menus as $id => $menu ){
            $options[ $menu['menu_name'] ] = $menu['menu_name'];
        }
        $options[ '*add*' ] = 'Add to new menu..';


        $form = $objForm->outputForm(array(
            'FORM_START'     => $objForm->start('new_link', array('method'=>'POST', 'action'=>'/'.root().'admin/core/menus/newlinkSave/', 'class'=>'form-horizontal')),
            'FORM_END'       => $objForm->finish(),

            'FORM_TITLE'     => 'Add a link',
            'FORM_SUBMIT'    => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
            'FORM_RESET'     => $objForm->button('reset', 'Reset'),
        ),
        array(
            'field' => array(
                'Link Name'       => $objForm->inputbox('name', 'text'),
                'URL'             => $objForm->inputbox('url', 'text'),

                'Menu Identifier' => $objForm->select('ident1', $options).
                    $objForm->inputbox('ident2', 'input', '', array(
                        'class' => 'hide'
                    )),

                'External Link?'  => $objForm->radio('external', array(
                    '0' => langVar('L_YES'),
                    '1' => langVar('L_NO'),
                ), 0),

            ),
            'desc' => array(
            ),
            'errors' => $_SESSION['errors']['menus'],
        ));

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Menu Administration',
            'CONTENT' => $form,
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
    public function newlinkSave() {
        $_SESSION['errors']['menus'] = array();
        $objPage = Core_Classes_coreObj::getPage();
        $objSQL = Core_Classes_coreObj::getDBO();

        $url = $this->config('global', 'fullPath');

        if( !HTTP_POST ){
            $_SESSION['errors']['menus'][] = 'Please use the form to submit the data.';

            $objPage->redirect( str_replace('newlinkSave', 'newlink', $url) );
            exit;
        }

        $save = array('ident1', 'ident2', 'name', 'url', 'external');

        foreach( $save as $key ){
            if( !isset($_POST[ $key ]) ){
                $_SESSION['errors']['menus'][] = 'Not all required data is available, please try again.';

                $objPage->redirect( str_replace('newlinkSave', 'newlink', $url) );
                exit;
            }
        }


        $ident = doArgs('ident1', false, $_POST);
        if( $ident === false || $ident == '*add*' ){
            $ident = doArgs('ident2', null, $_POST);
        }


        $insert = array();
        $insert['menu_name']  = $ident;
        $insert['link_url']   = doArgs('url', null, $_POST);
        $insert['link_title'] = doArgs('name',  null, $_POST);
        $insert['external']   = doArgs('external', 0, $_POST);
        $insert['order']      = 1000;

        $insertQuery = $objSQL->queryBuilder()
            ->insertInto('#__menus')
            ->set($insert)
            ->build();

        $insertResult = $objSQL->query( $insertQuery );
            if( $insertResult === false ){
                $_SESSION['errors']['menus'][] = 'There was a problem saving the menu, SQL Said: '.$objSQL->getError();

                $objPage->redirect( str_replace('newlinkSave', 'newlink', $url) );
                exit;
            }

        $menuIdent = urlencode( $ident );
        $objPage->redirect( str_replace('newlinkSave', 'edit/'.$menuIdent, $url) );
        exit;
    }

    /**
     * Editor for the menu system
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *  
     * @return  void
     */
    public function edit($args = array()) {
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objPage    = Core_Classes_coreObj::getPage();

        // Check we have the menu name
        if( !is_array( $args ) || !is_string( $args[1] ) || strlen( $args[1] ) == 0 ) {
            // error
            trigger_error('Error: Could not get menu name.');
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
            'panel' => cmsROOT . 'modules/core/views/admin/menus/menu_link_list.tpl',
        ));

        $queryList =  $objSQL->queryBuilder()
            ->select('*')
            ->from('#__menus')
            ->where('menu_name', '=', $menuName)
            ->orderBy('`parent_id`, `order`', 'ASC');

        $links = $objSQL->fetchAll( $queryList->build() );
            if( !is_array( $links ) ) {
                trigger_error('Error: Menu does not exist.');
                $this->menus();
                return false;
            }

        $args = array( 'title' => 'link_title', 'id' => 'id', 'parent' => 'parent_id' );
        $tree = $this->generateTree($links, $args);
        $objTPL->assign_var( 'tree_menu', $tree );

        $objTPL->parse('panel', false);
        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Edit Menu - <strong>'.secureMe( $menuName ).'</strong>',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

            $objTPL->assign_block_vars('block.custom', array(
                'ICON' => 'icon-save',
            )); 

        $objTPL->parse('body', false);
    }

    /**
     * Saves the data from the menu editor
     *
     * @version         1.0
     * @since           1.0.0
     * @author          Dan Aldridge
     * @data-access     AJAX Only
     *  
     * @return          string
     */
    public function editSave($args = array()){

        if( !HTTP_POST ){
            die('Error: Could not get post data.');
        }

        $data = array(
            'menu_name' => doArgs('1', false, $args),
            'menu_data' => doArgs('menu', false, $_POST),
        );
            if( in_array($data, false) ){
                die( 'Error: could not retrieve proper data.' );
            }

        $data['menu_data'] = json_decode($data['menu_data'], true);
        $data['menu_data'] = $this->generateFlatTable($data['menu_data']);

        if( !is_array($data['menu_data']) || is_empty($data['menu_data']) ){
            die( 'Error: Could not process array.' );
        }

        $parents = null; $orders = null;
        foreach( $data['menu_data'] as $id => $row ){
            $parents .= sprintf(' WHEN `id`="%s" THEN "%s"'."\n", $id, $row['parent']);
            $orders  .= sprintf(' WHEN `id`="%s" THEN "%s"'."\n", $id, $row['order']);
        }

        // raw query, but honestly wouldnt know where to start with the query builder & this baby XD
        $objSQL = Core_Classes_coreObj::getDBO();
        $query = '
            UPDATE #__menus SET 
                `parent_id` = CASE 
                    '.$parents.'
                ELSE `parent_id` END,

                `order` = CASE 
                    '.$orders.'
                ELSE `order` END
            WHERE id IN("'. implode('", "', array_keys($data['menu_data'])).'")
        ';

        $query = $objSQL->query($query);
            if( $query === false ){
                die( 'Error: Could not run update query. SQL Said: '.$objSQL->getError() );
            }

        die( 'Info: Updated Successfully.' );
        exit;
    }


/**
 //
 //--Helper Functions
 //
**/

    /**
     * Generates a Tree from an multidimensional array
     * http://sandeepsamajdar.blogspot.co.uk/2011/05/generate-tree-from-php-array.html
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Modified by Dan Aldridge
     *
     * @return  string
     */
    function generateTree($array, $args, $parent= 0, $level= 0) {
        $has_children = false; $output = null;
        foreach($array as $key => $value){
            if ($value[ $args['parent'] ] == $parent) {
                if ($has_children === false) {
                    $has_children = true;

                    $output .= "\n".str_repeat("\t", $level+1).($level>=1 ? '<ul>' : '<ul class="tree" id="tree">')."\n";
                    $level++;
                }
                $output .= str_repeat("\t", $level+1).'<li'. ($level>9000 ? ' class="nodrop"' : '') .' id="'. $value[ $args['id'] ] .'"><span>' . '('.$value[ $args['id'] ].') '. $value[ $args['title'] ] . '</span>';
                $output .= $this->generateTree($array, $args, $value[ $args['id'] ], $level);
                $output .= str_repeat("\t", $level+1).'</li>'."\n";
            }
        }
        if ($has_children === true) {
            $output .= str_repeat("\t", $level).'</ul>'."\n";
        }
        return $output;
    }

    /**
     * Generates a flat array from an multidimensional array
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  array
     */
    function generateFlatTable($array, $parent=0, $orders=array()){

        // if we have nothing passed through, just stop here
        if( is_empty($array) ){
            return array();
        }

        // look through each element in the array
        $rows = array();
        foreach($array as $key => $value){
            // if this one has children, then recurse into that array before continuing
            if( !is_empty($value['child']) ){
                $rows += $this->generateFlatTable( $value['child'], $value['id'], $orders );
            }

            // output this row
            $rows[ $value['id'] ]['parent'] = (int)$parent;

            // reset the menus order, whatever we set on the UI is what should stick
            if( !isset($orders[ $parent ]) ){
                $orders[ $parent ] = 0;
            }
            $rows[ $value['id'] ]['order'] = ++$orders[ $parent ];
        }

        // sort the array by key & return!
        ksort($rows); 
        return $rows;
    }

}


?>