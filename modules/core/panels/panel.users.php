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
 * @author  Daniel Noel-Davies
 */
class Admin_Modules_core_users extends Admin_Modules_core{

    public function manage(){
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT . 'modules/core/views/admin/users/list.tpl',
        ));

        $query = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__users')
            ->orderby('id')
            ->build();

        $users = $objSQL->fetchAll( $query, 'id' );
            if( !$users ){
                msgDie('INFO', 'Cant query users :/');
                return false;
            }

        foreach( $users as $id => $user ){

            //$role = 






            $objTPL->assign_block_vars('user', array(
                'ID'              => $id,
                'NAME'            => $user['username'],
                'EMAIL'           => $user['email'],
                'DATE_REGISTERED' => $objTime->mk_time($user['register_date']),
                'STATUS'          => ( $user['active'] == '1' ? 'Active' : 'Disabled' ),
                'STATUS_LABEL'    => ( $user['active'] == '1' ? 'success' : 'error' ),

            ));

        }

        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   => 'User Management',
                'CONTENT' => $objTPL->get_html('panel', false),
                'ICON'    => 'fa-icon-user',
            ),
            'custom' => array(
                'ICON' => 'icon-save',
                'URL'   => '#',
                'TITLE' => 'Save the menu structure',
                'LINK'  => '',
                'CLASS' => '',
            ),
        ));
    }

    public function add() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();

        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Add User' )
        ));

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/users/add.tpl',
        ));

        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   =>  'Add User',
                'CONTENT' =>  $objTPL->get_html('panel', false),
                'ICON'    =>  'faicon-user',
            ),
        ));
    }


}


?>