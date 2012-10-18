<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class debug extends coreObj{

    public $errors          = array(),
           $includedFiles   = array(),
           $templateFiles   = array();

    public function __construct( ) {

    }

    /**
     * Retrieves all the included files in the current page
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      array
     */
    public function getIncludedFiles( $output = false ) {
        $this->includedFiles = get_included_files ();

        if( $output !== true ) {
            return;
        }

        $output = '';

        foreach( $this->includedFiles as $file ) {
            $output .= sprintf('<li>%s</li>', $file);
        }

        return sprintf( '<ul>%s</ul>', $output );
    }

    /**
     * Retrieves all the included files in the current page
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @todo
     *
     * @return      string
     */
    public function getInitdCaches( $output = false ) {
        if( $output !== true ) {
            return '';
        }

        $output   = '';
        $objCache = coreObj::getCache();

        if( !empty( $objCache->loadedCaches ) ) {
            foreach( $objCache->loadedCaches as $cache ) {
                $output .= sprintf('<li>%s</li>', $cache);
            }
        }

        return sprintf( '<ul>%s</ul>', $output );
    }

    /**
     * Retrieves all the SQL Queries and pumps them out
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      string
     */
    public function getSQLQueries( $output = false ) {
        if( $output !== true ) {
            return '';
        }

        $output   = '';
        $objSQL = coreObj::getDBO();

        $debug = $objSQL->getVar('debug');
        if( !empty( $debug ) ) {
            foreach( $debug as $query ) {
                $output .= sprintf('<li>%1$s (%2$f)<br /><span class="file">%3$s:%4$s</span></li>',
                    $query['query'],
                    $query['time_taken'],
                    $query['file'],
                    $query['line']
                );
            }
        }

        return sprintf( '<ul>%s</ul>', $output );
    }

    /**
     * Retrieves all the used template files
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      string
     */
    public function getTemplates( $output = false ) {
        if( $output !== true ) {
            return '';
        }

        $output   = '';
        $files    = coreObj::getTPL()->files;

        if( !empty( $files ) ) {
            foreach( $files as $file ) {
                $output .= sprintf('<li>%1$s</li>',
                    $file
                );
            }
        }

        return sprintf( '<ul>%s</ul>', $output );
    }

    public function getMemoryUse( $output = false ){
        if( $output !== true ){
            return false;
        }

        $output = null;
        $debug = memoryUsage('OUTPUT!');


//echo dump($debug);

        $output .= '<table class="table "><tbody><tr>';

        $header = null;
        foreach($debug as $row){
            $info = explode(':', $row['info']);
            if($info[0] == 'OUTPUT!'){ continue; }
            if($info[0] !== $header){
                $header = $info[0];
                $output .= '</tr><tr>';
                $output .= sprintf('<td colspan="9"><h4>%s</h4></td>', $header.' - '.$row['file_exec']);
            }
            $output .= '</tr><tr>';

            #$output .= sprintf('<td>[%s - %d-%d] [Timer: %s] [Memory: %s] %s</td>',);


            $output .= sprintf('<td>%s</td>', $row['time_exec']);
            $output .= sprintf('<td>%s</td>', $row['start_exec'] .' - '.$row['end_exec']);
            $output .= sprintf('<td>%s</td>', $row['memory_exec']);
            $output .= sprintf('<td>%s</td>', $info[1]);
        }

        $output .= '</tr></tbody></table>';
        return $output;
    }

    /**
     * Outputs the debug onto the page
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @return      string
     */
    public function output( ) {
        $tabs      = '';
        $content   = '';
        $output    = '';
        $debugTabs = array( );
        $objPlugin = coreObj::getPlugins();
        $objPage   = coreObj::getPage();

        // Setup the tabs
        $debugTabs['console']   = array(
            'title'     => 'Console Log',
            'content'   => ''
        );
        $debugTabs['errors']    = array(
            'title'     => 'PHP / CMS Errors',
            'content'   => ''
        );
        $debugTabs['memory']    = array(
            'title'     => 'Memory Usage',
            'content'   => $this->getMemoryUse(true),
        );
        $debugTabs['queries']   = array(
            'title'     => 'SQL Queries',
            'content'   => $this->getSQLQueries(true)
        );
        $debugTabs['included']  = array(
            'title'     => 'Included Files',
            'content'   => $this->getIncludedFiles(true)
        );
        $debugTabs['templates'] = array(
            'title'     => 'Template Files',
            'content'   => $this->getTemplates(true),
        );
        $debugTabs['cache']     = array(
            'title'     => 'Cache\'s in use',
            'content'   => $this->getInitdCaches(true)
        );

        // Allow developers to hook into the debug bar
        $objPlugin->hook('CMS_DEBUGBAR_TABS', $debugTabs);

        $counter = 0;
        foreach( $debugTabs as $k => $tab ) {
            $tabs .= sprintf( '<li class="tab" id="%1$s" data-index="%3$d"><a href="#%1$s">%2$s</a></li>',
                $k,
                $tab['title'],
                $counter++
            );

            $content .= sprintf( '<div class="content">%s</div>',
                $tab['content']
            );
        }
        $tabs .= '<li class="tab pull-right"><div id="debug_button"><i class="socicon-cogs"></i></div></li>';
        return sprintf( '<div id="debug-tabs"><ul class="nav nav-tabs">%s</ul><div class="tab-content">%s</div></div>',
            $tabs,
            $content
        );
    }
}
?>