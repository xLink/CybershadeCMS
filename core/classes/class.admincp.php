<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_AdminCP extends Core_Classes_coreObj{

    public function __construct($name, $options=array()){
        $this->mode   = doArgs('__mode',      null,         $options);
        $this->module = doArgs('__module',    'core',       $options);
        $this->action = doArgs('__action',    'index',      $options);
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

        $module = $this->module;
        $this->module = 'Admin_Modules_'.$this->module;

        // if defaults are being loaded for the core acp panel, then we want dashboard not index
        if( $this->action == 'index' && $module == 'core' ){
            $this->action = 'dashboard';
        }

        // if nothing is selected, index all the way
        if( is_empty($this->action) ){
            $this->action = 'index';
        }

        $action = array( $this->action );
        if( strpos($this->action, '/') !== false ){
            $action = explode('/', $this->action);
        }

        $panels = cmsROOT.'modules/%s/panels/';
        $panels = sprintf($panels, $module);

        // check if we are dealing with the sub panels or not
        if( file_exists( $panels ) && is_readable( $panels ) && count( glob($panels.'panel.*.php') ) ){
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
            $path = sprintf($panels, $module).'panel.'.$args['method'].'.php';
                if( file_exists($path) && is_readable($path) ){
                    require_once($path);
                    (DEBUG ? debugLog($path, 'invokeRoute(): Loaded sub panel... ') : '');
                }else{
                    trigger_error('Error: Could not load ACP Panel: '.$path);
                }

            // then call to it like normal :D
            $method = reflectMethod($this->module.'_'.$args['method'], $args['args'][0], $args);
        }else{
            $method = reflectMethod($this->module, array_shift($action), $action);
        }

        if( $method === false ) {
            $objRoute->throwHTTP(404);
        }
    }

    public function getNav(){
        $objSQL = Core_Classes_coreObj::getDBO();
        $objTPL = Core_Classes_coreObj::getTPL();

        $acpROOT = '/'.root().'admin/';

        $query = $objSQL->queryBuilder()
            ->select('id', 'link_url', 'link_title', 'parent_id')
            ->from('#__menus')
            ->orderBy('`menu_name`, `order`')
            ->where('menu_name', '=', 'admin_menu')
            ->build();

        $results = $objSQL->fetchAll( $query, 'id' );
            if( count( $results ) <= 0 ) {
                trigger_error('No results could be found for the admin menu');
                return false;
            }

        foreach( $results as $id => $result ) {
            $results[$id]['link_url'] = $result['link_url'] = str_replace( '{ADMIN_ROOT}', $acpROOT, $result['link_url'] );

            if( $result['parent_id'] !== '0' ) {
                
                if( !isset( $results[$result['parent_id']]['subs'] ) ) {
                    $results[$result['parent_id']]['subs'] = array();
                }
                
                $results[$result['parent_id']]['subs'][$id] = $result;

                unset( $results[$id] );
            }
        }

        $this->generateNav($results);
    }

    protected function generateNav( $links=array() ){

        $objSQL = Core_Classes_coreObj::getDBO();
        $objTPL = Core_Classes_coreObj::getTPL();
        $objPage = Core_Classes_coreObj::getPage();

        // Loop through the links
        foreach( $links as $link ) {

            $objTPL->assign_block_vars('menu', array());

            // If this navigational piece has subnavigation, deal with it.
            if ( isset( $link['subs'] ) && !empty( $link['subs'] ) ) {

                // Setup our dropdown parent item
                $objTPL->assign_block_vars('menu.dropdown', array(
                    'TITLE' => $link['link_title'],
                ));

                // Loop through our subnavigational items
                foreach( $link['subs'] as $subLink ) {

                    // If the title and / or url isn't set, ignore it
                    if( !isset( $subLink['link_title'] ) || !isset( $subLink['link_url'] ) ) {
                        continue;
                    }

                    $objTPL->assign_block_vars('menu.dropdown.subnav', array(
                        'URL'   => $subLink['link_url'],
                        'TITLE' => $subLink['link_title'],
                    ));

                }

            // Looks like a normal link, sweet.
            } else if( isset( $link['link_url'] ) ) {

                $objTPL->assign_block_vars('menu.normal', array(
                    'URL'   => $link['link_url'],
                    'TITLE' => $link['link_title'],
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