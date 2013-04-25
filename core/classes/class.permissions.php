<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Core_Classes_Permissions extends Core_Classes_coreObj {

    public  $uid            = 0;
    public  $groups         = array();
    public  $permissions    = array();
    private $permissionList = array();

    /**
     * Start grabbing the information we need to test for permissions
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     *
     */
    public function __construct( $name, $options=array() ) {
        $objUser      = Core_Classes_coreObj::getUser();
        $objGroups    = Core_Classes_coreObj::getGroups();

        $this->uid    = doArgs('uid', $objUser->grab('id'), $options);
        $this->groups = $objGroups->userInGroups($this->uid);

        $this->buildACL();
    }

/**
  //
  //-- Utility Methods
  //
**/

    /**
     * Get a list of groups this user belongs to.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getUserGroups() {
        return $this->groups;
    }

    /**
     * Get a flattened list of permissions this user has.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getFlatPermissions() {

        if( !count($this->permissions) ){
            return array();
        }

        $return = array();
        // loop through each permission in the set
        foreach($this->permissions as $perm => $values){

            // loop through each version of the permission the groups have
            foreach($values as $k => $v){
                $return[ $perm ][ $v['content_id'] ] = $v['value'];
            }

        }

        return $return;
    }

    /**
     * Get an array with the current set of permissions in.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getKeys(){
        return array_keys($this->permissions);
    }

    /**
     * Get a permission node based on its $key and $content_id
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getNode( $key, $content_id=0 ){
        $key = strtolower($key);
        return ( isset($this->permissions[ $key ][ $content_id ]) ? $this->permissions[ $key ][ $content_id ] : $this->permissions[ $key ][ 0 ] );
    }

    /**
     * Returns a list of all the permissions in the system.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getAll(){

        if( is_empty($this->permissionList) ){
            $objSQL = Core_Classes_coreObj::getDBO();

            $query = $objSQL->queryBuilder()
                ->select('key', 'name', 'description')
                ->from('#__permissions');

            $permissions = $objSQL->fetchAll( $query->build() );
                if( $permissions === false || is_empty($permissions) ){
                    return array();
                }

            $perms = array();
            foreach($permissions as $perm){
                $perms[ $perm['key'] ] = $perm;
            }

            $this->permissionList = $perms;
        }

        return $this->permissionList;
    }


/**
  //
  //-- Core Class Methods
  //
**/

    /**
     * Check if we have the permission on the user.
     *
     * @version 1.2
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function can( $permission, $content_id=0 ){

        $permission = strtolower($permission);

        // make sure the permission is valid
        if( isset($this->permissions[$permission]) ){

            // if we have the content id they want, return based on its value
            if( isset($this->permissions[$permission][ $content_id ]['value']) ){
                return ($this->permissions[$permission][ $content_id ]['value']==1 ? 1 : 0);
            }

            // if not, try for content id = 0, this is a global version
            if( isset($this->permissions[$permission][ '0' ]['value']) ){
                return ($this->permissions[$permission][ '0' ]['value']==1 ? 1 : 0);
            }
        }

        // if we got this far, permission doesn't exist so..
        return -1;
    }

    /**
     * Build the permissions array
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    private function buildACL(){

        // if we have groups, do their permissions first
        if( count($this->groups) > 0 ){
            $this->permissions = array_merge( $this->permissions, $this->getGroupPerms($this->groups) );
        }

        // then do the users
        $this->permissions = array_merge( $this->permissions, $this->getUserPerms($this->groups) );

    }

    /**
     * Get a list of group permissions the user has inherited
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function getGroupPerms( $groups ){

        $objSQL = Core_Classes_coreObj::getDBO();

        // we can pass multiple groups through this func, if we just have a string, then arrayify it
        if( !is_array($groups) ){
            $groups = array( $groups );
        }

        // do the query to grab the permissions
        $query = $objSQL->queryBuilder()
            ->select('gp.permission_key', 'gp.permission_value', 'gp.content_id', 'gp.group_id', 'g.order')
            ->from(array( 'gp' => '#__groups_perms' ))
                ->leftJoin(array( 'g' => '#__groups' ))
                    ->on('gp.group_id', '=', 'g.id')
            ->where(sprintf( 'group_id IN %s', implode('","', array_keys($groups)) ))
            ->orderBy('g.order', 'DESC');

        $permissions = $objSQL->fetchAll( $query->build() );
            if( $permissions === false || is_empty($permissions) ){
                return array();
            }

        return $this->figurePerms($permissions);
    }

    /**
     * Get a list of user permissions that has been assigned
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    private function getUserPerms(){

        $objSQL = Core_Classes_coreObj::getDBO();

        // do the query to grab the permissions
        $query = $objSQL->queryBuilder()
            ->select('permission_key', 'permission_value', 'content_id', 'user_id')
            ->from('#__users_perms')
            ->where(sprintf( 'user_id = %s', $this->uid ));

        $permissions = $objSQL->fetchAll( $query->build() );
            if( $permissions === false || is_empty($permissions) ){
                return array();
            }

        return $this->figurePerms($permissions);
    }

    /**
     * Flatten the permissions into something usable.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    private function figurePerms( $permissions ){

        if( !is_array($permissions) || is_empty($permissions) ){
            trigger_error('$permissions was empty');
            return array();
        }

        $return = array();
        foreach($permissions as $p){

            $permKey = strtolower( $p['permission_key'] );
                if( is_empty($permKey) ){ continue; }

            $return[ $permKey ][ $p['content_id'] ] = array(
                'permission' => $permKey,
                'name'       => $g['name'],
                'inherited'  => (isset($p['group_id']) ? true : false),
                'value'      => ($p['permission_value'] == '1' ? true : false),
                'content_id' => $p['content_id'],
            );
        }

        return $return;
    }

}

?>