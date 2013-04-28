<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Admin_Modules_core extends Core_Classes_Module{

    public function __construct(){ }

    /**
     * Generates a form for the site configuration
     * NB this uses a modified version of Form::OutputForm()
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function tabbedConfig(array $tabs, array $desc, array $form){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Site Config' )
        ));

        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'settings' => cmsROOT.'modules/core/views/admin/config/settings.tpl',
            'form'     => cmsROOT.'modules/core/views/admin/config/formWrapper.tpl',
            'tabs'     => cmsROOT.'modules/core/views/tabs.tpl',
        ));

        // split the fields into tabs
        $tabs = array_reverse($tabs);

        if( isset($form['MSG_ERROR']) && !is_empty($form['MSG_ERROR']) ){
            $objTPL->assign_block_vars('form_error', array(
                'ERROR_MSG' => implode('<br />', $form['MSG_ERROR']),
            ));
        }

        if( isset($form['MSG_INFO']) && !is_empty($form['MSG_INFO']) ){
            $objTPL->assign_block_vars('form_info', array(
                'INFO_MSG' => $form['MSG_INFO'],
            ));
        }

        // generate the tpl setup for the tab content
        $tabCount = count($tabs); $i = 0;
        foreach( $tabs as $tab => $content ){

            $objTPL->reset_block_vars('_form_row');

            //loop thru each element
            foreach( $content as $label => $field ){
                if( is_empty($field) ){ continue; }

                $formVars = array();

                $objTPL->assign_block_vars('_form_row', array());
                // assign some vars to the template
                $objTPL->assign_block_vars('_form_row._field', array(
                    'F_ELEMENT'  => $header ? null : $field,
                    'F_INFO'     => (doArgs('parseDesc', false, $options) ? contentParse($desc[$label]) : $desc[$label]),
                    'CLASS'      => $header ? ' title' : ($count++%2 ? ' row_color2' : ' row_color1'),
                    'L_LABEL'    => $label,
                    'L_LABELFOR' => inBetween('name="', '"', $field),
                ));

                // output the label
                    $objTPL->assign_block_vars('_form_row._field._label', array());

                // if we have a description, lets output it with the label
                if( is_empty($desc[$label]) === false ){
                    $objTPL->assign_block_vars('_form_row._field._desc', array());
                }

                // see if we need to prepend or append anything to the field
                $pre = inBetween('data-prepend="', '"', $field);
                $app = inBetween('data-append="', '"', $field);

                if( !is_empty($pre) ){
                    $objTPL->assign_block_vars('_form_row._field._prepend', array('ADDON' => $pre));
                } else if( !is_empty($app) ){
                    $objTPL->assign_block_vars('_form_row._field._append', array('ADDON' => $app));
                }else{
                    $objTPL->assign_block_vars('_form_row._field._normal', array());

                }
            }

            $objTPL->parse('settings', false);
            $objTPL->assign_block_vars('tabs', array(
                'ID'      => seo($tab),
                'NAME'    => $tab,
                'CONTENT' => $objTPL->get_html('settings', false),
                'ACTIVE'  => ( ++$i == $tabCount ? ' active' : '' )
            ));
        }

        // the form needs infos
        $objTPL->assign_vars(array_merge($form, array(
            'FORM_CONTENT'=> $objTPL->get_html('tabs', false),
        )));

        $objTPL->parse('form', false);
        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   =>  'Site Configuration',
                'CONTENT' =>  $objTPL->get_html('form', false),
                'ICON'    =>  'fa-icon-wrench',
            ),
        ));

    }

}
?>