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

    public function siteConfig(){
        Core_Classes_coreObj::getPage()->addBreadcrumbs(array(
            array( 'url' => doArgs('REQUEST_URI', '', $_SERVER), 'name' => 'Site Config' )
        ));

        $objForm    = Core_Classes_coreObj::getForm();
        $objTPL     = Core_Classes_coreObj::getTPL();


        $objTPL->set_filenames(array(
            'body'  => cmsROOT . Core_Classes_Page::$THEME_ROOT . 'block.tpl',
        ));

        $yn = array(1 => langVar('L_YES'), 0 => langVar('L_NO'));

            $fields = array(
                langVar('L_SITE_CONFIG')            => '_header_',
                    langVar('L_SITE_TITLE')         => $objForm->inputbox('title', 'text', $this->config('site', 'title')),
                    langVar('L_SITE_SLOGAN')        => $objForm->inputbox('slogan', 'text', $this->config('site', 'slogan')),
                    langVar('L_ADMIN_EMAIL')        => $objForm->inputbox('admin_email', 'text', $this->config('site', 'admin_email')),
                    langVar('L_GANALYTICS')         => $objForm->inputbox('google_analytics', 'input', $this->config('site', 'google_analytics')),

                langVar('L_CUSTOMIZE')              => '_header_',
                    // langVar('L_INDEX_MODULE')       => $objForm->select('index_module', $defaultModule,
                    //                                     array('disabled' => $tzDisable, 'selected' => $this->config('site', 'index_module'))),
                    // langVar('L_DEF_LANG')           => $objForm->select('language', $languages,
                    //                                     array('selected' => $this->config('site', 'language'))),
                    // langVar('L_DEF_THEME')          => $objForm->select('theme', $tpl,
                    //                                     array('selected' => $this->config('site', 'theme'))),
                    langVar('L_THEME_OVERRIDE')     => $objForm->radio('theme_override', $yn, $this->config('site', 'theme_override')),
                    langVar('L_SITE_TZ')            => $timezone,
                    langVar('L_DST')                => $objForm->radio('dst', $yn, $this->config('time', 'dst')),
                    langVar('L_DEF_DATE_FORMAT')    => $objForm->inputbox('default_format', 'input', $this->config('time', 'default_format')),
            );

        $form = $objForm->outputForm(array(
            'FORM_START'    => $objForm->start('panel', array('method' => 'POST', 'action' => $saveUrl, 'class' => 'form-horizontal')),
            'FORM_END'      => $objForm->finish(),

            'FORM_TITLE'    => $mod_name,
            'FORM_SUBMIT'   => $objForm->button('submit', 'Submit', array( 'class' => 'btn-primary' )),
            'FORM_RESET'    => $objForm->button('reset', 'Reset'),

            'HIDDEN'        => $objForm->inputbox('sessid', 'hidden', $sessid).$objForm->inputbox('id', 'hidden', $uid),
        ),
        array(
            'field' => $fields,
            'desc' => array(
                    langVar('L_INDEX_MODULE')       => langVar('L_DESC_IMODULE'),
                    langVar('L_SITE_TZ')            => langVar('L_DESC_SITE_TZ'),
                    langVar('L_DEF_DATE_FORMAT')    => langVar('L_DESC_DEF_DATE'),
                    langVar('L_DEF_THEME')          => langVar('L_DESC_DEF_THEME'),
                    langVar('L_THEME_OVERRIDE')     => langVar('L_DESC_THEME_OVERRIDE'),
                    langVar('L_ALLOW_REGISTER')     => langVar('L_DESC_ALLOW_REGISTER'),
                    langVar('L_EMAIL_ACTIVATE')     => langVar('L_DESC_EMAIL_ACTIVATE'),
                    langVar('L_MAX_LOGIN_TRIES')    => langVar('L_DESC_MAX_LOGIN'),
                    langVar('L_REMME')              => langVar('L_DESC_REMME'),
                    langVar('L_GANALYTICS')         => langVar('L_DESC_GANALYTICS'),
            ),
            'errors' => $_SESSION['site']['panel']['error'],
        ),
        array(
            'header' => '<h4>%s</h4>',
            'dedicatedHeader' => true,
            'parseDesc' => true,
        ));           

        Core_Classes_coreObj::getAdminCP()->setupBlock('body', array(
            'cols'  => 3,
            'vars'  => array(
                'TITLE'   =>  'Site Configuration',
                'CONTENT' =>  $form,
                'ICON'    =>  'fa-icon-user',
            ),
        ));

    }


}


?>