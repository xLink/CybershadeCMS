<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){die('Error: Cannot access directly.');}

/**
 * Group Class designed to allow easier access to expand on the group system implemented
 *
 * @version     1.3
 * @since       1.0.0
 * @author      Dan Aldridge, Richard Clifford (Ported to CS v1.0)
 */
class Core_Classes_Groups extends Core_Classes_coreObj {

    /**
     * Init's the groups
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    public function __construct(){
        $this->getGroups();
    }

    /**
     * Returns information on a group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int $gid     Group ID
     *
     * @return  array
     */
    public function getGroup($gid){
        // check to make sure the args are right
        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        return (isset($this->group[$gid]) ? $this->group[$gid] : false);
    }

    /**
     * Gets all the groups in the system & cache em for the rest of the system
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  array
     */
    public function getGroups(){
        $objSQL = Core_Classes_coreObj::getDBO();

        $query = $objSQL->queryBuilder()
            ->select('id', 'name', 'moderator')
            ->from('#__groups')
            ->build();

        $groups = $objSQL->fetchAll( $query );
            if( $groups === false ){
                trigger_error('Cannot query groups');
                return false;
            }

        foreach($groups as $id => $g){
            $this->group[$id] = $g;
        }

        return $groups;
    }

    /**
     * Joins a user to a specific group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * @todo    Needs testing under new system
     *
     * @param   int $uid         User's ID
     * @param   int $gid         Group ID
     * @param   int $pending     Whether the user will be accessible to the group
     *
     * @return  bool
     */
    public function joinGroup($uid, $gid, $pending=1){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        if( !is_number($pending) ){
            trigger_error('$pending is not valid');
            return false;
        }

        // test to see if $uid is already in said group, moderator of the group is added as a subscriber anyway
        if( $this->isInGroup($uid, $gid, 0) || $this->isInGroup($uid, $gid, 1) ){
            trigger_error('User is already in group'); return false;
        }

        // add if needed
        unset($insert);
        $insert['group_id'] = $gid;
        $insert['user_id']  = $uid;
        $insert['pending']  = $pending;

        $objPlugins = Core_Classes_coreObj::getPlugins();
        $objSQL     = Core_Classes_coreObj::getDBO();

        $insertQuery = $objSQL->queryBuilder()
            ->insertInto('#__groups_subs')
            ->set($insert)
            ->build();

        $result = $objSQL->query( $insertQuery );
            if( $result === false ){
                trigger_error('Failed to add user to group: '.$objSQL->getError());
                return false;
            }

        $args = array( func_get_args() );
        $objPlugins->hook('CMS_GROUPS_JOIN', $args);

        unset($insert);

        return true;
    }

    /**
     * Removes a user from a group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * @todo    Needs testing under new system
     *
     * @param   int $uid     User's ID
     * @param   int $gid     Group ID
     *
     * @return  bool
     */
    public function leaveGroup($uid, $gid){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // remove the user from the group
        $delete = $objSQL->queryBuilder()
            ->deleteFrom('#__groups_subs')
            ->where('user_id', '=', $uid)
                ->andWhere('group_id', '=', $gid)
            ->build();

        $objSQL->query( $delete );
            if( $delete === false ){
                trigger_error('Failed to remove user from group: '.$objSQL->getError());
                return false;
            }else{        
                $objUser = Core_Classes_coreObj::getUsers();
                $log = 'User Groups: Removed '.$objUser->profile($uid, RAW).' from '.$gid;
            }

        $this->objPlugins->hook('CMS_GROUPS_JOIN', func_get_args());

        return true;
    }

    /**
     * Assign a user Moderator status over a group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * @todo    Needs testing under new system
     *
     * @param   int $uid     User's ID
     * @param   int $gid     Group ID
     *
     * @return  bool
     */
    public function makeModerator($uid, $gid){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        $group = $this->getGroup($gid);

        $objSQL  = Core_Classes_coreObj::getDBO();
        $objUser = Core_Classes_coreObj::getUser();

        // make sure old moderator is a subscriber
        $oldModQuery = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__groups_subs')
            ->where('user_id', '=', $group['moderator'])
                ->andWhere('group_id', '=', $gid)
            ->limit(1)
            ->build();

        $oldModerator = $objSQL->fetchLine($oldModQuery);
            if( is_empty($oldModerator) ){
                $this->joinGroup($group['moderator'], $gid, 0);
            }

        // make $uid new moderator
        if( $group['moderator'] != $uid ){
            unset($update);
            $update['moderator'] = $uid;

            $update = $objSQL->queryBuilder()
                ->update('#__groups_subs')
                ->set($update)
                ->where( sprintf('id = "%s"', $gid) )
                ->build();

            $objSQL->query( $update );

            $log = 'User Groups: '.$objUser->makeUsername($uid, RAW).' has been made group Moderator of '.$group['name'];

            $args = array($uid, $gid);
            Core_Classes_coreObj::getPlugins()->hook('CMS_GROUPS_CHANGE_MODERATOR', $args);
        }

        // make the moderator a subscriber too
        $this->joinGroup($uid, $gid, 0);

        return true;
    }

