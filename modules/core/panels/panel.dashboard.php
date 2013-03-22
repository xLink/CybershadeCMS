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
 * @author  Daniel Noel-Davies
 */
class Admin_Modules_core_dashboard extends Admin_Modules_core{

    public function dashboard(){
        $objTPL = Core_Classes_coreObj::getTPL();
        $objTPL->set_filenames(array(
            'body' => cmsROOT . 'modules/core/views/admin/dashboard/default.tpl'
        ));

        $blocks = array();

        // for( $i = 0; $i < 10; $i++ ) {
        //     $blocks[randcode(10)] = array(
        //         'COL'     => rand(1,3) * 4,
        //     );
        // }

        $blocks[randcode(10)] = array( 'COL' => 3 *4 );

        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );


        $blocks[randcode(10)] = array( 'COL' => 2 *4 );
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
  
  
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
        $blocks[randcode(10)] = array( 'COL' => 2 *4 );

        $this->displayPortlets( $blocks );

        $objTPL->parse('body', false);
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
    private function displayPortlets( $blocks ) {
        $objTPL = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'block_notices' => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl'
        ));

        $rowCount = 12;
        foreach( $blocks as $title => $block ){

            $block['COL'] = (int) doArgs( 'COL', 12, $block );

            $objTPL->assign_block_vars('block', array(
                'TITLE'   => $title,
                'CONTENT' => dump( $rowCount, 'RowCount' ) . dump( $block, 'block' ),
                'ICON'    => 'icon-'.doArgs('ICON', null, $block),
            ));

            // If there are no blocks in the row, Start new row
            if( $rowCount === 12 ) {
                $objTPL->assign_block_vars('block.start_row', array());

            // If there is no space for the current block, end the current div above everything, and start a new one
            } else if( $rowCount - $block['COL'] < 0 ) {
                $objTPL->assign_block_vars('block.start_row', array());
                $objTPL->assign_block_vars('block.pre_end_row', array());
            }

            // If, after everything, we are at 0, end the current block, and reset the row count
            $rowCount -= $block['COL'];
            if( $rowCount <= 0 ) {
                $objTPL->assign_block_vars('block.end_row', array());
                $rowCount = 12;
            }

            $objTPL->assign_block_vars('block.'.(doArgs('COL', '12', $block)/4).'col', array());
            $objTPL->assign_vars(array(
                'BLOCKS' => $objTPL->get_html('block_notices')
            ));
        }
    }

}


?>