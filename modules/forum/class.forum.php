<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class forum extends Module{

    public function __construct(){
        $objPage = coreobj::getPage();
        $objPage->setMenu('forum');
        $objPage->addJSFile('/'.root().'modules/forum/scripts/forum.js');
        $objPage->addCSSFile('/'.root().'modules/forum/styles/forum.css');

echo dump($_GET);
        // echo dump($objPage->jsFiles);
        /*








        */
    }

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
    public function viewThread( $id, $_all='' ) {
        $objTPL = coreObj::getTPL();

        $objTPL->assign_var('VARIABLE', dump($id));
echo dump(coreObj::getDBO()->getVar('debug'));
        $this->setView('view_1');
    }
}



?>