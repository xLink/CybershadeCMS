<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Module_test_module extends Module{
    
    function __construct() {
        
        
    }


    public function viewIndex() {
        echo dump($_REQUEST);

        exit;
        $this->setView('../../core/views/admin/users/default/default.tpl');

        $objTPL = coreObj::getTPL();
        $objSQL = coreObj::getDBO();
        $objTime = coreObj::getTime();



            $query = $objSQL->queryBuilder()
                            ->select('*')
                            ->from('#__users')
                            ->build();

            $users = $objSQL->fetchAll( $query, 'id' );
            echo dump($users);
                if( !$users ){
                    msgDie('INFO', 'Cant query users :/');
                    return false;
                }

            foreach( $users as $id => $user ){
                $objTPL->assign_block_vars('user', array(
                    'id'          => $id,
                    'username'    => $user['username'],
                    'last_active' => $objTime->mk_time($user['last_active']),
                ));
            }



    }

}

?>