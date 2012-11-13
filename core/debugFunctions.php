<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

    /**
     * Pretty wrapper to print_r()
     *
     * @version     3.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       variable    $var
     * @param       string      $info
     * @param       string      $color          Changes the debug header color
     * @param       bool        $specialFX
     *
     * @return      string
     */
    function dump(&$var, $info = false, $color='', $specialFX=true) {
        if (file_exists('debug')) { return; }

        $objPage   = coreObj::getPage();
        $scope     = false;
        $prefix    = 'unique';
        $suffix    = 'value';
        $return    = null;
        $specialFX = ($specialFX!==false ? true : false);

        if(is_object($objPage)){

            if($specialFX){
                /*$objPage->addJSFile(array(
                    'src'      => '/'.root().'assets/javascript/tree.js',
                    'priority' => LOW,
                ));*/
            }

        }else{
            static $run;
            if(!isset($run) || $run != true){
                echo '<link rel="stylesheet" type="text/css" href="/' . root() . 'assets/styles/debug.css" />'."\n";
            }
            $run = true;
        }

        $vals = ($scope ? $scope : $GLOBALS);

        $old = $var;
        $var = $new = $prefix.rand().$suffix;
        $vname = false;
        foreach($vals as $key => $val) {
            if ($val === $new) {
                $vname = $key;
            }
        }
        $var = $old;

        $debug = debug_backtrace();
        $call_info = array_shift($debug);
        $code_line = $call_info['line'];
        $file = explode((stristr(PHP_OS, 'WIN') ? '\\' : '/'), $call_info['file']);
        $file = array_pop($file);

        $id = substr(md5(microtime()), 0, 6);
        $return .= sprintf('<div class="debug"><div><div class="header" style="background-color: '.$color.';"></div>DEBUG! (<strong>%s : %s</strong>)', $file, $code_line);
            if ($info != false) {
                $return .= ' | <strong style="color: red;">'.$info.':</strong>';
            }
            $return .= '</div><ul id="debug_'.$id.'"'.($specialFX ? ' data-switch="true"' : '').'>'.doDump($var, '$'.$vname).'</ul>';
        $return .= '</div>';

        return $return;
    }

    /**
     * Internal function used with dump();
     *
     * @access      private
     * @version     3.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       variable     $var
     * @param       string      $var_name
     * @param       string      $indent
     * @param       string      $reference
     *
     * @return      string
     */
    function doDump(&$var, $var_name = null, $indent = null, $reference = null, $counter = 0) {
        $do_dump_indent = '&nbsp;&nbsp; ';
        $reference = $reference.$var_name;
        $keyvar = 'the_do_dump_recursion_protection_scheme';
        $keyname = 'referenced_object_name';
        $return = '';

        #if($counter >= 14){ return; }

        $return .= '<li>';
        if (is_array($var) && isset($var[$keyvar])) {
            $real_var = &$var[$keyvar];
            $real_name = &$var[$keyname];
            $type = ucfirst(gettype($real_var));
            $return .= $indent.$var_name.'<span class="ident">'.$type.'</span> = <span style="color:#e87800;">&amp;'.$real_name.'</span><br />';
        } else {
            $var = array($keyvar => $var, $keyname => $reference);
            $avar = &$var[$keyvar];

            $type = ucfirst(gettype($avar)); $type_color = '<span style="color:black">'; $color = 'black';
            if ($type == 'String') {
                $type_color = '<span style="color:green">';
                $color = 'green';
            } elseif($type == 'Integer') {
                $type_color = '<span style="color:red">';
                $color = 'red';
            } elseif($type == 'Double') {
                $type_color = '<span style="color:#0099c5">'; $type = 'Float';
                $color = '#0099c5';
            } elseif($type == 'Boolean') {
                $type_color = '<span style="color:#92008d">';
                $color = '#92008d';
            } elseif($type == 'null') {
                $type_color = '<span style="color:black">';
                $color = 'black';
            } elseif($type == 'Resource') {
                $type_color = '<span style="color:#00c19f">';
                $color = '#00c19f';
            }

            $keyNames = array('[\'password\']', '[\'pin\']');
            $avar = in_array($var_name, $keyNames) ? str_pad('', (strlen($avar)), '*') : $avar;

            if (is_array($avar)) {
                $count = count($avar);
                $return .= ''.$indent.($var_name ? $var_name.' => ' : '').'<span class="ident">'.$type.'('.$count.')</span>'.$indent.'<ul>(';
                $keys = array_keys($avar);
                foreach($keys as $name) {
                    $value = &$avar[$name];
                    $return .= doDump($value, '["'.$name.'"]', $indent.$do_dump_indent, $reference, 1);
                }
                $return .= ")</ul>";
            } elseif(is_object($avar)) {
                $return .= $indent.$var_name.' <span class="ident">'.$type.'</span>'.$indent.'<ul>(';
                $_indent = $indent.$do_dump_indent;
                foreach($avar as $key => $value){
                    $return .= doDump($value, "->". $key, $indent.$do_dump_indent, $reference, $counter++);
                }
                $return .= ")</ul>";
            } elseif(is_int($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.$type_color.$avar.'</span>';
            } elseif(is_string($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.
                                $type_color.'"'.str_replace(str_split("\t\n\r\0\x0B"), '', htmlspecialchars($avar)).'"</span>';
            } elseif(is_float($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.$type_color.$avar.'</span>';
            } elseif(is_bool($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.
                                $type_color.($avar == 1 ? 'true' : 'false').'</span>';
            } elseif(is_null($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.$type_color.'NULL</span>';
            } elseif(is_resource($avar)) {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'</span> '.$type_color.$avar.'</span>';
            } else {
                $return .= $indent.$var_name.' = <span class="ident">'.$type.'('.strlen($avar).')</span> '.
                                "$avar";
            }
            $var = $var[$keyvar];

        }
        $return .= '</li>';
        return $return;
    }
    /**
     * Determine where a function is being called from
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string     $info
     * @param   string  $nl
     *
     * @return  string
     */
    function getExecInfo($info = null, $nl = '<br />') {
        $a = debug_backtrace();

        $msg = array();
        $x = 0;
        foreach($a as $key => $file) {
            $msg[] = outputDebug($file, ($x==0 ? $info : null), $nl);
            $x++;
        }
        return implode('', $msg).$nl;
    }

    /**
     * Output a specfic iteration for getExecInfo
     *
     * @version    1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   array    $file
     * @param   string   $info
     * @param   string   $nl
     *
     * @return  string
     */
    function outputDebug($file, $info = null, $nl='<br />') {

        if( array_key_exists( 'file', $file ) ) {
            $filename      = explode((stristr(PHP_OS, 'WIN') ? '\\' : '/'), $file['file']);
            $filenameIndex = count($filename) - 1;
        }

        $title         = ( $info !== null                                          ? '<strong>['.$info.']</strong> <br />'          : null );
        $args          = ( isset( $file['args'] ) && !is_empty( $file['args'] )    ? htmlentities( json_encode( $file['args'] ) )   : null );
        $line          = ( isset( $file['line'] )                                  ? $file['line']                                  : '<i>Line Number Unknown</i>' );
        $function      = ( isset( $file['function'] )                              ? $file['function']                              : '<i>Function Name Unknown</i>' );
        $filename      = ( isset($filename) && isset( $filenameIndex, $filename )  ? $filename[$filenameIndex]                      : '<i>Filename Unknown</i>' );

        $msg = '%s Called on line <strong>%s</strong> of file <strong>%s</strong> via function <strong>%s</strong> with arguments (\'%s\')%s';

        $msg = sprintf( $msg,
            $title,
            $line,
            $filename,
            $function,
            $args,
            $nl
        );

        return $msg;
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
            'memory_exec' => memory_get_usage()
        );

        $start_time = time() + microtime();
        $start_code_line = $code_line;

        return $memoryUsage;
    }

    /**
     * Quickly generate a readable filesize
     *
     * @version  1.0
     * @since    1.0.0
     *
     * @param    int        $size
     *
     * @return   string
     */
    function formatBytes($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++){
            $size /= 1024;
        }

        return round($size, 2).$units[$i];
    }

?>