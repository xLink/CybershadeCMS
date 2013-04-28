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
class Admin_Modules_core_siteconfig extends Admin_Modules_core{

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function siteConfig() {
        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        // generate the values we need
        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));

        $yn = array(1 => langVar('L_YES'), 0 => langVar('L_NO'));

        $this->tabbedConfig(
            array(
                'Site Config' => array(
                    langVar('L_SITE_TITLE')         => $objForm->inputbox('title', 'text', $this->config('site', 'title')),
                    langVar('L_SITE_SLOGAN')        => $objForm->inputbox('slogan', 'text', $this->config('site', 'slogan')),
                    langVar('L_ADMIN_EMAIL')        => $objForm->inputbox('admin_email', 'text', $this->config('site', 'admin_email')),
                    langVar('L_GANALYTICS')         => $objForm->inputbox('google_analytics', 'input', $this->config('site', 'google_analytics')),
                ),
                'Customize' => array(
                    // langVar('L_DEF_LANG')           => $objForm->select('language', $lang,
                    //                                     array('selected' => $this->config('site', 'language'))),
                    // langVar('L_SITE_TZ')            => $timezone,
                    langVar('L_DST')                => $objForm->radio('dst', $yn, $this->config('time', 'dst')),
                    langVar('L_DEF_DATE_FORMAT')    => $objForm->inputbox('default_format', 'input', $this->config('time', 'default_format')),
                ),
            ),
            array(
                'FORM_START'  => $objForm->start('siteConfig', array('method'=>'POST', 'action'=>'/'.root().'admin/core/siteconfig/save', 'class'=>'form-horizontal')),
                'FORM_END'    => $objForm->finish(),
                'FORM_TOKEN'  => $formToken,

                'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
                'FORM_RESET'  => $objForm->button('reset', 'Reset'),
                'FORM_CONTENT'=> $objTPL->get_html('tabs', false),

                'MSG_INFO'    => 'Yeah, this form wont do anything, if you submit, it\'ll show you what you submitted, but thats about it :D',
                'MSG_ERROR'   => '',
            )
        );

    }


    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function save( ) {
        echo dump($_POST);

        $this->siteConfig();


    }

/**
  //
  //-- Helper Functions
  //
**/

    /**
     * Generates a form for the site configuration
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @return  void
     */
    public function tabbedConfig($tabs, $form){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Site Config' )
        ));

        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();

        $objTPL->set_filenames(array(
            'tabs'     => cmsROOT.'modules/core/views/tabs.tpl',
            'form'     => cmsROOT.'modules/core/views/admin/config/formWrapper.tpl',
            'settings' => cmsROOT.'modules/core/views/admin/config/settings.tpl',
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
                    'CLASS'      => $header ? ' title' : ($count++%2 ? ' row_color2' : ' row_color1'),
                    'L_LABEL'    => $label,
                    'L_LABELFOR' => inBetween('name="', '"', $field),
                ));

                // output the label
                    $objTPL->assign_block_vars('_form_row._field._label', array());

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