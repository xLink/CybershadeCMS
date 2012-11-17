<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Debug extends coreObj{

    public $errors          = array(),
           $includedFiles   = array(),
           $templateFiles   = array();

    public function __construct( ) {

    }

/**
  //
  //-- Included Files Tab
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
        $this->includedFiles = get_included_files();

        if( $output !== true ) {
            return;
        }

        $output = '';

        foreach( $this->includedFiles as $file ) {
            $output .= sprintf('<li>%s</li>', $file);
        }

        return array('count' => count($this->includedFiles), 'content' => sprintf( '<ul>%s</ul>', $output ));
    }

/**
  //
  //-- SQL Queries Tab
  //
**/

    /**
     * Retrieves all the SQL Queries and pumps them out
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      array
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
            $output .= '<table class="table"><tbody>';

                $output .= '</tr>';
                    $output .= sprintf('<tr class="%s"><td colspan="11" style="height: 5px; padding: 0;"></td></tr>', ($query['affected_rows']=='-1' ? 'error' : 'success'));

                $replace = array('FROM', 'LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 'ON', 'OR', 'AND', 'SET', 'WHERE', 'LIMIT', 'GROUP BY', 'ORDER BY', 'VALUES', );

                foreach($replace as $r){
                    $replace = "\n";
                    $r = ' '.$r;

                    $query['query'] = str_replace($r, $replace.$r, $query['query']);
                }

                $geshi = new GeSHi($query['query'], 'sql');

                $output .= '</tr><tr>';
                    $output .= sprintf('<tr><td style="background-color: #1E1E1E; color: white;"> <strong>%1$s</strong> @ <strong>%2$s</strong> // Affected %3$d Rows <br /> %4$s </td></tr>',
                                    realpath($query['file']),
                                    $query['line'],
                                    $query['affected_rows'],
                                    $geshi->parse_code()
                                );


                if( $query['affected_rows'] == '-1' ){
                    $output .= '</tr><tr>';
                    $output .= sprintf('<td style="background-color: #1E1E1E; color: white;"> %s </td>', dump($query). $query['error']);
                }

                // $output .= '</tr><tr>';

                //     $output .= sprintf('<td> %s </td>', $this->getSource(file(realpath($query['file'])), $query['line'], 0, 5));

                $output .= '</tr>';
            $output .= '</tbody></table>';
            }

        }

        return array('count' => count($debug), 'content' => sprintf( '<ul>%s</ul>', $output ));
    }

/**
  //
  //-- Template Files Tab
  //
**/

    /**
     * Retrieves all the used template files
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      array
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

        return array('count' => count($files), 'content' => sprintf( '<ul>%s</ul>', $output ));
    }

/**
  //
  //-- Memory Usage Tab
  //
**/

    /**
     * Generates output for the Memory Usage Tab
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      array
     */
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

        $header = null; $memory = 0; $oldTime = 0;
        foreach($debug as $row){
            $info = explode(':', $row['info'], 2);
            if($info[0] == 'OUTPUT!'){ continue; }
            if($header !== $info[0]){
                $header = $info[0];
                $output .= '</tr><tr><td colspan="11" style="height: 2px; padding: 0;"></td>';
            }

            $mem = ($row['memory_exec'] - $memory);
            $output .= '</tr><tr>';

            $timeDiff = ($row['time_exec']-$oldTime);
            $output .= sprintf('<td width="10%%">%s</td>',          $row['time_exec'].' <br />('.
                                                                        (substr($timeDiff, 0, 1) == '-' ? '-'.$timeDiff : '+'.$timeDiff).')');
            $output .= sprintf('<td width="20%%">%s <br />%s</td>', $row['file_exec'], $row['start_exec'] .' - '.$row['end_exec']);
            $output .= sprintf('<td width="">%s</td>',              $info[1]);
            $output .= sprintf('<td width="15%%">%s</td>',          (substr($mem, 0, 1) == '-') ? '-' . formatBytes( -$mem ).'<br />Cleared' : formatBytes( $mem ).'<br />Used' );

            $memory = $row['memory_exec'];
            $oldTime = $row['time_exec'];
        }

        $output .= '</tr></tbody></table>';

        return array('count' => formatBytes(memory_get_usage()), 'content' => $output);
    }

