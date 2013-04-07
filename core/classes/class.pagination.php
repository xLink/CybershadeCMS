<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

/**
 * Used to paginate tables and sql results
 *
 * @version  1.0
 * @since    1.0.0
 * @author   Dan Aldridge
 */
class Core_Classes_Pagination extends Core_Classes_coreObj {

    public $instance = '';
    public $total_per_page = 1;
    public $total_items = 1;
    public $total_pages = 1;
    public $current_page = 1;
    public $query_string = '';

    /**
     * Loads the options for the pagination
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $name
     * @param   array   $args
     */
    public function __construct($name=null, $args=array()) {
        $this->instance       = doArgs('instance', '', $args);
        $this->total_per_page = doArgs('perPage', 1, $args);

        $this->total_items    = doArgs('count', 1, $args);

        //calculate some more basic vars
        $this->total_pages  = ceil( $this->total_items / $this->total_per_page );
        $this->current_page = doArgs( $this->instance, 1, $_GET, 'is_number' );

        //check that the current page is not over the max pages
        if( $this->current_page > $this->total_pages ){
            $this->current_page = $this->total_pages;
        }

        //check that the current page is not below 0
        if( $this->current_page < 1 ){
            $this->current_page = 1;
        }

        $this->url = $this->parseQueryString( is_empty($options['url']) ? $_SERVER['REQUEST_URI'] : $options['url'] );
    }

    /**
     * Returns the current page
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  int
     */
    public function getCurrentPage(){
        return $this->current_page;
    }

    /**
     * Returns the total amount of pages
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  int
     */
    public function getTotalPages(){
        return $this->total_pages;
    }

    /**
     * Get the SQL Limit String
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  int
     */
    public function getSqlLimit(){
        return (($this->current_page-1) * $this->total_per_page).','.$this->total_per_page;
    }

    /**
     * Sets current page to last page
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  int
     */
    public function goLastPage(){
        $this->current_page = ($this->getTotalPages() <= 0 ? 1 : $this->getTotalPages());
    }

    /**
     * Returns the html for the pagination
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   array   $options
     *
     * @return  int
     */
    public function getPagination( $options=array() ){
        $options = array(
            'url'      => doArgs('url', '', $options),
            'controls' => doArgs('controls', false, $options),
            'type'     => doArgs('type', 'pagination-mini', $options),
            'showOne'  => doArgs('showOne', false, $options),
        );

        $objTPL = Core_Classes_coreObj::getTPL();
        $objUser = Core_Classes_coreObj::getUser();

        // if we have 1 or less pages, then unless we specifically want to see it, hide the pagination
        if( $this->getTotalPages() <= 1 ){
            if( $options['showOne'] === false ){ 
                return ''; 
            }
        }

        // generate the pagination handle - each one has to be diff, to support > 1 on a page
        $handle = 'pagination_'.randCode(6);
        $objTPL->set_filenames(array(
            $handle => cmsROOT.'modules/core/views/markup.tpl'
        ));

        // figure out which one we want to use
        $switch = (IS_ONLINE ? $objUser->get('paginationStyle') : '1');
            if( !method_exists($this, 'paginationStyle'.$switch) ){
                $switch = '1';
            }

        $pages = $this->{'paginationStyle'.$switch}( $options['controls'] );
        $pages = ( isset($pages) ? $pages : array() );


        // setup the output
        $objTPL->assign_block_vars('pagination', array( 'TYPE' => $options['type'] ));
        foreach($pages as $page){
            $objTPL->assign_block_vars('pagination.page', array(
                'NUM'   => doArgs('label', doArgs('count', '0', $page), $page),
                'STATE' => doArgs('state', '', $page),
            ));

            if( doArgs('url', true, $page) ){
                $objTPL->assign_block_vars('pagination.page.url', array(
                    'URL'   => doArgs('url', true, $page) ? $this->url.$this->instance.'='.doArgs('count', '0', $page) : '',
                ));
            }else{
                $objTPL->assign_block_vars('pagination.page.span', array());
                
            }
        }

        // and output
        $objTPL->parse($handle, false);
        return $objTPL->get_html($handle);
    }

