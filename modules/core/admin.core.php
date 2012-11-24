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

        $blocks = array();

        for( $i = 0; $i < 10; $i++ ) {
            $blocks[randcode(10)] = array(
                'COL'     => rand(1,2) * 4,
            );
        }

        $this->displayPortlets( $blocks );


    }
    /**
     * Loops through the blocks and displays them nicely using the theme template
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   array  $blocks     Collection of blocks
     *
     */
    public function displayPortlets( $blocks ) {
        $objTPL = coreObj::getTPL();

        $objTPL->set_filenames(array(
            'block_notices' => cmsROOT . Page::$THEME_ROOT . 'block.tpl'
        ));

        $rowCount = 12;
        foreach( $blocks as $title => $block ){

            $block['COL'] = (int) doArgs( 'COL', 12, $block );

            $objTPL->assign_block_vars('block', array(
                'TITLE'   => $title,
                'CONTENT' => dump( $rowCount, 'RowCount' ) . dump( $block, 'block' ),
                'ICON'    => doArgs('ICON', null, $block),
            ));

            // If there are no blocks in the row, Start new row
            if( $rowCount === 12 ) {
                $objTPL->assign_block_vars('block.start_row');

            // If there is no space for the current block, end the current div above everything, and start a new one
            } else if( $rowCount - $block['COL'] < 0 ) {
                $objTPL->assign_block_vars('block.start_row');
                $objTPL->assign_block_vars('block.pre_end_row');
            }

            // If, after everything, we are at 0, end the current block, and reset the row count
            $rowCount -= $block['COL'];
            if( $rowCount <= 0 ) {
                $objTPL->assign_block_vars('block.end_row');
                $rowCount = 12;
            }

            $objTPL->assign_block_vars('block.'.(doArgs('COL', '12', $block)/4).'col', array());
            $objTPL->assign_vars(array(
                'BLOCKS' => $objTPL->get_html('block_notices')
            ));
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