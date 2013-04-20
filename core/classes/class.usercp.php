<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_UserCP extends Core_Classes_ControlPanel{

    /**
     * Override the orig, add the content wrapper in there so we have a vertical menu
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function output(){
        $objTPL = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'ucpBody' =>  cmsROOT . Core_Classes_Page::$THEME_ROOT . 'user_panel.tpl',
        ));

            $content = parent::output();
            $objTPL->assign_vars(array(
                'CONTENT_BODY' => $content,
            ));

        $objTPL->parse('ucpBody', false);
        return $objTPL->get_html('ucpBody');
    }

}

?>