    /**
     * Parses the query string and removes the page value
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $url
     *
     * @return  int
     */
    public function parseQueryString($url){
        $url = explode('?', $url);
        parse_str($url[1], $vars);

        $query_string = '';
        foreach($vars as $key => $value){
            if($key != $this->instance && $value != 'last_page'){
                $query_string .= sprintf('%s=%s&', $key, urlencode($value));
            }
        }
        return sprintf('%s?%s', $url[0], $query_string);
    }


    /**
     * Simple pagination style 1
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $nnb     Next & back links
     *
     * @return  array
     */
    public function paginationStyle1( $nnb=false ){

        // define some vars to get started
        $adjacents  = 2;
        $totalPages = $this->getTotalPages();
        $currentPage = $this->getCurrentPage();

        // gather a list of pages
        $pages = array();

        if( $nnb ){
            // go back
            $pages[] = array( 'count' => 1,               'label' => '&lt; &lt;',   'state' => ($currentPage == 1 ? 'disabled' : ''), 'url' => ($currentPage == 1 ? false : true) );
            $pages[] = array( 'count' => $currentPage -1, 'label' => '&lt;',        'state' => ($currentPage < 2 ? 'disabled' : ''), 'url' => ($currentPage < 2 ? false : true) );
        }

        // dont break up the pagination if we dont have that many pages
        if( $totalPages < (7 + ($adjacents * 2)) ){
            for($i = 1; $i <= $totalPages; $i++){
                $pages[] = array(
                    'count' => $i,
                    'state' => ($i == $currentPage ? 'active' : ''),
                );
            }

        }else{

            // show some at the beginning, hide later pages
            if( $currentPage < (1 + ($adjacents * 3)) ){
                for($i = 1; $i <= (4 + ($adjacents * 2)); $i++){
                    $pages[] = array(
                        'count' => $i,
                        'state' => ($i == $currentPage ? 'active' : ''),
                    );
                }

                $pages[] = array( 'count' => '...', 'state' => 'disabled', 'url' => false );
                $pages[] = array( 'count' => ($totalPages -1) );
                $pages[] = array( 'count' => $totalPages );

            // hide some front & some back
            }else if( ($totalPages - ($adjacents * 2) > $currentPage) && ($currentPage > ($adjacents * 2)) ){
                $pages[] = array( 'count' => '1' );
                $pages[] = array( 'count' => '2' );
                $pages[] = array( 'count' => '...', 'state' => 'disabled', 'url' => false );

                for($i = ($currentPage - $adjacents); $i <= ($currentPage + $adjacents); $i++){
                    $pages[] = array(
                        'count' => $i,
                        'state' => ($i == $currentPage ? 'active' : ''),
                    );
                }

                $pages[] = array( 'count' => '...', 'state' => 'disabled', 'url' => false );
                $pages[] = array( 'count' => ($totalPages -1) );
                $pages[] = array( 'count' => $totalPages );
            
            // hide starter pages, we are further thru the pages now
            }else{
                $pages[] = array( 'count' => '1' );
                $pages[] = array( 'count' => '2' );
                $pages[] = array( 'count' => '...', 'state' => 'disabled', 'url' => false );

                for($i = ( $totalPages - (1 + ($adjacents * 3) ) ); $i <= $totalPages; $i++){
                    $pages[] = array(
                        'count' => $i,
                        'state' => ($i == $currentPage ? 'active' : ''),
                    );
                }

            }

        }

        if( $nnb ){
            // go back
            $pages[] = array( 'count' => $currentPage +1, 'label' => '&gt;',    'state' => ($currentPage >= $totalPages ? 'disabled' : ''), 'url' => ($currentPage >= $totalPages ? false : true) );
            $pages[] = array( 'count' => $totalPages, 'label' => '&gt; &gt;',   'state' => ($currentPage == $totalPages ? 'disabled' : ''), 'url' => ($currentPage == $totalPages ? false : true) );
        }

        return $pages;
    }



}

?>