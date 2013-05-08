<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Admin_Modules_Permissions extends Core_Classes_Module{

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function __construct(){
        $objPage = Core_Classes_coreObj::getPage();

        $objPage->addCSSFile(array(
            'href'     => '/'.root().'modules/permissions/assets/styles/admin/permissions.css',
            'type'     => 'text/css',
        ));

        $objPage->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/permissions/', 'name' => 'Permissions' )
        ));
    }

/**
  //
  //-- User Perms
  //
**/
    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function user( ){
        $args = func_get_args();

        $uid = doArgs('0', false, $args);

        if( $uid === 'save' && HTTP_POST ){
            $this->userSave();
            $uid = doArgs('uid', 0, $_POST);
        }

        if( is_number($uid) === false ){
            Core_Classes_coreObj::getPage()->redirect( '/'.root().'admin/core/users/manage' );
            exit;
        }else{
            $this->userPerms($uid);
        }
    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function userPerms( $uid ){
        $objPerms  = Core_Classes_coreObj::getPermissions($uid);
        $objTPL    = Core_Classes_coreObj::getTPL();
        $objForm   = Core_Classes_coreObj::getForm();
        $objUser   = Core_Classes_coreObj::getUser();
        $objPage   = Core_Classes_coreObj::getPage();

        $objPage->addJSFile('/'.root().'modules/permissions/assets/javascript/admin/permissions.js');
        $objTPL->set_filenames(array(
            'tabs'  => cmsROOT. 'modules/permissions/views/admin/perms.tabs.tpl',
            'panel' => cmsROOT. 'modules/permissions/views/admin/perms.panel.tpl',
        ));

        $permissions = array(); $permissionsList = $objPerms->getAll();

        // reset the permissions to just user permissions
        $objPerms->permissions = $objPerms->getUserPerms($objPerms->groups);

        $objPage->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/permissions/user/', 'name' => 'User' ),
            array( 'url' => '/'.root().'admin/permissions/user/'.$group['id'], 'name' => $objUser->makeUsername($uid, RAW) ),
        ));

        // get a list of tabs we need
        $tabs = array();
        foreach($permissionsList as $permKey => $p){
            $permSegments = explode('.', $permKey);
            $tabs[ $permSegments[1] ][] = $p;
        }

        // run thru the tabs, and output the permissions for each tab
        $active = ' active';
        foreach($tabs as $tab => $perms){
            $objTPL->reset_block_vars('nodes');

            foreach($perms as $perm){
                $permKey = strtolower($perm['key']);

                if( !isset($objPerms->permissions[ $permKey ][0]) ){
                    $selected = 'x';
                }else{
                    if( $objPerms->permissions[ $permKey ][0]['value'] === true ){
                        $selected = '1';
                    }else{
                        $selected = '0';
                    }
                }

                $selectBox = array(
                    '1' => '<i class="'.((string)$selected === '1' ? 'icon-check' : 'icon-check-empty').'"></i> Allow',
                    '0' => '<i class="'.((string)$selected === '0' ? 'icon-check' : 'icon-check-empty').'"></i> Deny',
                    'x' => '<i class="'.((string)$selected === 'x' ? 'icon-check' : 'icon-check-empty').'"></i> Don\'t Set (Inherit)',
                );

                $objTPL->assign_block_vars('nodes', array(
                    'KEY'   => $permKey,
                    'NAME'  => $perm['name'],
                    'DESC'  => $perm['description'],
                ));

                $count = 0;
                foreach($selectBox as $v => $name){
                    $objTPL->assign_block_vars('nodes.values', array(
                        'VALUE_KEY'  => $v,
                        'VALUE_NAME' => $name,
                        'COUNT'      => $count++,
                        'SELECTED'   => ( (string)$selected === (string)$v ? 'active' : 'muted' ),
                    ));
                }
            }

            $objTPL->assign_block_vars('tabs', array(
                'ID'      => seo($tab),
                'NAME'    => $tab,
                'CONTENT' => $objTPL->get_html('panel', false),
                'ACTIVE'  => ( $active )
            )); $active = '';
        }

        // output the form for saving the permissions
        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));

        $objTPL->assign_vars(array(
            'FORM_START'  => $objForm->start('group_permissions', array('method'=>'POST', 'action'=>'/'.root().'admin/permissions/user/save/', 'class'=>'form-horizontal')),
            'FORM_END'    => $objForm->finish(),
            'FORM_TOKEN'  => $formToken . $objForm->inputbox('uid', 'hidden', $uid),

            'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
            'FORM_RESET'  => $objForm->button('reset', 'Reset'),
        ));

        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   => 'User Permissions for <strong>'.$objUser->makeUsername($uid, RAW).'</strong>',
                'CONTENT' => $objTPL->get_html('tabs', false),
                'ICON'    => 'icon-th-list',
            ),
        ));

    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function userSave(){
        if( !HTTP_POST ){ return false; }
        if( !isset($_POST['perm']) ){ return false; }
        if( !isset($_POST['uid']) ){ return false; }

        $objSQL = Core_Classes_coreObj::getDBO();

        foreach( $_POST['perm'] as $perm => $value ){
            switch( $value ){
                // update the database with the allow/deny keys
                case '1':
                case '0':
                    $query = $objSQL->queryBuilder()
                        ->replaceInto('#__users_perms')
                        ->set(array(
                            'permission_key'   => strtoupper($perm),
                            'permission_value' => $value,
                            'content_id'       => '0',
                            'user_id'          => $_POST['uid'],
                        ));

                    $results = $objSQL->query( $query->build() );
                break;

                // remove the permission from the table
                case 'x':
                    $query = $objSQL->queryBuilder()
                        ->deleteFrom('#__users_perms')
                        ->where( 'permission_key', '=', strtoupper($perm) )
                            ->andWhere( 'user_id', '=', $_POST['uid'] )
                            ->andWhere( 'content_id', '=', '0')
                        ->limit(1);

                    $results = $objSQL->query( $query->build() );
                break;
            }
        }
    }

