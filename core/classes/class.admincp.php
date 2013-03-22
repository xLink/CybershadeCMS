<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_AdminCP extends Core_Classes_coreObj{

    public function __construct($name, $options=array()){
        // apparently it wants to throw the args into an array first :/
        $options = $options[0];

        $this->mode   = doArgs('__mode',      null,         $options);
        $this->module = doArgs('__module',    'core',       $options);
        $this->action = doArgs('__action',    'dashboard',  $options);
        $this->extra  = doArgs('__extra',     null,         $options);
    }

    /**
     * Tests to see if we have a body handle in the template system, if so output it
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    public function output(){
        $objTPL = Core_Classes_coreObj::getTPL();

        if( !$objTPL->isHandle('body') ){
            $page = Core_Classes_coreObj::getPage()->getVar('contents');

            if( $page === null ){
                msgDie('FAIL', 'No output received from module.');
            } else {
                echo $page;
            }

        } else {
            echo $objTPL->get_html('body');
        }
    }

    public function invokeRoute(){
        // Get instanced
        $objRoute = Core_Classes_coreObj::getRoute();

        $this->module = 'Admin_Modules_'.$this->module;

        $action = array($this->action);
        if( strpos($this->action, '/') !== false ){
            $action = explode('/', $this->action);
        }

        // check if we are dealing with the core panels or not
        if( $this->module == 'Admin_Modules_core' ){
            // we are !
            $method = array_shift($action);

            if( !isset($action[0]) || is_empty($action[0]) ){
                $action[0] = $method;
            }

            $args = array(
                'method' => $method,
                'args'   => $action,
            );

            // check the panel to see if it exists, if so include it
            $path = cmsROOT.'modules/core/panels/panel.'.$args['method'].'.php';
                if( file_exists($path) && is_readable($path) ){
                    require_once($path);
                    (DEBUG ? memoryUsage('System: Loaded panel...') : '');
                }else{
                    trigger_error('Error: Could not load ACP Panel: '.$path);
                }

            // then call to it like normal :D
            $method = reflectMethod($this->module.'_'.$args['method'], $args['args'][0], $args);
        }else{
            $method = reflectMethod($this->module, array_shift($action), $action);
        }

        if( !$method ) {
            $objRoute->throwHTTP(404);
        }
    }

    public function getNav(){
        $objSQL = Core_Classes_coreObj::getDBO();
        $objTPL = Core_Classes_coreObj::getTPL();

        $acpROOT = '/'.root().'admin/';

        $query = $objSQL->queryBuilder()
                    ->select('id', 'link', 'lname', 'blank', 'parent')
                    ->from('#__menus')
                    ->orderBy('name, disporder')
                    ->where('name', '=', 'admin_menu')
                    ->build();

        $results = $objSQL->fetchAll( $query, 'id' );

        if( sizeOf( $results ) <= 0 ) {
            trigger_error('No results could be found for the admin menu');
            return false;
        }

        foreach( $results as $id => $result ) {
            $results[$id]['icon'] = $result['icon'] = 'icon-dashboard';
            $results[$id]['link'] = $result['link'] = str_replace( '{ADMIN_ROOT}', $acpROOT, $result['link'] );

            if( $result['parent'] !== '0' ) {
                
                if( !isset( $results[$result['parent']]['subs'] ) ) {
                    $results[$result['parent']]['subs'] = array();
                }
                
                $results[$result['parent']]['subs'][$id] = $result;

                unset( $results[$id] );
            }
        }

        // $nav = array(
        //     'Dashboard' => array(
        //         'icon' => 'fa-icon-dashboard',
        //         'url'  => $acpROOT,
        //     ),

        //     'System' => array(
        //         'icon' => 'icon-cog',
        //         'subs' => array(
        //             'Site Configuration' => array(
        //                 'icon' => 'icon-wrench',
        //                 'url'  => $acpROOT.'core/siteConfig/',
        //             ),
        //         ),
        //     ),

        //     'Users' => array(
        //         'icon' => 'icon-user',
        //         'subs' => array(
        //             'Search' => array(
        //                 'icon' => 'fa-icon-search',
        //                 'url'  => $acpROOT.'core/users/search/',
        //             ),
        //             'Manage Users' => array(
        //                 'icon' => 'fa-icon-user',
        //                 'url'  => $acpROOT.'core/users/manage/',
        //             ),
        //             'Add New User' => array(
        //                 'icon' => 'fa-icon-plus',
        //                 'url'  => $acpROOT.'core/users/add/',
        //             ),
        //         ),
        //     ),

        //     'Modules' => array(
        //         'icon' => 'fa-icon-sitemap',
        //         'url'  => $acpROOT . 'modules/',
        //     ),

        //     'Themes' => array(
        //         'icon' => 'icon-picture',
        //         'url'  => $acpROOT . 'themes/',
        //     ),

        //     'Blocks' => array(
        //         'icon' => 'fa-icon-check-empty',
        //         'url'  => $acpROOT . 'blocks/',
        //     ),

        //     'Plugins' => array(
        //         'icon' => 'fa-icon-folder-close',
        //         'url'  => $acpROOT . 'plugins/',
        //     ),
            
        //     'Languages' => array(
        //         'icon' => 'icon-globe',
        //         'url'  => $acpROOT . 'languages/',
        //     ),
        // );

        $this->generateNav($results);
    }

    protected function generateNav( $links=array() ){

        $objSQL = Core_Classes_coreObj::getDBO();
        $objTPL = Core_Classes_coreObj::getTPL();
        $objPage = Core_Classes_coreObj::getPage();

        // Loop through the links
        foreach( $links as $link ) {

            $objTPL->assign_block_vars('menu', array());

            // If the icon isn't set, ignore this link
            if( !isset( $link['icon'] ) ) {
                continue;
            }

            // If this navigational piece has subnavigation, deal with it.
            if ( isset( $link['subs'] ) && !empty( $link['subs'] ) ) {

                // Setup our dropdown parent item
                $objTPL->assign_block_vars('menu.dropdown', array(
                    'TITLE' => $link['lname'],
                    'ICONS' => $link['icon']
                ));

                // Loop through our subnavigational items
                foreach( $link['subs'] as $subLink ) {

                    // If the icon and / or url isn't set, ignore it
                    if( !isset( $subLink['icon'] ) || !isset( $subLink['link'] ) ) {
                        continue;
                    }

                    $objTPL->assign_block_vars('menu.dropdown.subnav', array(
                        'URL'   => $subLink['link'],
                        'ICONS' => $subLink['icon'],
                        'TITLE' => $subLink['lname']
                    ));
                }

            // Looks like a normal link, sweet.
            } else if( isset( $link['link'] ) ) {

                $objTPL->assign_block_vars('menu.normal', array(
                    'URL'   => $link['link'],
                    'ICONS' => $link['icon'],
                    'TITLE' => $link['lname']
                ));
            }

        }
    }


    public function getNotifications(){

    }

   /**
     * Outputs the ACP Dashboard
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     */
    public function dashboard(){
        $objPage = Core_Classes_coreObj::getPage();
        $this->getNav();
        $this->invokeRoute();
        $objPage->buildPage();
        $objPage->showHeader();
        $this->output();
        $objPage->showFooter();
    }
}
?>