/**
  //
  //-- PHP / CMS Errors Tab
  //
**/

    /**
     * Silently grabs all the PHP errors and throws them into the Errors Tab
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     */
    public function errorHandler( $errno, $errstr, $errfile, $errline, $errcontext){
        if(!(error_reporting() & $errno)){ return; }

        if( substr($errstr, 0, strlen('MYSQL Error:')) == 'MYSQL Error:') {
            $a = debug_backtrace();
            $this->errors[] = $a[3];

        } else {
            $this->errors[] = func_get_args();
        }
        $this->trace[] = getExecInfo();
    }

    /**
     * Silently grabs all the PHP errors and throws them into the Errors Tab
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       bool        $output     If True, The function will output the HTML
     *
     * @return      array
     */
    public function getPHPErrors( $output = false ){
        $definition = array(
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parsing Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Runtime Notice',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated'
        );

        $output = '<ul>';
        foreach($this->errors as $num => $error){
            $_errorOutput = '<table class="table table-bordered">';
            $_errorOutput .= '<colgroup><col width="1%"><col width="99%"></colgroup><tr>';

            $_errorOutput .= sprintf('<td>%s</td>', 'Type: ');
            $_errorOutput .= sprintf('<td>%s</td>', $definition[$error[0]]);

            $_errorOutput .= '</tr><tr>';
            $_errorOutput .= sprintf('<td>%s</td>', 'Message: ');
            $_errorOutput .= sprintf('<td>%s</td>', $error[1]);

            $_errorOutput .= '</tr><tr>';
            $_errorOutput .= sprintf('<td>%s</td>', 'File: ');
            $_errorOutput .= sprintf('<td>%s : %s</td>', $error[2], $error[3]);

            $_errorOutput .= '</tr><td colspan="2">';
            $_errorOutput .= $this->trace[$num];

            $_errorOutput .= '</tr><td colspan="2">';
            $_errorOutput .= $this->getSource(file($error[2]), $error[3], 0, 6);
            $_errorOutput .= '</td></tr></table>';


            $output .= sprintf('<li>%s</li>', $_errorOutput);
        }
        $output .= '</ul>';

        return array('count' => count($this->errors), 'content' => $output);
    }

/**
  //
  //-- Globals Tab
  //
**/

    /**
     * Gathers Output info for the Globals
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @return      array
     */
    public function getGlobals(){
        $count   = 0;
        $content = '';

        if( !is_empty($_GET) ){
            $content .= dump($_GET, '_GET');
        }

        if( !is_empty($_POST) ){
            $content .= dump($_POST, '_POST');
        }

        if( !is_empty($_SESSION) ){
            $content .= dump($_SESSION, '_SESSION');
        }

        if( !is_empty($_COOKIE) ){
            $content .= dump($_COOKIE, '_COOKIE');
        }

        if( !is_empty($_SERVER) ){
            $content .= dump($_SERVER, '_SERVER');
        }

        return array('count' => $count, 'content' => $content );
    }

/**
  //
  //-- Config Tab
  //
**/

    /**
     * Gathers Output info for the Config Array
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @return      array
     */
    public function getConfig(){
        $count   = 0;
        $content = '';

        $perms = array(
            'IS_ONLINE' => User::$IS_ONLINE,
            'IS_USER'   => User::$IS_USER,
            'IS_MOD'    => User::$IS_MOD,
            'IS_ADMIN'  => User::$IS_ADMIN,
        );
        $objUser = coreObj::getUser();
        $content .= dump($perms, 'Global User Perms for '.$objUser->grab('username'));

        $config = $this->config();
        $content .= dump($config, 'config');

        return array('count' => $count, 'content' => $content );
    }

/**
  //
  //-- Other Tab
  //
**/

    /**
     * Throws some misc stuff into a tab
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @return      array
     */
    public function getOtherTab(){

        $count = 0;
        $content = null;

        $cache = $this->getInitdCaches(true);
        if( is_array($cache) && !is_empty($cache) ){
            $count += $cache['count'];
            $content .= dump($cache['content'], 'Cache\'s in use');
        }

        $cache = $this->getAvailableHooks(true);
        if( is_array($cache) && !is_empty($cache) ){
            $count += $cache['count'];
            $content .= dump($cache['content'], 'Hooks avaliable on this page');
        }


        return array('count' => $count, 'content' => $content );
    }

        /**
         * Retrieves all the included files in the current page
         *
         * @version     1.0
         * @since       1.0.0
         * @author      Dan Aldridge
         *
         * @param       bool        $output     If True, The function will output the HTML
         *
         * @return      array
         */
        public function getInitdCaches( $output = false ) {
            if( $output !== true ) {
                return '';
            }

            $output   = '';
            $objCache = coreObj::getCache();

            $output = array(
                'loaded' => $objCache->loadedCaches,
                'failed' => $objCache->failedCaches
            );


            return array('count' => count($output), 'content' => $output );
        }

        /**
         * Retrieves all the available hooks on the page
         *
         * @version     1.0
         * @since       1.0.0
         * @author      Dan Aldridge
         *
         * @param       bool        $output     If True, The function will output the HTML
         *
         * @return      array
         */
        public function getAvailableHooks( $output = false ) {
            if( $output !== true ) {
                return '';
            }

            $objPlugin = coreObj::getPlugins();
            $hooks = $objPlugin->getAvailableHooks();

            return array('count' => count($hooks), 'content' => $hooks );
        }

