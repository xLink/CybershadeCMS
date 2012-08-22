<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class page extends coreObj{

    static  $THEME      = '',
            $THEME_ROOT = '';
    public  $jsFiles    = array(),
            $cssFiles   = array(),
            $jsCode     = array(),
            $cssCode    = array(),
            $options    = array(),
            $acpMode    = false;

    public function __construct(){
        $this->options['simpleTPL'] = false;
        $this->options['pageTitle'] = '';
    }

/**
  //    
  //-- Setup Functions    
  //    
**/
    public function setOptions($key, $value){
        if(!empty($key) || !empty($value)){ return false; }

        $this->options[$key] = $value;

        return true;
    }

    public function getOptions($key){
        if(!empty($this->options) && array_key_exists($this->options, $key)){
            return $this->options[$key];
        }

        return false;
    }

    public function setTitle($title){
        $this->setOptions('pageTitle', secureMe($title)); 
    }

    public function setMenu($moduleName, $page_id='default'){
        $this->setOptions('moduleMenu',  array(
            'module'  => $moduleName, 
            'page_id' => $page_id,
        ));
    }

    public function addBreadcrumbs(array $value){
        $options = (is_array($this->getOptions('breadcrumbs')) ? $this->getOptions('breadcrumbs') : array());
        $this->setOptions('breadcrumbs', array_merge($options, $value));
    }


    public function addCSSFile(){
        $args = $this->_getArgs(func_get_args());

        $arg = func_get_arg(0);
        if(is_array($arg)){
            $css = $args;
        }else{
            $css = array(
                'src'  => doArgs(0, false, $args),
                'type' => doArgs(1, 'text/css', $args),
                'rel'  => doArgs(2, 'stylesheet', $args),
            );
        }

        if($css['src'] === false){ return false; }

        $file = explode(DS, $css['src']);
            if(array_key_exists(end($file), $this->cssFiles)){ return false; }

        $this->cssFiles[end($file)] = $css;

        return true;
    }
    public function addJSFile(){
    }
}

?>