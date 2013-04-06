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
class Admin_Modules_core_users extends Admin_Modules_core{

    /**
     * List current set of users
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * 
     * @return  void
     */
    public function manage(){
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();
        $objUser    = Core_Classes_coreObj::getUser();

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

            switch( $user['userlevel'] ){
                case ADMIN:
                    $role = 'Administrator';
                break;

                case MOD:
                    $role = 'Moderator';
                break;

                case USER:
                    $role = 'User';
                break;
            }

            $objTPL->assign_block_vars('user', array(
                'ID'              => $id,
                'NAME'            => $objUser->makeUsername($id),
                'EMAIL'           => $user['email'],
                'DATE_REGISTERED' => $objTime->mk_time($user['register_date']),

                'ROLE'            => $role,

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
            'custom_html' => array(
                'HTML' => Core_Classes_coreObj::getForm()->inputBox('search_user', 'text', '', array(
                    'class'       => 'input-mini',
                    'placeholder' => 'Search..',
                )),
            ),
        ));
    }

    /**
     * Add a new user to the system
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * 
     * @return  void
     */
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