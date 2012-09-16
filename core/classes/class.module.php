<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * This class handles everything modules!
 *
 * @version 2.0
 * @since   1.0.0
 * @author  Dan Aldridge
 */
class Module extends coreObj{
    public $modConf = array();

    function __construct() {

        $this->modConf['mode']      = doArgs('__mode',      null, $_GET);
        $this->modConf['module']    = doArgs('__module',    null, $_GET);
        $this->modConf['action']    = doArgs('__action',    null, $_GET);
        $this->modConf['extra']     = doArgs('__extra',     null, $_GET);

        // Retrieve info from config
        if(is_readable(cmsROOT . 'modules/' . $this->modConf['module'] . '/cfg.php')) {
            require_once cmsROOT . 'modules/' . $this->modConf['module'] . '/cfg.php';

            $this->modConf['path']  = '/' . root() . substr($mod_dir, 2);
        }

        $exAction                   = explode('/', $this->modConf['action']);
        $this->modConf['filename']  = (!is_empty($this->modConf['action']) && !is_empty($this->modConf['extra'])
                                            ? ($exAction[count($exAction)-1].$this->modConf['extra'])
                                            : '');
        $this->modConf['ext']       = ((substr_count($this->modConf['filename'], '.') > 0)
                                            ? (substr($this->modConf['filename'], strrpos($this->modConf['filename'], '.') + 1))
                                            : null);
        $this->modConf['action']    = $this->modConf['action'] . $this->modConf['extra'];
        $this->modConf['all']       = $this->modConf['path'] . $this->modConf['action'];

        $this->route();
    }

    /**
     * Check if the request is a media type, & output it if it is
     *
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    public function route(){

        //specify some deafult actions
        if(preg_match('/images\/(.*?)/i', str_replace($this->modConf['extra'], '', $this->modConf['action']))) {

            $imagesTypes = array('jpg', 'gif', 'png', 'jpeg', 'jfif', 'jpe', 'bmp', 'ico', 'tif', 'tiff');
            $filename = cmsROOT . 'modules/' . $this->modConf['module'] . '/images/' . $this->modConf['filename'];
                if(in_array($this->modConf['ext'], $imagesTypes) && is_readable($filename)) {

                    header('Content-Type: image/' . $this->modConf['ext']);
                    include ($filename);
                    exit;

                } else { $this->throwHTTP('404'); }
        }
        if(preg_match('/scripts\/(.*?)/i', str_replace($this->modConf['extra'], '', $this->modConf['action']))) {

            $filename = cmsROOT . 'modules/' . $this->modConf['module'] . '/' . $this->modConf['action'];
                if(file_exists($filename)) {

                    header('Content-type: text/javascript');
                    include ($filename);
                    exit;

                } else { $this->throwHTTP('404'); }
        }
        if(preg_match('/styles\/(.*?)/i', str_replace($this->modConf['extra'], '', $this->modConf['action']))) {

            $filename = cmsROOT . 'modules/' . $this->modConf['module'] . '/' . $this->modConf['action'];
                if(file_exists($filename)) {

                    header('Content-Type: text/css');
                    include ($filename);
                    exit;

                } else { $this->throwHTTP('404'); }
        }

        return false;
    }

    /**
     * Check if a module exists in the file structure
     *
     * @version 1.1
     * @since   1.0.0
     * @author  Jesus
     *
     * @param   string     $moduleName
     *
     * @return  bool
     */
    public function moduleExists($moduleName) {
        if(is_empty($moduleName) || !is_dir(cmsROOT . 'modules/' . $moduleName)) {
            return false;
        }

        $files = glob(cmsROOT.'modules/'.$moduleName.'/base.'.$moduleName.'.php');
            if(is_empty($files)) {
                return false;
            }
        return true;
    }
}

?>