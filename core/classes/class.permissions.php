<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Permissions extends coreObj {

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
     * @return  mixed    True/False on successful check, -1 on unknown group
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
}

?>