<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Permissions extends coreObj {

    static  $IS_ADMIN   = false,
            $IS_MOD     = false,
            $IS_USER    = false,
            $IS_SPECIAL = false; // For various, custom permissions

    /**
     * Defines global CMS permissions
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    public function initPerms(){

        
        // Causing infiniate loop - FIX
        $objUser = coreObj::getUser();
        
        self::$IS_USER      = $this->checkPermissions($objUser->get('id'), USER);
        self::$IS_MOD       = $this->checkPermissions($objUser->get('id'), MOD);
        self::$IS_ADMIN     = $this->checkPermissions($objUser->get('id'), ADMIN);
    }

    /**
     * Returns permission state for given user and group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int     $uid        UserID
     * @param   int     $group      GUEST, USER, MOD, or ADMIN
     *
     * @return  bool    True/False on successful check, -1 on unknown group
     */
    public function checkUserAuth( $type, $key, $u_access, $is_admin ){
        $auth_user = 0;

        if(count($u_access)){
            for($j = 0; $j < count($u_access); $j++){
                $result = 0;
                switch($type){
                    case AUTH_ACL:   $result = $u_access[$j][$key]; break;
                    case AUTH_MOD:   $result = $result || $u_access[$j]['auth_mod']; break;
                    case AUTH_ADMIN: $result = $result || $is_admin; break;
                }
                $auth_user = $auth_user || $result;
            }
        } else {
            $auth_user = $is_admin;
        }

        return $auth_user;
    }

    /**
     * Returns permission state for given user and group
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int     $uid        UserID
     * @param   int     $group      GUEST, USER, MOD, or ADMIN
     *
     * @return  bool    True/False on successful check, -1 on unknown group
     */
    public function checkPermissions( $uid, $group=0 ) {
        $group = (int)$group;

        //make sure we have a group to check against
        if(is_empty($group) || $group == 0 || $group == GUEST){
            return true;
        }

        //check to see whether we have a user id to check against..
        if(is_empty($uid)){
            return false;
        }

        //grab the user level if possible
        $userlevel = GUEST;

        // Get the user Object
        $objUser = coreObj::getUser();

        if(self::$IS_ONLINE){
            $userlevel = $objUser->getUserInfo($uid, 'userlevel');
        }

        //see which group we are checking for
        switch($group){
            case GUEST:
                if(!self::$IS_ONLINE){
                    return true;
                }
            break;

            case USER:
                if(self::$IS_ONLINE){
                    return true;
                }
            break;

            case MOD:
                if($userlevel == MOD){
                    return true;
                }
            break;

            case ADMIN:
                if($userlevel == ADMIN){
                    if(LOCALHOST || doArgs('adminAuth', false, $_SESSION['acp'])){
                        return true;
                    }
                }
            break;

            //no idea what they tried to check for, so we'll return something unexpected too
            default: return -1; break;
        }

        //if we are an admin then give them mod powers regardless
        if(($group == MOD || $group == USER) && $userlevel == ADMIN){
            return true;
        }

        //apparently the checks didnt return true, so we'll go for false
        return false;
    }

    public function assignPermission( $uid, $module, $permissions = array() ){

    }

    public function getAvailablePerms( $module = '' ){

    }

    public function hasPermission( $uid, $module, $type ){

    }

}


?>