    /**
     * Toggles the pending status of a user in a group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int $uid     User's ID
     * @param   int $gid     Group ID
     *
     * @return  bool
     */
    public function togglePending($uid, $gid){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // get group
        $group = $this->getGroup($gid);
            if( $group === false ){
                trigger_error('Could not obtain group information');
                return false;
            }

        // update the pending status
        unset($update);
        $update['pending'] = 'IF(pending=1, 0, 1)';

        $updateQuery = $objSQL->queryBuilder()
            ->update('#__groups_subs')
            ->set( $update )
            ->where('user_id', '=', $uid)
                ->andWhere('group_id', '=', $gid)
            ->build();

        $updateResult = $objSQL->query( $updateQuery );
            if( $updateResult === false ){
                trigger_error('Updating pending status failed');
                return false;
            }

        return true;
    }

    /**
     * Determine whether user is in a group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int $uid         User's ID
     * @param   int $gid         Group ID
     *
     * @return  bool
     */
    public function isInGroup($uid, $gid){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // get group
        $query = $objSQL->queryBuilder()
            ->select('ug.user_id', 'g.status', 'g.moderator')
            ->from( array('g' => '#__groups') )
                ->leftJoin( array('ug' => '#__groups_subs') )
                    ->on('ug.group_id', '=', 'g.id')
            ->where( sprintf('g.id = %d', $gid) )
                ->andWhere( sprintf('g.status != %d', GROUP_HIDDEN) )
            ->build();


        $result = $objSQL->fetchAll($query);
            if( is_empty($result) ){
                trigger_error('No group for ID: '.$gid);
                return false;
            }

        // test to see if user is in group and return accordingly
        foreach($result as $row){
            if( $uid == $row['user_id'] ){
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an array of user id in said group according to whether they are $pending
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int $uid         User's ID
     * @param   int $pending
     *
     * @return  array
     */
    public function usersInGroup($gid, $pending=0){
        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        if( !is_number($pending) ){
            trigger_error('$pending is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();
        $objUser = Core_Classes_coreObj::getUser();

        // Get Group
        $query = $objSQL->queryBuilder()
            ->select('ug.user_id', 'ug.pending')
            ->from( array('g' => '#__groups') )
                ->leftJoin( array('ug' => '#__groups_subs') )
                    ->on('ug.group_id', '=', 'g.id')
            ->where( sprintf('g.id = %d', $gid) )
                ->andWhere('ug.pending', '=', $pending)
            ->build();

        $result = $objSQL->fetchAll( $query );

        if( is_empty($result) ){
            trigger_error('No group for ID: '.$gid);
            return false;
        }

        // create an array of uid's in group according to $pending
        $users = array();
        foreach($result as $row){
            $users[ $row['user_id'] ] = $objUser->makeUsername($row['user_id'], RAW);
        }

        return $users;
    }

    /**
     * 
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     */
    public function userInGroups( $uid ){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // Get Group
        $query = $objSQL->queryBuilder()
            ->select(array('group_id' => 'g.id', 'g.name'))
            ->from( array('g' => '#__groups') )
                ->leftJoin( array('ug' => '#__groups_subs') )
                    ->on('ug.group_id', '=', 'g.id')
            ->where( sprintf('ug.user_id = %d', $uid) )
                ->andWhere( 'ug.pending', '=', '0' )
            ->build();

        $result = $objSQL->fetchAll( $query );
            if( is_empty($result) ){
                trigger_error('User is in no groups.');
                return false;
            }

        // create an array of uid's in group according to $pending
        $groups = array();
        foreach($result as $row){
            $groups[ $row['group_id'] ] = $row['name'];
        }

        return $groups;
    }
}

?>