<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Core ACP Panel
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Dan Aldridge
 */
class Admin_Modules_core_systeminfo extends Admin_Modules_core{

    public function systeminfo() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();
        $objForm    = Core_Classes_coreObj::getForm();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));


        // checkers

        // grab some info about GD
        if(function_exists('gd_info')){
            $a = gd_info(); $gdVer = preg_replace('/[[:alpha:][:space:]()]+/', '', $a['GD Version']);
        }else{
            $gdVer = 'Not Installed.';
        }

        $info = '<div class="alert alert-info"><strong>Important!</strong> This panel needs more updating to output more useful data that has been made avaliable during the last overhaul</div>';
        $content = 'This panel gives the CMS dev team some information about your setup.

;--System Setup
    CMS Version: '.CMS_VERSION.'
    PHP Version: '.PHP_VERSION.' ('.(@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) == 'on' ?
                                            'Safe Mode Enabled' : 'Safe Mode Disabled').')
    MySQL Version: '.mysql_get_server_info().'

    GD Version: '.$gdVer.'

;--CMS Setup
    Install Path: /'.root().'

'.json_encode($objSQL->fetchAll('SELECT * FROM `#__config`')).'';

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'System Info',
            'CONTENT' => $info. $objForm->textarea('sysInfo', $content, array(
                'style' => 'width: 99%',
                'rows' => 20,
            )),
            'ICON'    => 'fa-icon-user',
        ));
        $objTPL->assign_block_vars('block.start_row', array());
        $objTPL->assign_block_vars('block.3col', array());          
        $objTPL->assign_block_vars('block.end_row', array());

        $objTPL->parse('body', false);
    }
}


?>