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

    /**
     * Set the view for the method.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $view
     *
     * @return  bool
     */
    public function setView($view='default'){
        $objTPL = coreObj::getTPL();

        $module = $this->getVar('_module');
        $method = $this->getVar('_method');
        $view   = str_replace('.tpl', '', $view);

        if( is_empty($view) ){
            trigger_error('You did not set a view for this method.');
            return false;
        }

        // Allow Developers to test custom views
        if( !empty( $_GET['view'] ) ) { // @TODO Add && IS_ADMIN
            $tempPath = sprintf('modules/%s/views/%s/%s.tpl', $module, $method, $_GET['view']);
            if( is_readable( $tempPath ) ) {
                $view = $_GET['view'];
            } else {
                trigger_error('The view overide you attempted to use dow work');
                return false;
            }
        }

        $path = sprintf('modules/%s/views/%s/%s.tpl', $module, $method, $view);
        if( !is_file($path) ){
            trigger_error($path.' is not a valid path');
            return false;
        }

        $objTPL->set_filenames(array(
            'body' => $path,
        ));

        $this->setVar('viewSet', true);
        return true;
    }

    /**
     * If the view has been set we will parse the body and go from there.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function __destruct(){
        // if view hasnt been set, then we dont want to continue
        if( $this->getVar('viewSet') !== true ){
            return false;
        }
        $objTPL = coreObj::getTPL();

        // if the handle isnt valid, then return
        if( !$objTPL->isHandle('body') ){
            return false;
        }

        //parse the body and store it for later use
        $objTPL->parse('body', false);
    }

    /**
     * Executes if a method has been called to & it dosen't exist
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $method
     * @param   array  $args
     */
    final public function __call($method, $args){
        $debug = array(
            'Class Name'    => $this->getClassName(),
            'Method Called' => $method,
            'Method Args'   => $args,
        );
        trigger_error('Error: Method dosen\'t exist.'.dump($debug));
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
        if( is_empty( $moduleName ) || !is_dir( sprintf( '%smodules/%s', cmsROOT, $moduleName ) ) ) {
            return false;
        }

        $files = glob( sprintf( '%1$smodules/%2$s/base%2$s.php', cmsROOT, $moduleName ) );
            if( is_empty( $files ) ) {
                return false;
            }
        return true;
    }
}

?>