<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Admin_Modules_core extends Core_Classes_Module{

    public function __construct(){

    }

/**
  //
  //-- Dashboard Section
  //
**/
    public function dashboard(){
        $objTPL = Core_Classes_coreObj::getTPL();
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


/**
  //
  //-- Site Configuration
  //
**/
    public function siteConfig(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Site Config' )
        ));

        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();


        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

        $yn = array(1 => langVar('L_YES'), 0 => langVar('L_NO'));

            $fields = array(
                langVar('L_SITE_CONFIG')            => '_header_',
                    langVar('L_SITE_TITLE')         => $objForm->inputbox('title', 'text', $this->config('site', 'title')),
                    langVar('L_SITE_SLOGAN')        => $objForm->inputbox('slogan', 'text', $this->config('site', 'slogan')),
                    langVar('L_ADMIN_EMAIL')        => $objForm->inputbox('admin_email', 'text', $this->config('site', 'admin_email')),
                    langVar('L_GANALYTICS')         => $objForm->inputbox('google_analytics', 'input', $this->config('site', 'google_analytics')),

                langVar('L_CUSTOMIZE')              => '_header_',
                    // langVar('L_INDEX_MODULE')       => $objForm->select('index_module', $defaultModule,
                    //                                     array('disabled' => $tzDisable, 'selected' => $this->config('site', 'index_module'))),
                    // langVar('L_DEF_LANG')           => $objForm->select('language', $languages,
                    //                                     array('selected' => $this->config('site', 'language'))),
                    // langVar('L_DEF_THEME')          => $objForm->select('theme', $tpl,
                    //                                     array('selected' => $this->config('site', 'theme'))),
                    langVar('L_THEME_OVERRIDE')     => $objForm->radio('theme_override', $yn, $this->config('site', 'theme_override')),
                    langVar('L_SITE_TZ')            => $timezone,
                    langVar('L_DST')                => $objForm->radio('dst', $yn, $this->config('time', 'dst')),
                    langVar('L_DEF_DATE_FORMAT')    => $objForm->inputbox('default_format', 'input', $this->config('time', 'default_format')),
            );

        $form = $objForm->outputForm(array(
            'FORM_START'    => $objForm->start('panel', array('method' => 'POST', 'action' => $saveUrl)),
            'FORM_END'      => $objForm->finish(),

            'FORM_TITLE'    => $mod_name,
            'FORM_SUBMIT'   => $objForm->button('submit', 'Submit'),
            'FORM_RESET'    => $objForm->button('reset', 'Reset'),

            'HIDDEN'        => $objForm->inputbox('sessid', 'hidden', $sessid).$objForm->inputbox('id', 'hidden', $uid),
        ),
        array(
            'field' => $fields,
            'desc' => array(
                    langVar('L_INDEX_MODULE')       => langVar('L_DESC_IMODULE'),
                    langVar('L_SITE_TZ')            => langVar('L_DESC_SITE_TZ'),
                    langVar('L_DEF_DATE_FORMAT')    => langVar('L_DESC_DEF_DATE'),
                    langVar('L_DEF_THEME')          => langVar('L_DESC_DEF_THEME'),
                    langVar('L_THEME_OVERRIDE')     => langVar('L_DESC_THEME_OVERRIDE'),
                    langVar('L_ALLOW_REGISTER')     => langVar('L_DESC_ALLOW_REGISTER'),
                    langVar('L_EMAIL_ACTIVATE')     => langVar('L_DESC_EMAIL_ACTIVATE'),
                    langVar('L_MAX_LOGIN_TRIES')    => langVar('L_DESC_MAX_LOGIN'),
                    langVar('L_REMME')              => langVar('L_DESC_REMME'),
                    langVar('L_GANALYTICS')         => langVar('L_DESC_GANALYTICS'),
            ),
            'errors' => $_SESSION['site']['panel']['error'],
        ),
        array(
            'header' => '<h4>%s</h4>',
            'dedicatedHeader' => true,
            'parseDesc' => true,
        ));


        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'User Management',
            'CONTENT' => $form,
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);

    }

