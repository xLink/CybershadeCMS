<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class forum extends Module{

    /**
     * Displays a forum thread
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       $id  int   ID of the forum thread
     *
     * @return      void
     */
    public function viewThread($id, $name, $_url){
    	$args = func_get_args();
    	$method = __METHOD__;
        echo dump($args, 'Called '.$method);
    }


}

?>