/**
  //
  //-- Group Permissions
  //
**/

    /**
     * Group "Controller"
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function group(){
        $args = func_get_args();

        // GID, should either be a number or a string 'save'
        $gid = doArgs('0', false, $args);

        if( $gid === 'save' && HTTP_POST ){
            $this->groupSave();
            $gid = doArgs('gid', 0, $_POST);
        }

        if( is_number($gid) === false ){
            $this->groupList();
        }else{
            $this->groupPerms($gid);
        }
    }

    /**
     * List groups for the permissions tables
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function groupList() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'panel' => cmsROOT. 'modules/core/views/admin/menus/menu_list.tpl',
        ));


        // List the different types of menus
        $query = $objSQL->queryBuilder()
            ->select('id', 'name', 'order')
            ->from('#__groups')
            ->orderBy('`order`', 'DESC')
            ->build();

        $groups = $objSQL->fetchAll( $query, 'id' );

        foreach( $groups as $group ) {
            $objTPL->assign_block_vars( 'list', array(
                'URL'  => '/' . root() . 'admin/permissions/group/'.$group['id'],
                'NAME' => secureMe($group['name'])
            ));
        }

        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   => 'Group Selection',
                'CONTENT' => $objTPL->get_html('panel', false),
                'ICON'    => 'icon-th-list',
            ),
        ));
    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function groupPerms($gid){
        // init all the classes
        $objPerms  = Core_Classes_coreObj::getPermissions();
        $objTPL    = Core_Classes_coreObj::getTPL();
        $objForm   = Core_Classes_coreObj::getForm();
        $objUser   = Core_Classes_coreObj::getUser();
        $objGroups = Core_Classes_coreObj::getGroups();
        $objPage   = Core_Classes_coreObj::getPage();

        // sort the assets & breadcrumbs out
        $objPage->addJSFile('/'.root().'modules/permissions/assets/javascript/admin/permissions.js');
        $objTPL->set_filenames(array(
            'tabs'  => cmsROOT. 'modules/permissions/views/admin/perms.tabs.tpl',
            'panel' => cmsROOT. 'modules/permissions/views/admin/perms.panel.tpl',
        ));

        $objPage->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/permissions/group/', 'name' => 'Group' )
        ));

        // see if we can get the group we want
        $group = $objGroups->getGroup($gid);
            if( $group === false ){
                trigger_error('Nope...');
                return false;
            }

        $objPage->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/permissions/group/'.$group['id'], 'name' => $group['name'] )
        ));

        // grab a copy of all the permissions & figure out what this group has access to
        $permissions = array(); $permissionsList = $objPerms->getAll();
        $groupPerms = $objPerms->getGroupPerms( array($gid => $group['name']) );

        // get a list of tabs we need
        $tabs = array();
        foreach($permissionsList as $permKey => $p){
            $permSegments = explode('.', $permKey);
            $tabs[ $permSegments[1] ][] = $p;
        }

        // run thru the tabs, and output the permissions for each tab
        $active = ' active';
        foreach($tabs as $tab => $perms){
            $objTPL->reset_block_vars('nodes');
            foreach($perms as $perm){
                $permKey = strtolower($perm['key']);

                if( !isset($groupPerms[ $permKey ][0]) ){
                    $selected = 'x';
                }else{
                    if( $groupPerms[ $permKey ][0]['value'] === true ){
                        $selected = '1';
                    }else{
                        $selected = '0';
                    }
                }

                $selectBox = array(
                    '1' => '<i class="'.((string)$selected === '1' ? 'icon-check' : 'icon-check-empty').'"></i> Allow',
                    '0' => '<i class="'.((string)$selected === '0' ? 'icon-check' : 'icon-check-empty').'"></i> Deny',
                    'x' => '<i class="'.((string)$selected === 'x' ? 'icon-check' : 'icon-check-empty').'"></i> Don\'t Set (Inherit)',
                );

                $objTPL->assign_block_vars('nodes', array(
                    'KEY'   => $permKey,
                    'NAME'  => $perm['name'],
                    'DESC'  => $perm['description'],
                ));

                $count = 0;
                foreach($selectBox as $v => $name){
                    $objTPL->assign_block_vars('nodes.values', array(
                        'VALUE_KEY'  => $v,
                        'VALUE_NAME' => $name,
                        'COUNT'      => $count++,
                        'SELECTED'   => ( (string)$selected === (string)$v ? 'active' : 'muted' ),
                    ));
                }

            }

            $objTPL->assign_block_vars('tabs', array(
                'ID'      => seo($tab),
                'NAME'    => $tab,
                'CONTENT' => $objTPL->get_html('panel', false),
                'ACTIVE'  => ( $active )
            )); $active = '';
        }


        // output the form for saving the permissions
        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));

        $objTPL->assign_vars(array(
            'FORM_START'  => $objForm->start('group_permissions', array('method'=>'POST', 'action'=>'/'.root().'admin/permissions/group/save/', 'class'=>'form-horizontal')),
            'FORM_END'    => $objForm->finish(),
            'FORM_TOKEN'  => $formToken . $objForm->inputbox('gid', 'hidden', $gid),

            'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
            'FORM_RESET'  => $objForm->button('reset', 'Reset'),
        ));

        // and output all the things!
        $objTPL->parse('panel', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   => 'Global Group Permissions for '.$group['name'],
                'CONTENT' => $objTPL->get_html('tabs', false),
                'ICON'    => 'icon-th-list',
            ),
        ));
    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function groupSave(){
        if( !HTTP_POST ){ return false; }
        if( !isset($_POST['perm']) ){ return false; }
        if( !isset($_POST['gid']) ){ return false; }

        $objSQL = Core_Classes_coreObj::getDBO();

        foreach( $_POST['perm'] as $perm => $value ){
            switch( $value ){
                // update the database with the allow/deny keys
                case '1':
                case '0':
                    $query = $objSQL->queryBuilder()
                        ->replaceInto('#__groups_perms')
                        ->set(array(
                            'permission_key'   => strtoupper($perm),
                            'permission_value' => $value,
                            'content_id'       => '0',
                            'group_id'         => $_POST['gid'],
                        ));

                    $results = $objSQL->query( $query->build() );
                break;

                // remove the permission from the table
                case 'x':
                    $query = $objSQL->queryBuilder()
                        ->deleteFrom('#__groups_perms')
                        ->where( 'permission_key', '=', strtoupper($perm) )
                            ->andWhere( 'group_id', '=', $_POST['gid'] )
                            ->andWhere( 'content_id', '=', '0')
                        ->limit(1);

                    $results = $objSQL->query( $query->build() );
                break;
            }
        }
    }

/**
  //
  //-- [Group] Content ID Perms
  //
**/

    public function getContentPerms($content_id, $permissions){
        if( !is_number($content_id) || $content_id <= 0 ){
            return false;
        }

        $objPerms = Core_Classes_coreObj::getPermissions();
        $objTPL   = Core_Classes_coreObj::getTPL();
        $objPage  = Core_Classes_coreObj::getPage();
        $objForm  = Core_Classes_coreObj::getForm();
        $objSQL   = Core_Classes_coreObj::getDBO();

        if( HTTP_POST ){
            $this->saveContentPerms();
        }

        $objPage->addJSFile('/'.root().'modules/permissions/assets/javascript/admin/permissions.js');
        $objTPL->set_filenames(array(
            'panel'  => cmsROOT. 'modules/permissions/views/admin/perms.content.tpl',
        ));


        // List the different types of groups
        $query = $objSQL->queryBuilder()
            ->select('id', 'name', 'order')
            ->from('#__groups')
            ->orderBy('`order`', 'DESC')
            ->build();

        $groups = $objSQL->fetchAll( $query, 'id' );

        // grab a list of the perms we wanted
        $permSet = $objPerms->getAll();

        $perms = array();
        foreach($permSet as $key => $perm){
            // if we got an array, then we want to check if the perm is in there, else preg match on the keys to see if we got a match
            if( ( is_array($permissions) && in_array($key, $permissions) ) ||
                    ( !is_array($permissions) && preg_match('/'.$permissions.'/u', $key) ) ){

                $perms[$key] = $perm;
            }
        }

        // reset the blocks everytime this func is run, stops any bleed through
        $objTPL->reset_block_vars( 'columns' );
        $objTPL->reset_block_vars( 'row' );

        // set this one so we have an offset
        $objTPL->assign_block_vars( 'columns', array(
            'NAME' => '&nbsp;',
        ));


        $columns = 1; $groupPerms = array();
        // loop through the groups
        foreach( $groups as $group ) {
            $objTPL->assign_block_vars( 'columns', array(
                'NAME' => secureMe($group['name']),
            )); $columns++;
            $groupPerms[ seo($group['name']) ] = $objPerms->getGroupPerms( array($group['id'] => $group['name']) );
        }

        // loop though the permissions
        foreach($perms as $key => $perm){
            $objTPL->assign_block_vars( 'row', array(
                'KEY'   => $perm['key'],
                'NAME'  => secureMe($perm['name']),
            ));

            foreach($groups as $group){
                $objTPL->assign_block_vars( 'row.group', array(
                    'NAME' => strtolower($group['name']),
                ));
                $permKey = strtolower($perm['key']);

                if( !isset($groupPerms[ seo($group['name']) ][ $permKey ][ $content_id ]) ){
                    $selected = 'x';
                }else{
                    if( $groupPerms[ seo($group['name']) ][ $permKey ][ $content_id ]['value'] === true ){
                        $selected = '1';
                    }else{
                        $selected = '0';
                    }
                }

                $selectBox = array(
                    '1' => '<i class="' .
                                ((string)$selected === '1' ? 'icon-check' : 'icon-check-empty') .
                            '"></i> Allow',

                    '0' => '<i class="' .
                                ((string)$selected === '0' ? 'icon-check' : 'icon-check-empty') .
                            '"></i> Deny',

                    'x' => '<i class="' .
                                ((string)$selected === 'x' ? 'icon-check' : 'icon-check-empty') .
                            '"></i> Inherit ('.($groupPerms[ seo($group['name']) ][ $permKey ][0]['value'] ? 'Allow' : 'Deny').')',
                );


                $count = 0;
                foreach($selectBox as $v => $name){
                    $objTPL->assign_block_vars('row.group.values', array(
                        'VALUE_KEY'  => $v,
                        'VALUE_NAME' => $name,
                        'COUNT'      => $count++,
                        'SELECTED'   => ( (string)$selected === (string)$v ? 'active' : 'muted' ),
                    ));
                }
            }

        }

        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));
        $objTPL->assign_vars(array(
            'FORM_START'  => $objForm->start('content_permissions', array('method'=>'POST', 'action' => $this->config('global', 'fullPath'), 'class'=>'form-horizontal')),
            'FORM_END'    => $objForm->finish(),
            'FORM_HIDDEN'  => $formToken . $objForm->inputbox('cid', 'hidden', $content_id),

            'FORM_SUBMIT' => $objForm->button('submit', 'Save Permissions', array('class' => 'btn btn-info')),
            'FORM_RESET'  => $objForm->button('reset', 'Reset'),
        ));

        return $objTPL->get_html('panel', false);
    }

    public function saveContentPerms(){
        if( !HTTP_POST ){ return false; }
        if( !isset($_POST['cid']) ){ return false; }

        $objSQL     = Core_Classes_coreObj::getDBO();
        $objSession = Core_Classes_coreObj::getSession();

        if( $objSession->checkToken('form_token') === false ){
            //die(sprintf($msg, 'Error: Token Mismatch. Send form from correct location.', 'error'));
            return false;
        }

        // List the different types of groups
        $query = $objSQL->queryBuilder()
            ->select('id', 'name', 'order')
            ->from('#__groups')
            ->orderBy('`order`', 'DESC')
            ->build();

        $groups = $objSQL->fetchAll( $query, 'id' );
        $content_id = doArgs('cid', '0', $_POST);
        // loop though the groups see if we have any in the post
        foreach( $groups as $group ){
            if( !isset($_POST[ strtolower($group['name']) ]) ){
                continue;
            }

            // loop thru all the perms in this group, taking appropriate action
            // whilst i realize it may not be the best way, its the most solid way
            foreach( $_POST[ strtolower($group['name']) ] as $perm => $value){
                switch( $value ){
                    // update the database with the allow/deny keys
                    case '1':
                    case '0':
                        $query = $objSQL->queryBuilder()
                            ->replaceInto('#__groups_perms')
                            ->set(array(
                                'permission_key'   => strtoupper($perm),
                                'permission_value' => $value,
                                'content_id'       => $content_id,
                                'group_id'         => $group['id'],
                            ));

                        $results = $objSQL->query( $query->build() );
                    break;

                    // remove the permission from the table
                    case 'x':
                        $query = $objSQL->queryBuilder()
                            ->deleteFrom('#__groups_perms')
                            ->where( 'permission_key', '=', strtoupper($perm) )
                                ->andWhere( 'group_id', '=', $group['id'] )
                                ->andWhere( 'content_id', '=', $content_id)
                            ->limit(1);

                        $results = $objSQL->query( $query->build() );
                    break;
                }
            }
        }

        return true;
    }

}
?>