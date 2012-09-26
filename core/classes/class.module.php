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

    public $tplSet  = false;
    public $_module = false;
    public $_method = false;

    public function setView($view='default'){
        $objTPL = coreObj::getTPL();

        $module = $this->getVar('_module');
        $method = $this->getVar('_method');
        $view   = str_replace('.tpl', '', $view);

        if(is_empty($view)){
            trigger_error('You did not set a view for this, cant see it going well ;/');
        }

        // Allow Developers to test custom views
        if( !empty( $_GET['view'] ) ) { // @TODO Add && IS_ADMIN
            $tempPath = sprintf('modules/%s/views/%s/%s.tpl', $module, $method, $_GET['view']);
            if( is_readable( $tempPath ) ) {
                $view = $_GET['view'];
            } else {
                trigger_error('The view overide you attempted to use dow work', E_USER_ERROR);
            }
        }

        $path = sprintf('modules/%s/views/%s/%s.tpl', $module, $method, $view);
        if(!is_file($path)){
            trigger_error($path.' is not a valid path', E_USER_ERROR);
        }

        $objTPL->set_filenames(array(
            'body' => $path,
        ));

        $this->setVar('tplSet', true);
    }

    public function __destruct(){
        if($this->getVar('tplSet') !== true){ return false; }
        $objTPL = coreObj::getTPL();

        $objTPL->parse('body', false);
    }

    /**
     * Check if a module exists in the file structure
     *
     * @version 1.1
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   string     $moduleName
     *
     * @return  bool
     */
    public function moduleExists( $moduleName ) {
        if( is_empty( $moduleName ) || !is_dir( cmsROOT . 'modules/' . $moduleName ) ) {
            return false;
        }

        $files = glob( cmsROOT.'modules/'.$moduleName.'/base.'.$moduleName.'.php' );
            if( is_empty( $files ) ) {
                return false;
            }
        return true;
    }
}

?>