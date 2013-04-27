<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_ControlPanel extends Core_Classes_Module{

    public function __construct($name, $options=array()){
        $this->mode   = doArgs('__mode',      null,         $options);
        $this->module = doArgs('__module',    'core',       $options);
        $this->action = doArgs('__action',    'index',      $options);
        $this->extra  = doArgs('__extra',     null,         $options);

        $this->getNav();
    }

    /**
     * Tests to see if we have a body handle in the template system, if so output it
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function output(){
        $objTPL = Core_Classes_coreObj::getTPL();
        $page = Core_Classes_coreObj::getPage()->getVar('contents');

        $content = null;
        if( !$objTPL->isHandle('body') ){

            if( $page === null ){
                msgDie('FAIL', 'No output received from module.');
            } else {
                $content .= $page;
            }

        } else {
            if( !is_empty($page) ){
                $content .= $page;
            }

            $content .= $objTPL->get_html('body');
        }

        return $content;
    }

    /**
     * Decides what to do with the current url setup
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function invokeRoute(){
        // Get instanced
        $objRoute = Core_Classes_coreObj::getRoute();

        $module = $this->module;
        $this->module = ucwords($this->mode).'_Modules_'.$this->module;

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

        $panels = cmsROOT.'modules/%s/'.$this->mode.'_panels/';
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
            ob_start();
                $method = reflectMethod($this->module.'_'.$args['method'], $args['args'][0], $args);
            Core_Classes_coreObj::getPage()->setVar('contents', ob_get_clean() );

        }else{
            ob_start();
                $method = reflectMethod($this->module, array_shift($action), $action);
            Core_Classes_coreObj::getPage()->setVar('contents', ob_get_clean() );
        }

        // check if there is a language file for this module & load it
        $langFile = cmsROOT.'modules/'.$module.'/languages/'.$this->config('global', 'language').'/'.$this->mode.'.php';
            if ( is_file($langFile) && is_readable($langFile) ) {
                translateFile($langFile);
            }

        if( $method === false ) {
            $objRoute->throwHTTP(404);
        }
    }

    /**
     * Makes the menu into a multidimensional array for processing
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function getNav(){
        $objSQL = Core_Classes_coreObj::getDBO();
        $objTPL = Core_Classes_coreObj::getTPL();

        $cpROOT = root().$this->mode.'/';

        $query = $objSQL->queryBuilder()
            ->select('id', 'link_url', 'link_title', 'parent_id')
            ->from('#__menus')
            ->orderBy('`menu_name`, `order`')
            ->where('menu_name', '=', strtoupper($this->mode.'_menu') )
            ->build();

        $results = $objSQL->fetchAll( $query, 'id' );
            if( count( $results ) <= 0 ) {
                trigger_error('No results could be found for the '.$this->mode.' menu');
                return false;
            }

        foreach( $results as $id => $result ) {
            $results[$id]['link_url'] = $result['link_url'] = str_replace( '{CP_ROOT}', $cpROOT, $result['link_url'] );

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

        /**
         * Generates a menu from an array
         *
         * @version 1.0
         * @since   1.0.0
         * @author  Dan Aldridge
         *
         * @return  void
         */
        protected function generateNav( $links=array() ){

            $objSQL = Core_Classes_coreObj::getDBO();
            $objTPL = Core_Classes_coreObj::getTPL();
            $objPage = Core_Classes_coreObj::getPage();

            // Loop through the links
            foreach( $links as $link ) {

                $objTPL->assign_block_vars('menu', array());

                // If this navigational piece has subnavigation, deal with it.
                if ( isset( $link['subs'] ) && !empty( $link['subs'] ) ) {
                    $open = false;

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


    /**
     * Outputs a block with content in for the ACP
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public static function setupBlock($handle, $options=array()){
        $options = array(
            'cols'        => doArgs('cols', 3, $options),
            'vars'        => ( isset($options['vars']) && is_array($options['vars']) ? $options['vars'] : array()),
            'custom'      => ( isset($options['custom']) && is_array($options['custom']) ? $options['custom'] : array()),
            'custom_html' => ( isset($options['custom_html']) && is_array($options['custom_html']) ? $options['custom_html'] : array()),
        );

        if( is_empty( $options['vars'] ) ){
            trigger_error('No vars passed to setupBlock()');
            return;
        }
        if( !in_array( $options['cols'], array(1,2,3)) ){
            trigger_error('Columns option needs to be 1 2 or 3');
            return;
        }


        $objTPL = Core_Classes_coreObj::getTPL();
        $objTPL->set_filenames(array(
            $handle  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

        $objTPL->assign_block_vars('block', $options['vars']);

        $objTPL->assign_block_vars('block.start_row', array());

        $objTPL->assign_block_vars('block.'.$options['cols'].'col', array());

        if( !is_empty($options['custom']) ){
            $objTPL->assign_block_vars('block.custom', $options['custom']);
        }

        if( !is_empty($options['custom_html']) ){
            $objTPL->assign_block_vars('block.custom_html', $options['custom_html']);
        }

        $objTPL->assign_block_vars('block.end_row', array());

        $objTPL->parse($handle, false);
    }

}
?>