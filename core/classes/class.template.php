<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


/**
 * Sets up the templating system
 *
 * @version     2.1
 * @since       1.0.0
 * @author      phpBB Team
 */
class template extends coreObj{

    // variable that holds all the data we'll be substituting into
    // the compiled templates.
    // ...
    // This will end up being a multi-dimensional array like this:
    // $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
    // if it's a root-level variable, it'll be like this:
    // $this->_tpldata[.][0][varname] == value
    public $_tpldata = array();

    // Hash of filenames for each template handle.
    public $files = array();

    // Root template directory.
    public $root = "./";

    // this will hash handle names to the compiled code for that handle.
    private $compiled_code = array();

    // This will hold the uncompiled code for that handle.
    private $uncompiled_code = array();

    // switch to decide if we are going to use the cache
    private $use_cache = true;
    private $_cache_directory = "";

    public $__eval;

    function __construct($name='', $args=array()) {
        $args = array(
            'useCache' => doArgs('useCache', false, $args),
            'cacheDir' => doArgs('cacheDir', '', $args),
            'root'     => doArgs('root', '.', $args),
        );

        if(!$this->set_rootdir($args['root'])) {
        	trigger_error('Error: Unable to find template root directory', E_USER_ERROR);
        }

        $this->use_cache = $args['useCache'];
        if($this->use_cache) {
            if(is_dir($args['cacheDir']) && is_writeable($args['cacheDir'])) {
                $this->cache_directory = $args['cacheDir'];
            } else {
                $this->cache_directory = $args['root'].'/cache/template/';
            }
        }
    }

    function isHandle($handle) {
        return !isset($this->files[$handle]) ? false : true;
    }

    function reset_block_vars($arrVar) {
        unset($this->_tpldata[$arrVar.'.']);
    }

    /**
     * Destroys this template object. Should be called when you're done with it, in order
     * to clear out the template data so you can load/parse a new template set.
     */
    function __destruct() {
        $this->_tpldata = array();
    }

    /**
     * Sets the template root directory for this Template object.
     */
    public function set_rootdir($dir) {
        if(!is_dir($dir)) {
            return false;
        }

        $this->root = $dir;
        return true;
    }

    /**
     * Sets the template filenames for handles. $filename_array
     * should be a hash of handle => filename pairs.
     */
    public function set_filenames($filename_array) {
        reset($filename_array);

        foreach($filename_array as $handle => $filename) {
            $this->files[$handle] = $this->make_filename($filename, $handle);
        }

        return true;
    }

