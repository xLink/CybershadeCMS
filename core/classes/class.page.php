<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Page extends coreObj{

    static  $THEME      = '',
            $THEME_ROOT = '';

    public  $jsFiles     = array(),
            $cssFiles    = array(),
            $jsCode      = array(),
            $cssCode     = array(),
            $metaTags    = array(),
            $options     = array(),
            $breadcrumbs = array(),
            $acpMode     = false;

    public function __construct(){
        $this->options['simpleTPL'] = false;
    }

/**
  //
  //-- Setup Functions
  //
**/

    /**
     * Sets a key in the options.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string  $key
     * @param   string  $value
     */
    public function setOptions($key, $value){
        if(is_empty($key) || is_empty($value)){ return false; }

        $this->options[$key] = $value;

        return true;
    }

    /**
     * Returns the options.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array  $title
     */
    public function getOptions($key){
        if( isset($this->options) && !is_empty($this->options) ){
            return $this->options[$key];
        }

        return false;
    }

    /**
     * Sets the pages title.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array  $title
     */
    public function setTitle($title){
        $objTPL = self::getTPL();
        $objTPL->assign_var('PAGE_TITLE', secureMe($title));
    }

    /**
     * Sets the Theme mode to simple, or not.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   bool    $simple  If true, then page is in simple mode, else FULL BLOWN!
     */
    public function setSimpleMode($simple){
        $this->setOptions('mode', ((bool)$simple===true ? true : false));
    }

    /**
     * Defines what menu set to use on this page.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string  $moduleName     Name of the module
     * @param   string  $page_id        Page ID
     *
     */
    public function setMenu($moduleName, $page_id='default'){
        $this->setOptions('moduleMenu',  array(
            'module'  => $moduleName,
            'page_id' => $page_id,
        ));
    }

    /**
     * Build all the blocks in the template
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function buildBlocks(){
        $objBlocks = coreObj::getBlocks();
        $objBlocks->insertBlocks();
    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     */
    public function buildMenu(){
        $objTPL = coreObj::getTPL();
        /*$objTPL = self::getTPL();

        $noMenu = (defined('NO_MENU') && NO_MENU ? true : false);

        $menu = $this->getOptions('moduleMenu');
        if($menu['module'] === false){ $noMenu = true; }

        //we cant do nothin without any blocks
        if(!$noMenu && !is_empty($config['menu_blocks'])){
            //if it got set to null, or wasnt set atall, default to the core menu
            if(!isset($menu['module']) || is_empty($menu['module'])){
                $menu['module'] = 'core';
            }
            if(!isset($menu['page_id']) || is_empty($menu['page_id'])){
                $menu['page_id'] = 'default';
            }

            //then do the output
            $menuSetup = show_menu($menu['module'], $menu['page_id']);
            if($menuSetup){
                $objTPL->assign_block_vars('menu', array());
            }
        }else{
            //if we cant show menu, may aswell set the no_menu block
            $objTPL->assign_block_vars('no_menu', array());
        }*/

    }


    /**
     * Sets the Theme for this page to use
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string  $theme
     *
     * @return  bool
     */
    public function setTheme( $theme=null, $override=false ){
        $objUser = coreObj::getUser();

        // if nothing was passed in, grab the current site theme
        if(is_empty($theme)){
            $theme = $this->config('site', 'theme');
        }
        $override = ($this->config('site', 'theme_override', 'false') === 'true' || $override !== true);

        // if the user is online, then check if the theme they selected is valid & gogo use that
        if( $override === true
                && User::$IS_ONLINE
                && $objUser->grab('theme') !== null
                && is_dir(cmsROOT.'themes/'.$objUser->grab('theme').'/') ){

            $theme = $objUser->grab('theme');
        }

        // test for the theme override switch, if this is true, everyone see's the same theme
        if( is_empty($theme) || $override ){
            $theme = $this->config('site', 'theme');
            if(!is_dir(cmsROOT.'themes/'.$theme.'/') || !is_readable(cmsROOT.'themes/'.$theme.'/details.php')){
                $theme = 'default';
            }
        }

        // check see if the theme dir is present & readable
        if( !is_dir(cmsROOT.'themes/'.$theme.'/') || !is_readable(cmsROOT.'themes/'.$theme.'/details.php') ){
            return false;
        }

        // check if there is a language file for this theme & load it
        $langFile = cmsROOT.'themes/'.$theme.'/languages/'.$this->config('global', 'language').'/main.php';
            if( is_file($langFile) && is_readable($langFile) ){
                translateFile($langFile);
            }

        // and then set the vars
        self::$THEME      = $theme;
        self::$THEME_ROOT = cmsROOT.'themes/'.$theme.'/';
        $this->setLanguage();

        return true;
    }

    /**
     *
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @return  bool
     */
    public function setLanguage(){
        $objUser = coreObj::getUser();

        // Grab the default language info, and test to see if user has a request
        $language = $this->config('site', 'language');
        $langDir  = cmsROOT.'languages/';

        if( User::$IS_ONLINE){
            $lang = $objUser->grab('language');
            if( is_dir($langdir . $lang . '/') ){
                $language = $lang;
            }
        }

        if( is_dir($langDir . $language . '/') || is_readable($langDir . $language . '/main.php') ){
            translateFile($langDir . $language . '/main.php');
        }else{
            trigger_error('objPage->setLanguage() - Could not load language files.');
        }
    }

    /**
     * Adds a breadcrumb to the list.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array  $value   An array with 2 elements, [text] && [link]
     *
     * @return  bool
     */
    public function addBreadcrumbs(array $value){
        $options = (is_array($this->getVar('breadcrumbs')) ? $this->getVar('breadcrumbs') : array());

        $this->setVar('breadcrumbs', array_merge($options, $value));

        return true;
    }

        /**
         * Builds the breadcrumb list for the template.
         *
         * @version 1.0
         * @since   1.0
         * @author  Dan Aldridge
         *
         * @param   array  $value   An array with 2 elements, [text] && [link]
         *
         * @return  bool
         */
        private function buildBreadcrumbs(){
            $objTPL = coreObj::getTPL();

            $breadcrumbs = $this->getVar('breadcrumbs');
            $length = count( $breadcrumbs );

            // Check we have breadcrumbs to work with
            if( !count( $length ) || $breadcrumbs === false ) {
                return false;
            }

            $objTPL->assign_block_vars('breadcrumbs', array());

            // Loop through breadcrumbs and assign the array values to each template block
            foreach( $breadcrumbs as $index => $crumb ) {
                if( $index < $length ) {
                    $objTPL->assign_block_vars('breadcrumbs.item', array(
                        'URL'  => $crumb['url'],
                        'NAME' => $crumb['name'],
                    ));

                // If this is the last crumb, make it un-clickable
                } else {
                    $objTPL->assign_block_vars('breadcrumbs.item', array(
                        'NAME' => $crumb['name'],
                    ));
                }
            }
        }

    /**
     * Adds a CSS file to the list.
     *
     * @version 2.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array           Containing the array, either with or without keys.
     * -------------------------------
     * @param   string  $href   The path of the file
     * @param   string  $type   The type of the file, text/css || text/less
     * @param   string  $rel    Usually stylesheet
     *
     * @return  bool
     */
    public function addCSSFile(){
        $args = $this->_getArgs(func_get_args());

        if(count(func_get_args()) == 1 && array_key_exists('href', $args)){
            $css = array(
                'href'     => doArgs('href', false, $args),
                'type'     => doArgs('type', 'text/css', $args),
                'rel'      => doArgs('rel', 'stylesheet', $args),
                'priority' => doArgs('priority', MED, $args),
            );
        }else{
            $args = array_values($args);
            $css = array(
                'href'     => doArgs(0, false, $args),
                'type'     => doArgs(1, 'text/css', $args),
                'rel'      => doArgs(2, 'stylesheet', $args),
                'priority' => doArgs(3, MED, $args),
            );
        }

        if(!isset($css['href'])){ return false; }
        $priority = doArgs('priority', MED, $css);

        $file = str_replace(DS, '-', $css['href']);
        $file = md5($file);
            if(isset($this->cssFiles[$priority]) && array_key_exists($file, $this->cssFiles[$priority])){ return false; }

        $this->cssFiles[$priority][$file] = $css;

        return true;
    }

        /**
         * Builds the CSS Files & SubStyles.
         *
         * @version 1.0
         * @since   1.0
         * @author  Dan Aldridge
         *
         * @return  string
         */
        private function buildCSS(){

            $_tag = "\n".'<link%s />';
            $_arg = ' %s="%s"';

            $return = null;
            if( count($this->cssFiles) ){

                foreach(range(HIGH, LOW) as $priority){
                    if( !isset( $this->cssFiles[$priority] ) || !count( $this->cssFiles[$priority] ) ){ continue; }

                    foreach($this->cssFiles[$priority] as $args){
                        $tag = null;
                        foreach($args as $k => $v){
                            if($k == 'priority'){ continue; }

                            $tag .= sprintf($_arg, $k, $v);
                        }
                        $return .= sprintf($_tag, $tag);
                    }
                }

            }

            return $return;
        }

    /**
     * Adds a JS file to the list.
     *
     * @version 2.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array               Containing the array, either with or without keys.
     * -------------------------------
     * @param   string  $src        The path of the file
     * @param   string  $position   The position of the JS File - Header || Footer
     *
     * @return  bool
     */
    public function addJSFile(){
        $args = $this->_getArgs(func_get_args());

        $arg = func_get_arg(0);
        $position = in_array($args[1], array('header', 'footer'))
                        ? strtolower($args[1])
                        : 'footer';

        if(is_array($arg) && isset($arg['src'])){
            $args = $arg;
            $js = array(
                'src'      => doArgs('src', false, $args),
                'type'     => doArgs('type', 'text/javascript', $args),
                'priority' => doArgs('priority', MED, $args),
            );
        }else{
            $js = array(
                'src'      => doArgs(0, false, $args),
                'type'     => doArgs(1, 'text/javascript', $args),
                'priority' => doArgs(2, MED, $args),
            );
        }

        if(!isset($js['src'])){ return false; }
        $priority = doArgs('priority', MED, $js);

        $file = str_replace(DS, '-', $js['src']);
        $file = md5($file);
            if(isset($this->jsFiles[$position][$priority]) && array_key_exists($file, $this->jsFiles[$position][$priority])){
                return false;
            }

        $this->jsFiles[$position][$priority][$file] = $js;

        return true;
    }

    /**
     * Adds a JS Code to be loaded.
     *
     * @version 2.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string  $code
     *
     * @return  bool
     */
    public function addJSCode($code){
        if(empty($code)){ return false; }

        $code = str_replace(DS, '-', $code);
        $code = md5($code);

            if( isset($this->jsCode) && array_key_exists($code, $this->jsCode) ){
                return false;
            }

        $this->jsCode[$code] = $js;

        return true;
    }

        /**
         * Builds the JS Files & SubScripts.
         *
         * @version 1.0
         * @since   1.0
         * @author  Dan Aldridge
         *
         * @return  string
         */
        private function buildJS($mode){

            $_tag = "\n".'<script%s>%s</script>';
            $_arg = ' %s="%s"';

            $return = null;
            // do the files
            if( isset($this->jsFiles[$mode])  && count($this->jsFiles[$mode]) ) {
                foreach( range(HIGH, LOW) as $priority ){
                    if( !isset($this->jsFiles[$mode][$priority]) || !count($this->jsFiles[$mode][$priority]) ){
                        continue;
                    }

                    foreach( $this->jsFiles[$mode][$priority] as $args ){
                        $tag = null;

                        foreach($args as $k => $v){
                            if($k == 'priority'){ continue; }

                            $tag .= sprintf($_arg, $k, $v);
                        }

                        $return .= sprintf($_tag, $tag, '');
                    }
                }
            }

            // & if we are in footer mode, do the js code too
            if( $mode == 'footer' && !empty($this->jsCode) ){
                foreach( $this->jsCode as $args ){
                    $return .= sprintf($_tag, '', $code);
                }
            }

            return $return;
        }

    /**
     * Adds a Meta Tag to the list.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   array               Containing the array, either with or without keys.
     * -------------------------------
     * @param   string  $argKey
     * @param   string  $argValue
     *
     * @return  bool
     */
    public function addMeta(){
        $args = $this->_getArgs(func_get_args());

        $key = (isset($args['name']) ? md5(strtolower($args['name'])) : md5(strtolower(json_encode($args))));

        $arg = func_get_arg(0);
        if(!is_array($arg)){
            $args = array($args[0] => $args[1]);
        }

        $this->metaTags[$key] = $args;

        return true;
    }

        /**
         * Builds the Meta Tags.
         *
         * @version 1.0
         * @since   1.0
         * @author  Dan Aldridge
         *
         * @return  string
         */
        private function buildMeta(){
            if(!count($this->metaTags)){ return false; }

            $_tag = "\n".'<meta%s />';
            $_arg = ' %s="%s"';

            $return = null;
            foreach($this->metaTags as $args){
                $tag = null;
                foreach($args as $k => $v){
                    $tag .= sprintf($_arg, $k, $v);
                }
                $return .= sprintf($_tag, $tag);
            }

            return $return;
        }

    /**
     * Builds a string of css classes that can be used to apply browser specific hacks.
     *
     * @version 1.0
     * @since   1.0
     * @author  Dan Aldridge
     *
     * @param   string $useragent
     *
     * @return  string
     */
    public function getCSSSelectors($useragent=null){
        if( is_empty($useragent) ){
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }
        $useragent = strtolower($useragent);

        $classes = array();
        if( !preg_match('/opera|webtv/i', $useragent) && preg_match('/msie\s(\d)/', $useragent, $matches) ) {
            $classes[] = 'ie';
            $classes[] = 'ie' . $matches[1];

        } elseif( strstr($useragent, 'firefox/2') ) {
            $classes[] = 'ff';
            $classes[] = 'ff2';

        } elseif( strstr($useragent, 'firefox/3.5') ) {
            $classes[] = 'ff';
            $classes[] = 'ff3_3';

        } elseif( strstr($useragent, 'firefox/3') ) {
            $classes[] = 'ff';
            $classes[] = 'ff3';

        } elseif( preg_match('/firefox(\s|\/)(\d+)/', $useragent, $matches) ) {
            $classes[] = 'ff';
            $classes[] = 'ff' . $matches[2];

        } elseif( strstr($useragent, 'gecko/') ) {
            $classes[] = 'gecko';

        } elseif( preg_match('/opera(\s|\/)(\d+)/', $useragent, $matches) ) {
            $classes[] = 'opera';
            $classes[] = 'opera' . $matches[2];

        } elseif( strstr($useragent, 'konqueror') ) {
            $classes[] = 'konqueror';

        } elseif( strstr($useragent, 'chrome') ) {
            $classes[] = 'webkit';
            $classes[] = 'safari';
            $classes[] = 'chrome';

        } elseif( strstr($useragent, 'iron') ) {
            $classes[] = 'webkit';
            $classes[] = 'safari';
            $classes[] = 'iron';

        } elseif( strstr($useragent, 'applewebkit/') ) {
            $classes[] = 'webkit';
            $classes[] = 'safari';
            if( preg_match('/version\/(\d+)/i', $useragent, $matches) ){
                $classes[] = 'safari' . $matches[1];
            }

        } elseif( strstr($useragent, 'mozilla/') ) {
            $classes[] = 'gecko';
        }


        if( strstr($useragent, 'j2me') ) {
            $classes[] = 'mobile';

        } elseif( strstr($useragent, 'iphone')) {
            $classes[] = 'iphone';

        } elseif( strstr($useragent, 'ipod')) {
            $classes[] = 'ipod';

        } elseif( strstr($useragent, 'mac')) {
            $classes[] = 'mac';

        } elseif( strstr($useragent, 'darwin')) {
            $classes[] = 'mac';

        } elseif( strstr($useragent, 'webtv')) {
            $classes[] = 'webtv';

        } elseif( strstr($useragent, 'win')) {
            $classes[] = 'win';

        } elseif( strstr($useragent, 'freebsd')) {
            $classes[] = 'freebsd';

        } elseif( strstr($useragent, 'x11') || strstr($useragent, 'linux') ) {
            $classes[] = 'linux';

        }

        return implode(' ', $classes);
    }

    /**
     * Redirect using PHP Header function or JS redirect
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $location
     * @param   int     $time
     * @param   int     $mode         Definitions - 1=>GET['redirect'], 2=>HTTP_REFFERER, => 0=>$location
     */
    public function redirect($location=null, $time=0, $mode=0){
        switch($mode) {
            case 1:
                $url = doArgs('redirect', $location, $_GET);
            break;

            case 2:
                $url = $this->config('global', 'referer');
            break;

            case 0:
            default:
                $url = $location;
            break;
        }

        // check to see weather headers have already been sent, this prevents us from using the header() function
        if( !headers_sent() && $time === 0 ) {
            header( 'Location: '.$url );
            return;

        } else { // headers have already been sent, so use a JS and even META equivalent
            $output = null;

            $output .= '<script type="text/javascript">';
            if( $time != 0 ){
                $output .= 'function redirect(){';
            }
            $output .= '  window.location.href="'.$url.'";';
            if( $time != 0 ){
                $output .= '} setTimeout(\'redirect()\', '.($time*1000).');';
            }
            $output .= '</script>';
            $output .= '<noscript>';
            $output .= '  <meta http-equiv="refresh" content="'.$time.';url='.$url.'" />';
            $output .= '</noscript>';

            echo $output;
        }
    }

    public function buildPage(){
        $objTPL     = self::getTPL();
        $objPlugins = self::getPlugins();
        $objUser    = self::getUser();

/**
  //
  //-- Meta Tags
  //
**/
        $this->addMeta('charset', 'utf-8');
        $this->addMeta(array(
            'http-equiv' => 'content-language',
            'content'    => $this->config('site', 'language'),
        ));

        if($this->config('site', 'no-zoom', true)){
            $this->addMeta(array(
                'name'    => 'viewport',
                'content' => 'width=device-width, initial-scale=1',
            ));
        }

            // this array holds the most common
            $metaArray = array(
                'author'        => $this->config('cms',  'name', 'Cybershade CMS'),
                'description'   => $this->config('site', 'description', ''),
                'keywords'      => $this->config('site', 'keywords', ''),
                //'copyright'     => langVar('L_SITE_COPYRIGHT', $this->config('site', 'title'), $this->config('cms', 'name'), CMS_VERSION),
                'generator'     => $this->config('cms',  'name').' v'.CMS_VERSION,

                'user_id'       => -1,
                'root'          => '/'.root(),
                'url'           => $this->config('global', 'url', ''),

                'ROBOTS'        => 'INDEX, FOLLOW',
                'GOOGLEBOT'     => 'INDEX, FOLLOW',
            );

                foreach($metaArray as $k => $v){
                    $this->addMeta(array(
                        'name'    => $k,
                        'content' => $v,
                    ));
                }
                unset($metaArray);

/**
  //
  //-- CSS
  //
**/
        $cssDir = '/'.root().'assets/styles';

        $this->addCSSFile($cssDir.'/framework-min.css', 'text/css', 'stylesheet', HIGH);
        #$this->addCSSFile($cssDir.'/extras-min.css', 'text/css');

        // throw a hook here, so they have the ability to do...whatever
        $cssFiles = array();
        $objPlugins->hook('CMS_PAGE_CSSFILES', $cssFiles);

            if(count($cssFiles)){
                foreach($cssFiles as $file){
                    $this->addCSSFile($file);
                }
            }

/**
  //
  //-- JS
  //
**/
        $cssDir = '/'.root().'assets/javascript';

        $this->addJSFile(array(
            'src' => $cssDir.'/framework-min.js',
            'priority' => HIGH,
        ), 'footer');
        $this->addJSFile(array(
            'src' => $cssDir.'/extras-min.js',
            'priority' => HIGH,
        ), 'footer');

        // throw a hook here, so they have the ability to do...whatever
        $jsFiles = array();
        $objPlugins->hook('CMS_PAGE_JSFILES', $jsFiles);

        if( defined('cmsDEBUG') && cmsDEBUG === true ){
            $this->addJSFile(array(
                'src' => $cssDir.'/debug.js',
                'priority' => HIGH,
            ), 'footer');
        }

/**
  //
  //-- Theme Extras
  //
**/
        $browserCSSSelectors = $this->getCSSSelectors();
        $themeConfig = self::$THEME_ROOT.'theme.php';
        if(is_readable($themeConfig)){
            include_once($themeConfig);
        }

        // Set the index.tpl file as the template
  //
  //-- Template Stuff
  //
        $tplGlobals = array(
            'ROOT'          => root(),
            'THEME_ROOT'    => root(). Page::$THEME_ROOT,

            'SITE_NAME'     => $this->config('site', 'site_name'),

            // 'ROW_COLOR1'    => $vars['row_color1'],
            // 'ROW_COLOR2'    => $vars['row_color2'],

            'USERNAME'      => $objUser->grab('username'),
            //'TIME'          => $objTime->mk_time(time(), 'l H:i:s a'),


            'U_UCP'         => '/'.root().'user/',
            'L_UCP'         => langVar('L_UCP'),
        );

        if( !User::$IS_ONLINE ){
            $tplGlobals += array(
                'U_LOGIN' => '/'.root().'login',
                'L_LOGIN' => 'Login',
            );
        } else {
            $tplGlobals += array(
                'U_LOGIN' => '/'.root().'logout?check='.$objUser->grab('usercode'),
                'L_LOGIN' => 'Logout'
            );
        }

        $objPlugins->hook('CMS_PAGE_TPL_GLOBALS', $tplGlobals);

        $objTPL->assign_vars($tplGlobals);
        $objTPL->assign_var('BREADCRUMBS',      $this->buildBreadcrumbs());
        $objTPL->assign_var('_CSS_SELECTORS',   $this->getCSSSelectors());
        $objTPL->assign_var('_META',            $this->buildMeta());
        $objTPL->assign_var('_CSS',             $this->buildCSS());
        $objTPL->assign_var('_JS_HEADER',       $this->buildJS('header'));
        $objTPL->assign_var('_JS_FOOTER',       $this->buildJS('footer'));
    }

    public function showHeader(){
        if($this->getOptions('completed')){ return; }

        $objTPL = self::getTPL();

        // run a check on simple
        $simple = ($this->getOptions('mode') ? true : false);

        // see if we are gonna get the simple one or the full blown one
        $header = ($simple ? 'simple_header.tpl' : 'site_header.tpl');

        $objTPL->set_filenames(array( 'siteHeader' => self::$THEME_ROOT . $header ));

        $this->buildBlocks();

        $objTPL->parse('siteHeader');

        $this->setOptions('completed', 1);
    }


    public function showFooter(){
        if(!$this->getOptions('completed')){ return; }

        $objTPL = self::getTPL();

        // run a check on simple
        $simple = ($this->getOptions('mode') ? true : false);

        // see if we are gonna get the simple one or the full blown one
        $footer = ($simple ? 'simple_footer.tpl' : 'site_footer.tpl');

        $objTPL->set_filenames(array( 'siteFooter' => self::$THEME_ROOT . $footer ));

        $this->buildBlocks();

        if( defined('cmsDEBUG') && cmsDEBUG === true ){

            $objDebug = coreObj::getDebug();
            $objTPL->assign_block_vars('debug', array(
                'DEBUG' => $objDebug->output(),
            ));

        }

        $objTPL->parse('siteFooter');
    }

}

?>