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
class Admin_Modules_core_userpermissions extends Admin_Modules_core{

    public function __construct(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/users/', 'name' => 'User Management' )
        ));
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/userpermissions/', 'name' => 'User Permissions' )
        ));

    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author
     *
     */
    public function userpermissions() {

        $objPerms = Core_Classes_coreObj::getPermissions();




    }

}


?>