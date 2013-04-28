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
        $objPage    = Core_Classes_coreObj::getPage();

        // we got a post back here, lets gogo and see if we can save
        if( HTTP_POST ){
            $failed = $this->siteConfig_save();
                if( !count($failed) ){
                    $objPage->redirect( '/'.root().'admin/core/siteconfig' );
                    exit;
                }
        }

        // generate the values we need
        $formToken = $objForm->inputbox('form_token', 'hidden', Core_Classes_coreObj::getSession()->getFormToken(true));

            $yn = array(1 => langVar('L_YES'), 0 => langVar('L_NO'));

            //generate a select box for the timezones
            $timezone_array = array(
                '-12.0', '-11.0',
                '-10.0', '-9.0', '-8.0', '-7.0', '-6.0', '-5.0',
                '-4.0', '-3.5', '-2.0', '-1.0', '0.0',
                '1.0', '2.0', '3.0', '3.5', '4.0', '4.5', '5.0',
                '5.5', '6.0', '6.5', '7.0', '8.0', '9.0', '9.5', '10.0',
                '11.0', '12.0'
            );

            array_walk($timezone_array, function(&$item, $key){
                $item = 'GMT '.$item;
            });

            // acquire a list of themes
            $lang = array();
            $dir = cmsROOT.'languages';
            foreach( getFiles($dir) as $file ){
                if( in_array($file, array('.', '..', 'index.php', 'index.html') )){ continue; }

                $path = $dir.'/'.$file['name'].'/details.php';
                if( file_exists($path) && is_readable($path) ){
                    include_once($path);

                    $className = 'Details_Lang_'.( str_replace('-', '_', $file['name']) );
                    $objLang = new $className;

                    if( !class_exists($className) ){ continue; }
                    $details = $objLang->details();
                    $lang[ $file['name'] ] = $details['name'];
                }
            } unset($objLang);


            $loginTries = array();
            for($x=0; $x<=10; $x++){
                $loginTries[] = $x;
            }


        $this->tabbedConfig(
            array(
                'Site Config' => array(
                    langVar('L_SITE_TITLE')         =>  $objForm->inputbox('site[title]', 'text', $this->config('site', 'title')),
                    langVar('L_SITE_SLOGAN')        =>  $objForm->inputbox('site[slogan]', 'text', $this->config('site', 'slogan')),
                    langVar('L_ADMIN_EMAIL')        =>  $objForm->inputbox('site[admin_email]', 'text', $this->config('site', 'admin_email')),
                    'Site Description'              =>  $objForm->textarea('site[description]', $this->config('site', 'description'), array(
                                                            'style' => 'width: 40%;',
                                                        )),
                    'Site Keywords'                 =>  $objForm->textarea('site[keywords]', $this->config('site', 'keywords'), array(
                                                            'style' => 'width: 40%;',
                                                        )),
                ),
                'Customize' => array(
                    langVar('L_DEF_LANG')           =>  $objForm->select('site[language]', $lang, array(
                                                            'selected' => $this->config('site', 'language')
                                                        )),
                    langVar('L_SITE_TZ')            =>  $objForm->select('site[timezone]', $timezone_array, array(
                                                            'selected' => 'GMT '.$this->config('time', 'timezone'),
                                                            'noKeys'   => true
                                                        )),

                    langVar('L_DST')                =>  $objForm->radio('time[dst]', $yn, $this->config('time', 'dst')),
                    langVar('L_DEF_DATE_FORMAT')    =>  $objForm->inputbox('time[default_format]', 'input', $this->config('time', 'default_format')),

                    langVar('L_GANALYTICS')         =>  $objForm->inputbox('site[google_analytics]', 'input', $this->config('site', 'google_analytics')),
                ),
                'Login & Reg' => array(
                    'Editable Nicknames'            =>  $objForm->radio('site[change_username]', $yn, $this->config('site', 'change_username')),
                    langVar('L_ALLOW_REGISTER')     =>  $objForm->radio('login[allow_register]', $yn, $this->config('login', 'allow_register')),
                    langVar('L_REMME')              =>  $objForm->radio('login[remember_me]', $yn, $this->config('login', 'remember_me')),
                    langVar('L_MAX_LOGIN_TRIES')    =>  $objForm->select('login[max_login_tries]', $loginTries, array(
                                                            'selected' => $this->config('login', 'max_login_tries'),
                                                            'noKeys'   => true
                                                        )),


                ),
                'Maintenance' => array(
                    langVar('L_DISABLE_SITE')       =>  $objForm->radio('site[site_closed]', $yn, $this->config('site', 'site_closed')),
                    langVar('L_DISABLE_MSG')        =>  $objForm->textarea('site[closed_msg]', $this->config('site', 'closed_msg'), array(
                                                            'style'    => 'width: 98%; height: 100px;'
                                                        )),
                ),
            ),
            array(
                langVar('L_SITE_TZ')            =>  langVar('L_DESC_SITE_TZ'),
                langVar('L_DEF_DATE_FORMAT')    =>  langVar('L_DESC_DEF_DATE'),

                langVar('L_GANALYTICS')         =>  langVar('L_DESC_GANALYTICS'),

                langVar('L_ALLOW_REGISTER')     =>  langVar('L_DESC_ALLOW_REGISTER'),
                langVar('L_EMAIL_ACTIVATE')     =>  langVar('L_DESC_EMAIL_ACTIVATE'),
                langVar('L_MAX_LOGIN_TRIES')    =>  langVar('L_DESC_MAX_LOGIN'),
                langVar('L_REMME')              =>  langVar('L_DESC_REMME'),
            ),
            array(
                'FORM_START'  => $objForm->start('siteConfig', array('method'=>'POST', 'action' => '/'.root().'admin/core/siteconfig', 'class'=>'form-horizontal')),
                'FORM_END'    => $objForm->finish(),
                'FORM_TOKEN'  => $formToken,

                'FORM_SUBMIT' => $objForm->button('submit', 'Submit', array('class' => 'btn btn-info')),
                'FORM_RESET'  => $objForm->button('reset', 'Reset'),

                'MSG_ERROR'   => $failed,
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
    public function siteConfig_save( ) {
        echo dump($_POST);
        if( !HTTP_POST ){ return false; }

        if( !Core_Classes_coreObj::getSession()->checkToken('form_token') ){
            return array('token' => 'Token: Could not resubmit form, please try again.');
        }

        $objSQL = Core_Classes_coreObj::getDBO();
        $objCache = Core_Classes_coreObj::getCache();

        $failed = array();
        foreach( $_POST as $key => $values ){
            if(!is_array($values)){ continue; }

            foreach( $values as $k => $v ){
                if( strlen($v) == 0 ){ $v = 'NULL'; }

                $query = $objSQL->queryBuilder()
                    ->replaceInto('#__config')
                    ->set(array(
                        'key'   => $key,
                        'var'   => $k,
                        'value' => $v,
                    ));

                $results = $objSQL->query( $query->build() );
                    if( $results === false ){
                        $failed[$key.'['.$k.']'] = $objSQL->getError();
                    }
            }
        }

        $objCache->regenerateCache('config');

        return $failed;
    }


}


?>