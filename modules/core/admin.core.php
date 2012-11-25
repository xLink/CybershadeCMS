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

        // for( $i = 0; $i < 10; $i++ ) {
        //     $blocks[randcode(10)] = array(
        //         'COL'     => rand(1,3) * 4,
        //     );
        // }

        $blocks[randcode(10)] = array( 'COL' => 3 *4);

        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );
        $blocks[randcode(10)] = array( 'COL' => 1 *4 );


        $blocks[randcode(10)] = array( 'COL' => 2 *4);
        $blocks[randcode(10)] = array( 'COL' => 1 *4);


        $blocks[randcode(10)] = array( 'COL' => 1 *4);
        $blocks[randcode(10)] = array( 'COL' => 2 *4);

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
    private function displayPortlets( $blocks ) {
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

    public function users(){
        coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/users/', 'name' => 'User Manager' )
        ));

        if( ( !count($this->_params) || (count($this->_params) === 1 && empty($this->_params[0])) )
            && method_exists( $this, 'users_default') ){

            $this->users_default();

        } else if( method_exists( $this, 'users_' . $this->_params[0]) ){
            $this->{'users_' . $this->_params[0]}();

        } else {
            trigger_error('Ah crap...404');
        }
    }

    public function users_default(){
        $objSQL = coreObj::getDBO();
        $objTPL = coreObj::getTPL();
        $objTime  = coreObj::getTime();

        $objTPL->set_filenames(array(
            'body' => cmsROOT . Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/users/default/default.tpl',
        ));

            $query = $objSQL->queryBuilder()
                ->select('*')
                ->from('#__users')
                ->build();

            $users = $objSQL->fetchAll( $query, 'id' );
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

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'User Management',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);
    }

    public function users_add(){
        coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/users/add', 'name' => 'Add User' )
        ));


    }


    //public function index() {}

}
?>