/**
  //
  //-- User Admin Section
  //
**/

    public function users(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'User Manager' )
        ));

        if( ( !count($this->_params) || (count($this->_params) === 1 && empty($this->_params[0])) )
            && method_exists( $this, 'users_default') ){

            $this->users_default();

        } else if( method_exists($this, 'users_' . $this->_params[0]) ){
            $this->{'users_' . $this->_params[0]}();

        } else {
            trigger_error('Ah crap...404');
        }
    }

    public function users_manage(){
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT . 'modules/core/views/admin/users/default/list.tpl',
        ));

        $query = $objSQL->queryBuilder()
            ->select('*')
            ->from('#__users')
            ->orderby('id')
            ->build();

        $users = $objSQL->fetchAll( $query, 'id' );
            if( !$users ){
                msgDie('INFO', 'Cant query users :/');
                return false;
            }

        foreach( $users as $id => $user ){
            $objTPL->assign_block_vars('user', array(
                'ID'              => $id,
                'NAME'            => $user['username'],
                'DATE_REGISTERED' => $objTime->mk_time($user['register_date']),
                'STATUS'          => ( $user['active'] == '1' ? 'Active' : 'Disabled' )
            ));

            $objTPL->assign_block_vars('user.actions.edit', array(
                'URL'   => '',
                'ICON'  => '',
            ));

            $objTPL->assign_block_vars('user.actions.activate', array(
                'URL'   => '',
                'ICON'  => '',
            ));

            $objTPL->assign_block_vars('user.actions.disable', array(
                'URL'   => '',
                'ICON'  => '',
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

    /*public function users_manage(){
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/users/default/manage.tpl',
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

                $objTPL->assign_block_vars('user.actions.edit', array(
                    'URL'   => '',
                    'ICON'  => '',
                ));

                $objTPL->assign_block_vars('user.actions.activate', array(
                    'URL'   => '',
                    'ICON'  => '',
                ));

                $objTPL->assign_block_vars('user.actions.disable', array(
                    'URL'   => '',
                    'ICON'  => '',
                ));
            }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'User Management',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);
    }*/

    public function users_add() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objTime    = Core_Classes_coreObj::getTime();

        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Add User' )
        ));

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/users/default/add.tpl',
        ));

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Add User',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);
    }

/**
  //
  //-- Menu Admin Section
  //
**/

    public function menu() {
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Menu Manager' )
        ));

        if( ( !count($this->_params) || (count($this->_params) === 1 && empty($this->_params[0])) )
            && method_exists( $this, 'menu_default') ){

            $this->menu_default();

        } else if( method_exists( $this, 'users_' . $this->_params[0]) ){
            $this->{'menu_' . $this->_params[0]}();

        } else {
            trigger_error('Ah crap...404');
        }
    }

    public function menu_default() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/menus/default/menu_list.tpl',
        ));
        
        // List the different types of menus
        $query = $objSQL->queryBuilder()
            ->select('id', 'name')
            ->from('#__menus')
            ->groupBy('name')
            ->build();

        $menus = $objSQL->fetchAll( $query, 'id' );

        foreach( $menus as $menu ) {
            $objTPL->assign_block_vars( 'menu', array(
                'URL'  => '/' . root() . 'admin/core/menu_edit/' . $menu['name'],
                'NAME' => $menu['name']
            ));
        }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Menu Administration',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }

    public function menu_edit() {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();


        // Check we have the menu name
        if( !is_array( $this->_params ) || !is_string( $this->_params[0] ) || strlen( $this->_params) == 0 ) {
            // error
            echo 'bad stuff';
        }

        $menuName = $this->_params[0];

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/menus/default/menu_link_list.tpl',
        ));


        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $queryList =  $objSQL->queryBuilder()
            ->select('*')
            ->from('#__menus')
            ->where('name', '=', $menuName)
            ->orderBy('disporder')
            ->build();

        $links = $objSQL->fetchAll($queryList);
            if( !is_array( $links ) ) {
                // Trigger error
                // Add error to tpl
                return false;
            }

        foreach ($links as $key => $link) {
            $objTPL->assign_block_vars( 'link', array(
                'LABEL' => $link['lname'],
                'URL'   => $link['link'],
                'ID'    => $link['id']
                // 'STATUS_CLASS' => ( $link['status'] == 1 ? 'success' : 'error'),
                // 'STATUS'       => $link['status']
            ));
        }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Menu Administration',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }
}
?>