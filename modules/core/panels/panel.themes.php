<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Core ACP Panel
 *
 * @version 1.0
 * @since   1.0.0
 * @author  Dan Aldridge
 */
class Admin_Modules_core_themes extends Admin_Modules_core{

    public function __construct(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => '/'.root().'admin/core/themes/', 'name' => 'Themes' )
        ));
        
    }

    public function themes(){
        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'table'  => cmsROOT . 'modules/core/views/admin/themes/manageTable.tpl',
        ));

        $dir = cmsROOT.'themes';
        $tpls = getFiles($dir);

        //echo dump($tpls);

        foreach( $tpls as $tpl ){
            if( $tpl['type'] !== 'dir' ){ continue; }

            $tplName = secureMe($tpl['name'], 'alphanum');
            $details = $this->getDetails( $tplName );
//echo dump($details, $tplName);
            $objTPL->assign_block_vars('theme', array(
                'NAME'      => doArgs('name', 'N/A', $details),
                'VERSION'   => doArgs('version', '0.0', $details),
                'ENABLED'   => 'true',
                'COUNT'     => '9001',
                'MODE'      => doArgs('mode', 'N/A', $details),
                'AUTHOR'    => doArgs('author', 'N/A', $details),
            ));
        }





        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Theme Management',
            'CONTENT' => $objTPL->get_html('table', false),
            'ICON'    => 'faicon-user',
        ));

        $objTPL->parse('body', false);

    }

    /**
     * Retrieve the themes details file
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies, Dan Aldridge
     *
     * @param   string     $themeName
     *
     * @return  array
     */
    public static function getDetails( $themeName ) {
        $detailsFile = sprintf( '%1$sthemes/%2$s/details.php', cmsROOT, $themeName );
        $detailsClassName = sprintf( 'Details_%s', str_replace('-', '_', $themeName) );

        // Make sure the details file exists
        if( file_exists( $detailsFile ) === false ) {
            trigger_error( 'Error getting Module Details :: Details file doesn\'t exist' );
            return false;
        }

        require_once( $detailsFile );
        return reflectMethod( $detailsClassName, 'details' );
    }

}


?>