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
 * @author  Daniel Noel-Davies
 */
class Admin_Modules_core_modules extends Admin_Modules_core{

    public function modules( ) {
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objTPL     = Core_Classes_coreObj::getTPL();
        $objModule  = Core_Classes_coreObj::getModule();

        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
            'panel' => cmsROOT. 'modules/core/views/admin/modules/default/module_list.tpl',
        ));

        $files = glob( sprintf( '%smodules/*', cmsROOT ) );

        foreach( $files as $file ) { 

            $moduleName = str_replace( 'modules/', '', $file );

            // Determine the status of the module
            if( parent::moduleExists( $moduleName ) === false ) {
                continue;
            }

            $query = $objSQL->queryBuilder()
                        ->select('*')
                        ->from('#__modules')
                        ->where( 'name', '=', $moduleName )
                        ->build();

            $row             = $objSQL->fetchLine( $query );
            $moduleInstalled = parent::moduleInstalled( $moduleName );

            if( empty( $row ) || $moduleInstalled === false ) {
                
                $details = $objModule->getModuleDetails( $moduleName );

                if( !empty( $details ) ) {
                    $version = $details['version'];
                    $hash = $details['hash'];
                }
            }

            $objTPL->assign_block_vars( 'module', array(
                'NAME'        => $moduleName,
                'VERSION'     => $version,
                'HASH'        => $hash,
                'STATUS'      => ( $moduleInstalled === false ? 'Not Installed' : 'Installed' ),
                'STATUS_ICON' => ( $moduleInstalled === false ? 'default'         : 'success' ),
            ));
        }

        $objTPL->parse('panel', false);

        $objTPL->assign_block_vars('block', array(
            'TITLE'   => 'Module List',
            'CONTENT' => $objTPL->get_html('panel', false),
            'ICON'    => 'icon-th-list',
        ));

        $objTPL->parse('body', false);
    }


}


?>