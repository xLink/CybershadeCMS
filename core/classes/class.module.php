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