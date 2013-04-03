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
            $objTPL->assign_block_vars('user', array(
                'ID'              => $id,
                'NAME'            => $user['username'],
                'DATE_REGISTERED' => $objTime->mk_time($user['register_date']),
                'STATUS'          => ( $user['active'] == '1' ? 'Active' : 'Disabled' )
            ));

            $objTPL->assign_block_vars('user.actions.edit', array(
                'URL'   => '',
                'ICON'  => '',
            ));

            $objTPL->assign_block_vars('user.actions.activate', array(
                'URL'   => '',
                'ICON'  => '',
            ));

            $objTPL->assign_block_vars('user.actions.disable', array(
                'URL'   => '',
                'ICON'  => '',
            ));
        }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'User Management',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);
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

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Add User',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);
    }



}


?>