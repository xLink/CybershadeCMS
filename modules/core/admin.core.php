<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Admin_core extends Module{

    public function __construct(){

    }

/**
  //
  //-- Dashboard Section
  //
**/
    public function dashboard(){
        $objTPL = coreObj::getTPL();
        $this->setView('admin/dashboard/default.tpl');


        $objTPL->set_filenames(array(
            'block_notices' => cmsROOT . Page::$THEME_ROOT . 'block.tpl'
        ));


        $blocks = array();

        $blocks['Notices'] = array(
            'CONTENT' => '',
            'ICON'    => 'home',
            'COL'     => '4',
        );

        $blocks['Test Block 1'] = array(
            'CONTENT' => '',
            'ICON'    => 'home',
            'COL'     => '4',
        );

        $blocks['Test Block 2'] = array(
            'CONTENT' => '',
            'ICON'    => 'home',
            'COL'     => '4',
        );

        $blocks['Test Block 3'] = array(
            'CONTENT' => '',
            'ICON'    => 'home',
            'COL'     => '12',
        );

        foreach( $blocks as $title => $block ){
            $objTPL->assign_block_vars('block', array(
                'TITLE'   => $title,
                'CONTENT' => doArgs('CONTENT', null, $block),
                'ICON'    => doArgs('ICON', null, $block),
            ));
            $objTPL->assign_block_vars('block.'.(doArgs('COL', '12', $block)/4).'col', array());
            $objTPL->assign_vars(array(
                'BLOCKS' => $objTPL->get_html('block_notices')
            ));
            //$objTPL->reset_block_vars('block');
        }


    }


/**
  //
  //-- Site Configuration
  //
**/
    public function siteConfiguration(){
        echo __METHOD__;
    }

/**
  //
  //-- User Admin Section
  //
**/
    public function rawr(){
        echo __METHOD__;
    }

    //public function index() {}

}
?>