/**
  //
  //-- OUTPUT!
  //
**/

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

        $objPage->addJSFile(array('src' => '/'.root().'assets/javascript/tabs.js'), 'footer');
        $objPage->addJSFile(array('src' => '/'.root().'assets/javascript/debug.js'), 'footer');

        // Setup the tabs
        $tab = $this->getGlobals(true);
        $debugTabs['globals']   = array(
            'title'     => 'Globals',
            'content'   => $tab['content'],
        );

        // Setup the tabs
        $tab = $this->getConfig(true);
        $debugTabs['config']   = array(
            'title'     => 'Config',
            'content'   => $tab['content'],
        );

        $tab = $this->getPHPErrors(true);
        $debugTabs['errors']    = array(
            'title'     => sprintf('PHP / CMS Errors <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        $tab = $this->getMemoryUse(true);
        $debugTabs['memory']    = array(
            'title'     => sprintf('Memory Usage <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        $tab = $this->getSQLQueries(true);
        $debugTabs['queries']   = array(
            'title'     => sprintf('SQL Queries <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        $tab = $this->getIncludedFiles(true);
        $debugTabs['included']  = array(
            'title'     => sprintf('Included Files <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        $tab = $this->getTemplates(true);
        $debugTabs['templates'] = array(
            'title'     => sprintf('Template Files <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        $tab = $this->getOtherTab(true);
        $debugTabs['other']     = array(
            'title'     => sprintf('Others <div class="label  label-info">%s</div>', $tab['count']),
            'content'   => $tab['content'],
        );

        // Allow developers to hook into the debug bar
        $objPlugin->hook('CMS_DEBUGBAR_TABS', $debugTabs);

        $counter = 0;
        foreach( $debugTabs as $k => $tab ) {
            $tabs .= sprintf( '<li class="tab" id="%1$s" data-toggle="tab"><a href="#%1$s">%2$s</a></li>',
                $k,
                $tab['title']
            );

            $content .= sprintf( '<div class="tab-pane content %s">%s</div>',
                $k,
                $tab['content']
            );
        }


        return sprintf( '<div id="debug-tabs" data-tabs="true"><ul class="nav nav-tabs">%s</ul><div class="tab-content well">%s</div></div>',
            $tabs,
            $content
        );
    }


    /**
     * Get source and highlight it for output
     *
     * @param string $source Line source
     * @param int $error Error on line
     * @param int $level (0 = error / 1 = warn)
     * @param int $lines Source lines to show
     *
     * @return string
     */
    private function getSource($source, $error, $level = 0, $lines = 10) {
        $output = null;
        $found = false;
        $begin = $e = $error - $lines > 0 ? $error - $lines : 1;
        $end = $error + $lines <= count($source) ? $error + $lines : count($source);
        $mark = $level == 0 ? 'error' : 'warn';

        // colorize
        foreach($source as $idx => &$line) {
            $colorize = null;

            if ( preg_match('/\/\*/', $line) ){ $found = true; }// fix comments
            if ( preg_match('/<\?(php)?[^[:graph:]]/', $line) ) {
                $colorize .= str_replace(array('<code>', '</code>'), '', highlight_string($line, true)); // fix colors
            } else {
                if ( $found ) {
                    $colorize .= preg_replace(
                                    array('/(&lt;\?php&nbsp;)+/', '/\/\//'),
                                    '',
                                    str_replace(
                                        array('<code>', '</code>'),
                                        array(''),
                                        highlight_string('<?php //'.$line, true)
                                    )
                                ); // fix comment
                } else {
                    $colorize .= preg_replace(
                                    '/(&lt;\?php&nbsp;)+/',
                                    '',
                                    str_replace(
                                        array('<code>', '</code>'),
                                        array(''),
                                        highlight_string('<?php '.$line, true)
                                    )
                                ); // fix colors
                }
            }
            if (preg_match('/\*\//', $line)){
                $found = false; // end fix comments
            }

            // output the marked line or the normal lines
            if ( ($idx + 1) === $error ) {
                $line = "<tr><td class='{$mark}'>".($idx + 1).".</div></td><td class='{$mark}'>{$colorize}</div></td></tr>";
            } else {
                $line = "<tr><td>".($idx + 1).".</td><td>{$colorize}</td></tr>";
            }
        }

        // only get a certain number of lines to show
        for($i = $begin - 1; $i < $end; $i++) {
            $output .= $source[$i];
        }

        return '<table class="debugCode" cellspacing="0" cellpadding="0">
                <col width="1%" /><col width="99%" />
                '. $output .'
                </table>';
    }

}

?>