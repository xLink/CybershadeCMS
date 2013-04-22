<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Core_Classes_Permissions extends Core_Classes_coreObj {

    private $uid = 0;
    private $groups = array();
    public $permissions = array();

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

        $this->uid    = $objUser->grab('id');
        $this->groups = $objGroups->userInGroups($this->uid);

        $this->buildACL();
    }


    /**
     * Check if we have the permission on the user.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function can( $permission ){

        $permission = strtolower($permission);

        if( isset($this->permissions[$permission]) ){

            if( $this->permissions[$permission]['value'] ){
                return true;
            }
        }

        return false;
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

        // if we have gorups, do their permissions first
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
            ->select('gp.id', 'gp.permission_key', 'gp.permission_value', 'gp.module', 'gp.content_id', 'gp.group_id', 'g.order')
            ->from(array( 'gp' => '#__groups_perms' ))
                ->leftJoin(array( 'g' => '#__groups' ))
                    ->on('gp.group_id', '=', 'g.id')
            ->where(sprintf( 'group_id IN %s', implode('","', array_keys($groups)) ))
            ->orderBy('g.order', 'DESC');

        $permissions = $objSQL->fetchAll( $query->build() );
            if( $permissions === false || is_empty($permissions) ){
                return array();
            }

        echo dump($permissions, 'group perms');
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
    public function getUserPerms(){

        $objSQL = Core_Classes_coreObj::getDBO();

        // do the query to grab the permissions
        $query = $objSQL->queryBuilder()
            ->select('id', 'permission_key', 'permission_value', 'module', 'content_id', 'user_id')
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

            $return[ $permKey ] = array(
                'permission' => $permKey,
                'inherited'  => (isset($p['group_id']) ? true : false),
                'value'      => ($p['permission_value'] == '1' ? true : false),
                'setWhere'   => (isset($p['group_id']) ? 'group' : 'user'),
            );
        }

        return $return;
    }

}

?>