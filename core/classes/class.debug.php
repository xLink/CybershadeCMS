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
     * Calculates Memory useage and Execution time between calls
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $info
     * @param       string $nl
     *
     * @return      string
     */
    function memoryUsage($info = null, $nl = '<br />') {
        global $START_CMS_LOAD;
        static $start_code_line = 0;
        static $memoryUsage = array();

        $start_time = $START_CMS_LOAD;

        $debug = debug_backtrace();
        $call_info = array_shift($debug);
        $code_line = $call_info['line'];
        $file = explode((stristr(PHP_OS, 'WIN') ? '\\' : '/'), $call_info['file']);
        $file = array_pop($file);

        if ($start_time === null) {
            print 'debug ['.($info === null ? null : $info).']<strong>'.$file.'</strong>> init'.$nl;
            $start_time = time() + microtime();
            $start_code_line = $code_line;
            return 0;
        }

        $memoryUsage[] = array(
            'info'        => ($info === null ? null : $info),
            'file_exec'   => $file,
            'start_exec'  => $start_code_line,
            'end_exec'    => $code_line,
            'time_exec'   => round(time() + microtime() - $start_time, 4),
            'memory_exec' => formatBytes(memory_get_usage())
        );

        $start_time = time() + microtime();
        $start_code_line = $code_line;

        return $memoryUsage;
    }

/**
  //
  // Debug Output Functionality
  //
**/

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

        $output .= '<table class="table table-bordered"><thead>';
            $output .= sprintf('<th>%s</th>', 'Execution <br />Time');
            $output .= sprintf('<th>%s</th>', 'File <br />Lines');
            $output .= sprintf('<th>%s</th>', 'Messages <br />'.count($debug));
            $output .= sprintf('<th>%s</th>', 'Memory <br />'.formatBytes(memory_get_usage()));
        $output .= '</thead><tbody><tr>';

        $header = null; $memory = 0;
        foreach($debug as $row){
            $info = explode(':', $row['info'], 2);
            if($info[0] == 'OUTPUT!'){ continue; }
            if($header !== $info[0]){
                $header = $info[0];
                $output .= '</tr><tr><td colspan="11" style="height: 2px; padding: 0;"></td>';
            }
            $output .= '</tr><tr>';

            $mem = ($row['memory_exec'] - $memory);

            $output .= sprintf('<td width="10%%">%s</td>', $row['time_exec']);
            $output .= sprintf('<td width="20%%">%s <br />%s</td>', $row['file_exec'], $row['start_exec'] .' - '.$row['end_exec']);
            $output .= sprintf('<td width="">%s</td>', $info[1]);
            $output .= sprintf('<td width="15%%">%s</td>', (substr($mem, 0, 1) == '-') ? '-' . formatBytes( -$mem ) : formatBytes( $mem ) );

            $memory = $row['memory_exec'];
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