    /**
     * Load the file for the handle, compile the file,
     * and run the compiled code. This will print out
     * the results of executing the template.
     */
    public function parse($handle, $echo=true) {
        $code = '';

        if(!$this->isHandle($handle)){ 
            trigger_error('"'.$handle.'" is not a valid file handle.');
        }
        
        if($this->use_cache) {
            $code = $this->get_cached_code($handle);
        } else {
            if(!$this->loadfile($handle)) {
               trigger_error('parse(): Couldn\'t load template file for handle "'.$handle.'"');
            }
            // actually compile the template now.
            if(!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle])) {
                // Actually compile the code now.
                $this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle]);
            }
            $code = $this->compiled_code[$handle];
        }

        if(substr($code, 0, 5) == '$_str') $code .= ' echo $_str;';
        if($echo) {
            eval($code);
            return true;
        } else {
            $this->__eval[$handle] = $code;
            return;
        }
    }

    public function parseString($handle, $string, $echo=true){
        if(is_empty($handle) || is_empty($string)){
            trigger_error('Error: Invalid arguments passed to parseString())');
        }

        if(isset($this->uncompiled_code[$handle])){
            trigger_error('Error: Code Handle already set for '.$handle);
        }

        $this->uncompiled_code[$handle] = $string;
        if($this->use_cache){
            $this->loadfile[$handle] = false;
            $code = $this->get_cached_code($handle);
        } else {
            if(!isset($this->compiled_code[$handle]) || is_empty($this->compiled_code[$handle])){
                $this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle]);
            }
            $code = $this->compiled_code[$handle];
        }

        if(substr($code, 0, 5) == '$_str') $code .= ' echo $_str;';
        if($echo) {
            eval($code);
            return true;
        } else {
            $this->__eval[$handle] = $code;
            return;
        }
    }

    public function output($handle, $echo=true){
        if(isset($this->__eval[$handle])){
            $code = $this->__eval[$handle];
            if($echo!==true){
                $code = str_replace('echo \'', '$_str.= \'', $code);
            }

            eval(str_replace('echo $_str;', '', $code));

            return ($echo===true ? false : $_str);
        }
        return true;
    }

    public function get_html($handle) {
        if(!$this->loadfile($handle)) {
            msgDie('FAIL', "Template->get_html(): Couldn't load template file for handle $handle @ Line ".__line__);
        }

        // Compile it, with the "no echo statements" option on.
        $_str = "";
        $code = '';

        if($this->use_cache) {
            $code = $this->get_cached_code($handle, true, '_str');
        } else {
            $code = $this->compile($this->uncompiled_code[$handle], true, '_str');
        }

        // evaluate the variable assignment.
        eval(str_replace('echo \'', '$_str .= \'', $code));
        // assign the value of the generated variable to the given varname.
        return $_str;
    }

    /**
     * Inserts the uncompiled code for $handle as the
     * value of $varname in the root-level. This can be used
     * to effectively include a template in the middle of another
     * template.
     * Note that all desired assignments to the variables in $handle should be done
     * BEFORE calling this function.
     */
    public function assign_var_from_handle($varname, $handle) {
        if(!$this->loadfile($handle)) {
            msgDie('FAIL', "Template->assign_var_from_handle(): Couldn't load template file for handle $handle @ Line ".__LINE__);
        }

        // Compile it, with the "no echo statements" option on.
        $_str = "";
        $code = '';

        if($this->use_cache) {
            $code = $this->get_cached_code($handle, true, '_str');
        } else {
            $code = $this->compile($this->uncompiled_code[$handle], true, '_str');
        }

        $code = str_replace('echo \'', '$_str.= \'', $code);

        // evaluate the variable assignment.
        eval($code);
        // assign the value of the generated variable to the given varname.
        $this->assign_var($varname, $_str);

        return true;
    }

    /**
     * Block-level variable assignment. Adds a new block iteration with the given
     * variable assignments. Note that this should only be called once per block
     * iteration.
     */
    public function assign_block_vars($blockname, $vararray) {
        if(strstr($blockname, '.')) {
            // Nested block.
            $blocks = explode('.', $blockname);
            $blockcount = sizeof($blocks) - 1;
            $str = '$this->_tpldata';
            for($i = 0; $i < $blockcount; $i++) {
                $str .= '[\''.$blocks[$i].'.\']';
                eval('$lastiteration = sizeof('.$str.') - 1;');
                $str .= '['.$lastiteration.']';
            }
            // Now we add the block that we're actually assigning to.
            // We're adding a new iteration to this block with the given
            // variable assignments.
            $str .= '[\''.$blocks[$blockcount].'.\'][] = $vararray;';

            // Now we evaluate this assignment we've built up.
            eval($str);
        } else {
            // Top-level block.
            // Add a new iteration to this block with the variable assignments
            // we were given.
            $this->_tpldata[$blockname.'.'][] = $vararray;
        }

        return true;
    }

    /**
     * Root-level variable assignment. Adds to current assignments, overriding
     * any existing variable assignment with the same name.
     */
    public function assign_vars($vararray){
        if(!is_array($vararray) || is_empty($vararray)){ return false; }
        reset($vararray);
        while(list($key, $val) = each($vararray)) {
            $this->_tpldata['.'][0][$key] = $val;
        }

        return true;
    }

    /**
     * Root-level variable assignment. Adds to current assignments, overriding
     * any existing variable assignment with the same name.
     */
    public function assign_var($varname, $varval) {
        $this->_tpldata['.'][0][$varname] = $varval;
        return true;
    }

    public function setOverride($handle, $value = false) {
        $this->override[$handle] = $value;
    }

    /**
     * Generates a full path+filename for the given filename, which can either
     * be an absolute name, or a name relative to the rootdir for this Template
     * object.
     */
    private function make_filename($filename, $handle) {
        // check to see if its a remote template
        $from_http = 0;
        $fname = $filename;
        if(strtolower( substr( $filename, 0 ,4 )) == 'http'){
            $from_http = 1;
        }

        if(!$from_http){
            // Check if it's an absolute or relative path.
            if(substr($filename, 0, 1) != '/'){
                // this allows loading of a template by url
                $filename = realpath($this->root.'/'.$filename);
            }

        }

        return $filename;
    }

    /**
     * If not already done, load the file for the given handle and populate
     * the uncompiled_code[] hash with its code. Do not compile.
     */
    private function loadfile($handle) {
        #echo dump($handle);
        // If the file for this handle is already loaded and compiled, do nothing.
        if(isset($this->uncompiled_code[$handle]) && !empty($this->uncompiled_code[$handle])) {
            return true;
        }

        // If we don't have a file assigned to this handle, die.
        if(!isset($this->files[$handle])) {
            msgdie('FAIL', "Template->loadfile(): No file specified for handle $handle @ Line ".__line__);
        }

        $filename = $this->files[$handle];

        $str = implode('', @file($filename));
        if(empty($str)) {
            msgdie('FAIL', "Template->loadfile(): File $filename for handle $handle is empty @ Line ".__line__);
        }

        $this->uncompiled_code[$handle] = $str;

        return true;
    }

    /**
     * Compiles the given string of code, and returns
     * the result in a string.
     * If "do_not_echo" is true, the returned code will not be directly
     * executable, but can be used as part of a variable assignment
     * for use in assign_code_from_handle().
     */
    public function compile($code, $do_not_echo = false, $retvar = '') {
        // replace \ with \\ and then ' with \'.
        $code = str_replace('\\', '\\\\', $code);
        $code = str_replace('\'', '\\\'', $code);

        // change template varrefs into PHP varrefs

        // This one will handle varrefs WITH namespaces
        $varrefs = array();
        preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $code, $varrefs);
        $varcount = sizeof($varrefs[1]);
        for($i = 0; $i < $varcount; $i++) {
            $namespace = $varrefs[1][$i];
            $varname = $varrefs[3][$i];
            $new = $this->generate_block_varref($namespace, $varname);

            $code = str_replace($varrefs[0][$i], $new, $code);
        }

        // This will handle the remaining root-level varrefs
        $code = preg_replace('#\{([a-z0-9\-_]*?)\}#is', '\'.((isset($this->_tpldata[\'.\'][0][\'\1\'])) ? $this->_tpldata[\'.\'][0][\'\1\'] : \'\').\'', $code);

        // Break it up into lines.
        $code_lines = explode("\n", $code);

        $block_nesting_level = 0;
        $block_names = array();
        $block_names[0] = '.';

        // Second: prepend echo ', append ' . "\n"; to each line.
        $line_count = sizeof($code_lines);
        for($i = 0; $i < $line_count; $i++) {
            $code_lines[$i] = rtrim($code_lines[$i]);
            if(preg_match('#<!-- BEGIN (.*?) -->#', $code_lines[$i], $m)) {
                $n[0] = $m[0];
                $n[1] = $m[1];

                // Added: dougk_ff7-Keeps templates from bombing if begin is on the same line as end.. I think. :)
                if(preg_match('#<!-- END (.*?) -->#', $code_lines[$i], $n)) {
                    $block_nesting_level++;
                    $block_names[$block_nesting_level] = $m[1];
                    if($block_nesting_level < 2) {
                        // Block is not nested.
                        $code_lines[$i] = '$_'.$n[1].'_count = (isset($this->_tpldata[\'' . $n[1] . '.\']) ? sizeof($this->_tpldata[\''.$n[1].'.\']) : 0);';
                        $code_lines[$i] .= "\n".'for ($_'.$n[1].'_i = 0; $_'.$n[1].'_i < $_'.$n[1].'_count; $_'.$n[1].'_i++){';
                    } else {
                        // This block is nested.

                        // Generate a namespace string for this block.
                        $namespace = implode('.', $block_names);
                        // strip leading period from root level..
                        $namespace = substr($namespace, 2);
                        // Get a reference to the data array for this block that depends on the
                        // current indices of all parent blocks.
                        $varref = $this->generate_block_data_ref($namespace, false);
                        // Create the for loop code to iterate over this block.
                        $code_lines[$i] = '$_'.$n[1].'_count = (isset('.$varref.') ? sizeof('.$varref.') : 0);';
                        $code_lines[$i] .= "\n".'for ($_'.$n[1].'_i=0; $_'.$n[1].'_i<$_'.$n[1].'_count; $_'.$n[1].'_i++){';
                    }

                    // We have the end of a block.
                    unset($block_names[$block_nesting_level]);
                    $block_nesting_level--;
                    $code_lines[$i] .= '} // END '.$n[1];
                    $m[0] = $n[0];
                    $m[1] = $n[1];
                } else {
                    // We have the start of a block.
                    $block_nesting_level++;
                    $block_names[$block_nesting_level] = $m[1];
                    if($block_nesting_level < 2) {
                        // Block is not nested.
                        $code_lines[$i] = '$_'.$m[1].'_count = (isset($this->_tpldata[\''.$m[1].'.\']) ? sizeof($this->_tpldata[\''.$m[1].'.\']) : 0);';
                        $code_lines[$i] .= "\n".'for ($_'.$m[1].'_i=0; $_'.$m[1].'_i<$_'.$m[1].'_count; $_'.$m[1].'_i++){';
                    } else {
                    // This block is nested.
                        // Generate a namespace string for this block.
                        $namespace = implode('.', $block_names);
                        // strip leading period from root level..
                        $namespace = substr($namespace, 2);
                        // Get a reference to the data array for this block that depends on the
                        // current indices of all parent blocks.
                        $varref = $this->generate_block_data_ref($namespace, false);
                        // Create the for loop code to iterate over this block.
                        $code_lines[$i] = '$_'.$m[1].'_count = (isset('.$varref.') ? sizeof('.$varref.') : 0);';
                        $code_lines[$i] .= "\n".'for ($_'.$m[1].'_i=0; $_'.$m[1].'_i<$_'.$m[1].'_count; $_'.$m[1].'_i++){';
                    }
                }
            } else if(preg_match('#<!-- END (.*?) -->#', $code_lines[$i], $m)) {
                // We have the end of a block.
                unset($block_names[$block_nesting_level]);
                $block_nesting_level--;
                $code_lines[$i] = '} // END '.$m[1];
            } else {
                // We have an ordinary line of code.
                if($do_not_echo !== true) {
                    $code_lines[$i] = 'echo \''.$code_lines[$i].'\'."\\n";';
                } else {
                    $code_lines[$i] = '$'.$retvar.' .= \''.$code_lines[$i].'\'."\\n";';
                }
            }
        }
        // Bring it back into a single string of lines of code.
        $code = implode("\n", $code_lines);
        return $code;

    }

    /**
     * Generates a reference to the given variable inside the given (possibly nested)
     * block namespace. This is a string of the form:
     * ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
     * It's ready to be inserted into an "echo" line in one of the templates.
     * NOTE: expects a trailing "." on the namespace.
     */
    private function generate_block_varref($namespace, $varname) {
        // Strip the trailing period.
        $namespace = substr($namespace, 0, strlen($namespace) - 1);

        // Get a reference to the data block for this namespace.
        $varref = $this->generate_block_data_ref($namespace, true);
        // Prepend the necessary code to stick this in an echo line.

        // Append the variable reference.
        $varref .= '[\''.$varname.'\']';
        $varref = '\'.(isset('.$varref.') ? '.$varref.' : \'\').\'';
        return $varref;
    }

    /**
     * Generates a reference to the array of data values for the given
     * (possibly nested) block namespace. This is a string of the form:
     * $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
     *
     * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
     * NOTE: does not expect a trailing "." on the blockname.
     */
    private function generate_block_data_ref($blockname, $include_last_iterator) {
        // Get an array of the blocks involved.
        $blocks = explode('.', $blockname);
        $blockcount = sizeof($blocks) - 1;
        $varref = '$this->_tpldata';
        // Build up the string with everything but the last child.
        for($i = 0; $i < $blockcount; $i++) {
            $varref .= '[\''.$blocks[$i].'.\'][$_'.$blocks[$i].'_i]';
        }
        // Add the block reference for the last child.
        $varref .= '[\''.$blocks[$blockcount].'.\']';
        // Add the iterator for the last child if requried.
        if($include_last_iterator) {
            $varref .= '[$_'.$blocks[$blockcount].'_i]';
        }

        return $varref;
    }

    /*
    Checks to see if the cached version of the compiled code exists in the cache directory and if its up to date
    if so it retrieves the cached version if not it attempts to create a cached version of the template and returns the code
    */
    private function get_cached_code($handle, $do_not_echo = false, $ret_var = '') {

        $_cache_file = $this->cache_directory.'tpl_'.(isset($this->files[$handle]) ? md5($this->files[$handle]) : md5(md5($handle)));

        $create_new_cache = 1;
        if($_cache_present = file_exists($_cache_file)) {
            $_cache_stat = stat($_cache_file);
            $_template_stat = stat($this->files[$handle]);
            $create_new_cache = ($_cache_stat['mtime'] < $_template_stat['mtime']) ? 1 : 0;
        }

        $code = '';
        if($create_new_cache) {
            if(!isset($this->loadFile[$handle])){
                if(!$this->loadfile($handle)) {
                    msgdie('FAIL', "Template->parse(): Couldn't load template file for handle $handle @ Line ".__line__);
                }
            }

            // actually compile the template now.
            if(!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle])) {
                // Actually compile the code now.
                $code = $this->compile($this->uncompiled_code[$handle], $do_not_echo, $ret_var);
                $this->compiled_code[$handle] = $code;
            }

            $fh = fopen($_cache_file, 'w');
            fwrite($fh, $code);
            fclose($fh);

        } else {
            $code = file_get_contents($_cache_file);
        }

        return $code;
    }
}
?>