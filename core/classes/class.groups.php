<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){die('Error: Cannot access directly.');}

/**
 * Group Class designed to allow easier access to expand on the group system implemented
 *
 * @version     1.2
 * @since       1.0.0
 * @author      Dan Aldridge, Richard Clifford (Ported to CS v1.0)
 */
class Core_Classes_Groups extends Core_Classes_coreObj {

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

        // if this particular one is cached already we shall just return it
        if( isset($this->group[$gid]) ){
            return $this->group[$gid];
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        $group_id = $objSQL->queryBuilder()
            ->select('id', 'name', 'moderator', 'single_user_group')
            ->from('#__groups')
            ->where('id', '=', $gid)
            ->limit(1)
            ->build();


        $this->group[$gid] = $objSQL->fetchLine( $group_id );

        if( is_empty($this->group[$gid]) ){
            trigger_error('Cannot query group');
            return false;
        }

        return $this->group[$gid];
    }


    /**
     * Joins a user to a specific group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int $uid         User's ID
     * @param   int $gid         Group ID
     * @param   int $pending     Whether the user will be accessable to the group
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
        $insert['gid']      = $gid;
        $insert['uid']      = $uid;
        $insert['pending']  = $pending;


        $objPlugins = Core_Classes_coreObj::getPlugins();
        $objSQL     = Core_Classes_coreObj:: getDBO();

        $objPlugins->hook('CMSGroups_beforeJoin', $insert);

        $insertQuery = $objSQL->queryBuilder()
            ->insertInto('#__group_subs')
            ->set($insert)
            ->build();

        $result = $objSQL->query( $insertQuery );

        if( !$result ){
            trigger_error('Failed to add user to group: '.$objSQL->getError());
            return false;
        }

        $args = func_get_args();
        $objPlugins->hook('CMSGroups_afterJoin', $args);

        unset($insert);

        return true;
    }

    /**
     * Removes a user from a group
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
    function leaveGroup($uid, $gid){
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
            ->deleteFrom('#__group_subs')
            ->where( sprintf('uid = "%s" AND gid = "%s"', $uid, $gid) )
            ->build();

        if( $delete ){
            $objUser = Core_Classes_coreObj::getUsers();
            $log = 'User Groups: Removed '.$objUser->profile($uid, RAW).' from '.$gid;
        }


        if( !$delete ){
            trigger_error('Failed to remove user from group: '.$objSQL->getError());
            return false;
        }

        $this->objPlugins->hook('CMSGroups_leave', func_get_args());

        return true;
    }

    /**
     * Assign a user Moderator status over a group
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
    function makeModerator($uid, $gid){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        $group = $this->getGroup($gid);

        // test to make sure group isnt a single user group
        if($group['single_user_group']){
            trigger_error('Group is user specific, Cannot reassign Moderator');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();
        $objUser = Core_Classes_coreObj::getUser();

        // make sure old moderator is a subscriber
        $oldModQuery = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__group_subs')
            ->where( sprintf('gid = "%s" AND uid = "%s"', $gid, $group['moderator']) )
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
                ->update('#__group_subs')
                ->set($update)
                ->where( sprintf('id = "%s"', $gid) )
                ->build();

            $log = 'User Groups: '.$objUser->profile($uid, RAW).' has been made group Moderator of '.$group['name'];

            Core_Classes_coreObj::getPlugins()->hook('CMSGroups_changeModerator', array($uid, $gid));
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
    function togglePending($uid, $gid){
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

        // grab the necesary row
        $subRowQuery = $objSQL->queryBuilder()
            ->select('uid', 'gid', 'pending')
            ->from('#__group_subs')
            ->where( sprintf('gid = "%s" AND uid = %d LIMIT 1', $gid, (int)$uid) )
            ->limit(1)
            ->build();

        $subRow = $objSQL->fetchLine( $subRowQuery );

        if( is_empty($subRow) ){
            trigger_error('User is not in group');
            return false;
        }

        // update the pending status
        unset($update);
        $update['pending'] = !$subRow['pending'];

        $updateQuery = $objSQL->queryBuilder()
            ->update('#__groups_subs')
            ->set( $update )
            ->where( sprintf('gid = "%s" AND uid = "%s"', $gid, $uid ))
            ->build();

        $updateResult = $objSQL->query( $updateQuery );

        if( !$updateResult ){
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
     * @param   array $query     Group Query
     *
     * @return  bool
     */
    function isInGroup($uid, $gid, $query=null){
        if( !is_number($uid) ){
            trigger_error('$uid is not valid');
            return false;
        }

        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        if( !is_array($query) && !is_empty($query) ){
            trigger_error('$query is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // get group
        if(is_empty($query)){

            /*$query = $objSQL->query(vsprintf('SELECT ug.uid, g.type, g.moderator
                                                FROM `#__groups` g, `#__group_subs` ug
                                                WHERE g.id = "%s"
                                                    AND g.type != "%s"
                                                    AND ug.gid = g.id',
                                                array( $gid, GROUP_HIDDEN)));*/
            $query = $objSQL->queryBuilder()
                ->select('ug.uid', 'g.type', 'g.moderator')
                ->from( array('g' => '#__groups') )
                    ->leftJoin( array('ug' => '#__group_subs') )
                        ->on('ug.gid', '=', 'g.id')
                ->where( sprintf('g.id = %d', $gid) )
                    ->andWhere( sprintf('g.type = %d', GROUP_HIDDEN) )
                ->build();


            $result = $objSQL->fetchAll($query);

            if( is_empty($result) ){
                trigger_error('No group for ID: '.$gid);
                return false;
            }
        }

        // test to see if user is in group and return accordingly
        foreach($result as $row){
            if( $uid == $row['uid'] ){
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
    function usersInGroup($gid, $pending=0){
        if( !is_number($gid) ){
            trigger_error('$gid is not valid');
            return false;
        }

        if( !is_number($pending) ){
            trigger_error('$pending is not valid');
            return false;
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        // Get Group
        /*$query = $objSQL->query(vsprintf('SELECT ug.uid, ug.pending, g.type, g.moderator
                                            FROM `#__groups` g, `#__group_subs` ug
                                            WHERE g.id = "%s"
                                                AND ug.gid = g.id',
                                        array($gid)));*/
        $query = $objSQL->queryBuilder()
            ->select('ug.uid', 'ug.pending', 'g.type', 'g.moderator')
            ->from( array('g' => '#__groups') )
                ->leftJoin( array('ug' => '#__group_subs') )
                    ->on('ug.gid', '=', 'g.id')
            ->where( sprintf('g.id = %d', $gid) )
            ->build();

        $result = $objSQL->fetchAll( $query );

        if( is_empty($result) ){
            trigger_error('No group for ID: '.$gid);
            return false;
        }

        // create an array of uid's in group according to $pending
        $users = array();
        foreach($result as $row){
            if( $row['pending'] == $pending  ){
                $users[] = $row['uid'];
            }
        }

        return $users;
    }
}

?>