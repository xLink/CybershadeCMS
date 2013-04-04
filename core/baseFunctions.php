<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

/**
  //
  //--Core Functions
  //
**/
    /**
     * Used to determine the base path of the CMS installation;
     *
     * @version 1.3
     * @since   1.0.0
     * @author  Daniel Noel-Davies, Richard Clifford
     *
     * @return  string
     */
    function root(){
        $path = str_replace('\\', '/', __FILE__);

        //if we are dealing with a users home directory
        if(substr($_SERVER['REQUEST_URI'], 0, 2) == '/~'){
            return substr($_SERVER['REQUEST_URI'], 1);
        }

        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $rootDifference = '';

        // Do checks on the urls
        $docRootCheck = strnstr( $docRoot, '/', 2 );
        $pathCheck    = strnstr( $path, '/', 2 );

        if( $docRootCheck !== $pathCheck ){
            if(preg_match( '(/(\w+)/)', dirname($docRoot), $matches )){
                // Rtrim so we don't get 2 slashes
                $rootDifference = rtrim($matches[0], '/');
            }
        }

        // Prepend the "X Factor" incase the URLs differ
        $path = $rootDifference . $path;

        //else we are dealing with a propper url setup
        $newPath = str_replace($docRoot, '', $path);
        $newPath = substr($newPath, 0, strrpos($newPath, 'core/baseFunctions.php'));

        if(substr($newPath, 0, 1) == '/'){
            $newPath = substr($newPath, 1);
        }
        return ($newPath != '' && $newPath != '/' ? $newPath : '');
    }

    /**
     * Tests $args for $key, if it is not a valid key, then return $default.
     * Extra checks can be called in by $callback, either a function name, or a closure.
     *
     * @version 1.2
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $key            Which key to check for
     * @param   string  $default        A default value to use if our checks fail
     * @param   array   $args           An array to check against
     * @param   mixed   $callback       Can be name of a func that returns a bool value or an anonymous function
     *
     * @return  string
     */
    function doArgs($key, $default, $args, $callback=null){
        $extra = true; //set this to true so the end result will work

        //test value here so we have atleast a value to work with
        $value = (isset($args[$key])
                    ? $args[$key]
                    : (!empty($default)
                        ? $default
                        : false));

        //if we have a callback then exec
        if(is_empty($callback)){
            return $value;
        }

        if(is_callable($callback)){
            //this will allow anonymous functions to be used
            if(is_object($callback)){
                return $callback($value);
            }

            $extra = call_user_func($callback, $value);
        }
        //test and return a value
        return (isset($args[$key]) && $extra ? $args[$key] : $default);
    }


    /**
     * Run a function recursivly through an array
     * http://www.php.net/manual/en/function.array-walk-recursive.php#99639
     *
     * @author  bradbeattie [at] gmail [dot] com
     * @version 1.0
     * @since   1.0.0
     *
     * @param   array   $array
     * @param   string  $function Callback
     * @param   array   $parameters
     *
     * @return  string
     */
    function recursiveArray(&$array, $function, $parameters = array()) {
        $reference_function = function(&$value, $key, $userdata) {
            $parameters = array_merge(array($value), $userdata[1]);
            $value = call_user_func_array($userdata[0], $parameters);
        };
        array_walk_recursive($array, $reference_function, array($function, $parameters));
    }

    /**
     * Search for a value within a multi-dimensional array
     *
     * @version     1.0
     * @since       1.0.0
     */
    function searchRecursiveArray($needle, $haystack, $strict=false, $path=array()){
        if(!is_array($haystack)){ return false; }

        foreach($haystack as $key => $val){
            if(is_array($val) && $subPath = array_searchRecursive($needle, $val, $strict, $path)){
                $path = array_merge($path, array($key), $subPath);
                return $path;
            }elseif((!$strict && $key == $needle) || ($strict && $key === $needle)){
                $path[] = $key;
                return $path;
            }
        }
        return false;
    }

    /**
     * Borrowed function from phpbb3 to get contents of a file on remote server
     *
     * @version 1.0
     * @since   1.0.0
     * @author  PHPBB Team
     */
    function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port=80, $timeout=10) {
        global $objCore;
        if($objCore->config('site', 'internetCalls') == 0){
            return false;
        }

        $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if($fsock) {
            @fputs($fsock, 'GET '.$directory.'/'.$filename.' HTTP/1.1'."\r\n");
            @fputs($fsock, 'HOST: '.$host."\r\n");
            @fputs($fsock, 'Connection: close'."\r\n\r\n");

            $file_info = '';
            $get_info = false;

            while(!feof($fsock)) {
                if($get_info) {
                    $file_info .= fread($fsock, 1024);
                } else {
                    $line = fgets($fsock, 1024);
                    if($line == "\r\n") {
                        $get_info = true;
                    } else {
                        if(stripos($line, '404 not found') !== false) {
                            $errstr = 'Error 404: '.$filename;
                            return false;
                        }
                    }
                }
            }
            fclose($fsock);
        } else {
            if($errstr) {
                return false;
            } else {
                $errstr = 'fsockopen is disabled.';
                return false;
            }
        }

        return $file_info;
    }

/**
  //
  //--CMS Functions
  //
**/
    /**
     * Sends an email to the intended target
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     * @access  private
     *
     * @param   string  $to
     * @param   string  $from
     * @param   string  $subject
     * @param   string  $message
     *
     * @return  bool
     */
    function _mailer($to, $from, $subject, $message){
        $server = $_SERVER['HTTP_HOST'];

        //set headers for the email
        $headers[] = 'From: NoReply <'.$from.'>';
        $headers[] = 'Reply-To: NoReply <'.$from.'>';
        $headers[] = 'Return-Path: NoReply <'.$from.'>';
        $headers[] = 'Date: '.date('r', time());
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Message-ID: <'.md5(uniqid(time())).'@'.$server.'>';
        $headers[] = 'Content-Type: text/html; charset="iso-8859-1"';
        $headers[] = 'X-Mailer: PHP v'.phpversion();
        $headers[] = 'X-Priority: 3';
        $headers[] = 'X-MSMail-Priority: Normal';
        $headers[] = 'X-MimeOLE: Produced By CybershadeCMS '.cmsVERSION;

        if(@mail($to, $subject, $message, implode(" \n", $headers))){
            return true;
        }

        return false;
    }

    /**
     * Returns a list of all directories and files in an associative array.
     *
     * @version 1.0
     * @since   1.0.0
     *
     * @param   string     $path
     *
     * @return  array
     */
    function getFiles($path) {
        $files = array();
        $fileNames = array();
        $i = 0;

        // ensure the path is a directory
        if(!is_dir($path)){
            return array();
        }

        // open it up and lets have a look
        if($dh = opendir($path)) {
            // whilst we have something to play with
            while(($file = readdir($dh)) !== false) {

                //if it is a . or .., which are this dir and parent dir, then continue over them
                if($file == '.' || $file == '..') {
                    continue;
                }

                $fullpath = $path . '/' . $file;
                $fkey = strtolower($file);

                // if the filename is already in here add a space, files & folders can have same names >.<
                while(array_key_exists($fkey, $fileNames)) {
                    $fkey .= ' ';
                }

                $a = stat($fullpath);
                $files[$fkey]['size'] = $a['size'];
                $files[$fkey]['sizetext'] = ($a['size'] > 0) ? formatBytes($a['size']) : '-';
                $files[$fkey]['name'] = $file;
                $files[$fkey]['type'] = filetype($fullpath);
                $fileNames[$i++] = $fkey;

            }
            closedir($dh);
        } else {
            trigger_error('getFiles(): Cannot open directory: '.$path, E_USER_ERROR);
        }

        // nothing was returned, we'll just return here
        if(is_empty($fileNames)){
            return array();
        }

        // we'll sort the files before returning
        sort($fileNames, SORT_STRING);
        $sortedFiles = array();
        $i = 0;
        foreach($fileNames as $f) {
            $sortedFiles[$i++] = $files[$f];
        }

        return $sortedFiles;
    }

    /**
     * Attempts to figure out what browser the string is relating to
     *
     * @version 2.0
     * @since   1.0.0
     *
     * @param   string     $useragent
     *
     * @return  string
     */
    function getBrowser($useragent){
        // BE CAREFUL WHEN MODIFYING AS THE ORDER DOES MATTER!
        if(strpos($useragent, 'Nintendo Wii') !== false){       return 'Nintendo Wii'; }
        if(strpos($useragent, 'Nitro') !== false){              return 'Nintendo DS'; }
        if(strpos($useragent, 'Opera') !== false){              return 'Opera'; }
        if(strpos($useragent, 'iPhone') !== false){             return 'iPhone'; }
        if(strpos($useragent, 'Android') !== false){            return 'Android'; }
        if(strpos($useragent, 'Chrome') !== false){             return 'Chrome'; }
        if(strpos($useragent, 'Netscape') !== false){           return 'Netscape'; }
        if(strpos($useragent, 'OmniWeb') !== false){            return 'OmniWeb'; }
        if(strpos($useragent, 'Safari') !== false){             return 'Safari'; }
        if(strpos($useragent, 'Konqueror') !== false){          return 'Konqueror'; }
        if(strpos($useragent, 'Minimo') !== false){             return 'Minimo'; }
        if(strpos($useragent, 'Galeon') !== false){             return 'Galeon'; }
        if(strpos($useragent, 'Phoenix') !== false){            return 'Phoenix'; }
        if(strpos($useragent, 'Firefox') !== false){            return 'Firefox'; }
        if(strpos($useragent, 'SeaMonkey') !== false){          return 'SeaMonkey'; }
        if(strpos($useragent, 'NetPositive') !== false){        return 'NetPositive'; }
        if(strpos($useragent, 'PalmOS') !== false){             return 'Novarra'; }
        if(strpos($useragent, 'Avant Browser') !== false){      return 'Avant Browser'; }
        if(strpos($useragent, 'PSP') !== false){                return 'PlayStation Portable'; }
        if(strpos($useragent, 'PLAYSTATION') !== false){        return 'PlayStation'; }
        if(strpos($useragent, 'Camino') !== false){             return 'Camino'; }
        if(strpos($useragent, 'OffByOne') !== false){           return 'Off By One'; }
        if(strpos($useragent, 'PIE') !== false){                return 'Pocket Internet Explorer'; }
        if(strpos($useragent, 'WebTV') !== false){              return 'WebTV'; }
        if(strpos($useragent, 'MSIE') !== false){               return 'Internet Explorer'; }
        if(strpos($useragent, 'Jeeves') !== false){             return 'Ask Bot'; }
        if(strpos($useragent, 'googlebot') !== false){          return 'Google Bot'; }
        if(strpos($useragent, 'grub') !== false){               return 'Grub Crawler'; }
        if(strpos($useragent, 'Yahoo!') !== false){             return 'Yahoo! Slurp'; }
        if(strpos($useragent, 'Slurp') !== false){              return 'Inktomi Slurp'; }
        if(strpos($useragent, 'w3m') !== false){                return 'w3m'; }
        if(strpos($useragent, 'Lynx') !== false){               return 'Lynx'; }
        if(strpos($useragent, 'ELinks') !== false){             return 'ELinks'; }
        if(strpos($useragent, 'Links') !== false){              return 'Links'; }
        if(strpos($useragent, 'Googlebot') !== false){          return 'Google Bot'; }
        if(strpos($useragent, 'msnbot') !== false){             return 'MSN Bot'; }
        if(strpos($useragent, 'ia_archiver') !== false){        return 'Alexa Bot'; }
        if(strpos($useragent, 'Baiduspider') !== false){        return 'Baidu Spider'; }
        if(strpos($useragent, 'curl') !== false){               return 'cURL Bot'; }
        if(strpos($useragent, 'GameSpy') !== false){            return 'GameSpy HTTP'; }
        if(strpos($useragent, 'Gigabot') !== false){            return 'Giga Bot'; }
        if(strpos($useragent, 'Scooter') !== false){            return 'Scooter Bot'; }
        if(strpos($useragent, 'Wget') !== false){               return 'wget'; }
        if(strpos($useragent, 'Yahoo') !== false){              return 'Yahoo Crawler'; }
        if(strpos($useragent, 'Android') !== false){            return 'Android'; }
        if(strpos($useragent, 'iCab') !== false){               return 'iCab'; }
        if(strpos($useragent, 'AvantGo') !== false){            return 'AvantGo'; }
        if(strpos($useragent, 'amaya') !== false){              return 'Amaya'; }
        if(strpos($useragent, 'Mozilla') !== false){            return 'Mozilla'; }
        if(strpos($useragent, 'America Online Browser') !== false){ return 'AOL Explorer'; }

        return $useragent;
    }


/**
  //
  //--String Functions
  //
**/

    /**
     * Set a cookie, this cookie shouldnt be accessable via scripting languages such as JS.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $name
     * @param   string  $value
     * @param   int     $expire
     *
     * @return  bool
     */
    function set_cookie($name, $value, $expire){
        //if cookie got set, then temp set it in PHP so its accessable before the next page reload
        if(setCookie($name, $value, $expire, '', '', false, true)){
            $_COOKIE[$name] = $value;
            return true;
        }

        return false;
    }

    /**
     * Returns a language var ready to be used on the page.
     *
     * @version 2.0
     * @since   1.0.0
     *
     * @param   string     $langVar
     * @param   ...
     *
     * @return  string
     */
    function langVar(){
        $_lang = Core_Classes_coreObj::$_lang;

        //get how many arguments the function received
        $args = func_get_args();
        $var = doArgs($args[0], null, $_lang); //get the corresponding lang var
            if(is_empty($var)){
                return false;
            }

        //swap the first argument for the language var
        foreach($args as $k => $v){
            $vars[$k] = ($k==0 ? $var : $v);
        }

        if(is_array($var)){
            foreach($var as $k => $v){
                $var[secureMe($k, 'langVar')] = secureMe($v, 'langVar');
            }
        }else{
            $var = secureMe($var, 'langVar');
        }
        return count($args)>1 ? call_user_func_array('sprintf', $vars) : $var;
    }

    /**
     * Adds a language file to the global language array
     *
     * @version 2.0
     * @since   1.0.0
     *
     * @param   string     $file
     *
     * @return  bool
     */
    function translateFile($file){
        if(!isset($LANG_LOAD)){
            $LANG_LOAD = true;
        }

        $return = false;
        if( is_file($file) && is_readable($file) ){
            $_lang = array();
            include_once($file);

            if( is_array($_lang) && count($_lang)>1 ){
                Core_Classes_coreObj::$_lang = array_merge(Core_Classes_coreObj::$_lang, $_lang);
            }

            $return = true;
        }

        unset($LANG_LOAD);
        return $return;
    }

    /**
     * Retreives part of a string
     *
     * @version 1.0
     * @since   1.0.0
     *
     * @param   string     $begin
     * @param   string     $end
     * @param   string     $contents
     *
     * @return  string
     */
    function inBetween($begin, $end, $contents) {
        $pos1 = strpos($contents, $begin);
        if($pos1 !== false){
            $pos1 += strlen($begin);
            $pos2 = strpos($contents, $end, $pos1);
            if($pos2 !== false){
                $substr = substr($contents, $pos1, $pos2 - $pos1);
                return $substr;
            }
        }
        return false;
    }
    
    /**
     * Verifies an IP against a IPv4 range.
     *         127.0.0.1 would verify against 127.0.0.* but not *.*.*.2
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $range      Range to check the IP against
     * @param   string  $ip         IP to check
     *
     * @return  bool
     */
    function checkIPRange($range, $ip){
        $range = explode('.', $range);
        $ip = explode('.', $ip);

        // Make sure the IP is valid under IPv4
        if(count($range) > 4 || count($ip) > 4){
            return false;
        }

        if($range[0] == '*' || $ip[0] == '*'){
            return false;
        }

        for($i=0;$i<4;$i++){
            // Make sure the SubMask is valid under IPv4
            if(strlen($range[$i]) > 3 || strlen($ip[$i]) > 3){
                return false;
            }

            // Make sure the SubMask is valid
            if(!is_number($range[$i]) && $range[$i] != '*'){
                return false;
            }

            // Make sure the SubMask is valid
            if(!is_number($ip[$i]) && $ip[$i] != '*'){
                return false;
            }

            // Make sure the SubMask is valid
            if(is_number($range[$i]) && strlen($range[$i]) > 255){
                return false;
            }

            // Make sure the SubMask is valid
            if(is_number($ip[$i]) && strlen($ip[$i]) > 255){
                return false;
            }

            // Final Check
            if($range[$i] != $ip[$i] && $range[$i] != '*'){
                return false;
            }
        }

        return true;
    }

    /**
     * Parses content for viewing in browser.
     *
     * @version 1.0
     * @since   1.0.0
     *
     * @param   string  $content
     * @param   bool    $echoContent
     * @param   bool    $showSmilies
     *
     * @return  string
     */
    function contentParse($content, $echoContent=false, $showSmilies=true){
        //load a new instance up
        $objBBCode = Core_Classes_coreObj::getLib('BBCode');

        //load in the smilies
        $objBBCode->SetSmileyDir('/'.root().'assets/images/smilies');

        //load in the bbcode_tags
        $file = cmsROOT.'core/bbcodeTags.php';
            if(is_readable($file)){
                include_once($file);
            }

        //set smilies on or off
        $objBBCode->SetEnableSmileys($showSmilies);

        //output the $content
        if( $echoContent === false ){
            return $objBBCode->parse(htmlspecialchars_decode($content));
        }
        echo $objBBCode->parse(htmlspecialchars_decode($content));
    }

    /**
     * Handles securing input/output
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string  $string
     * @param   string  $mode
     *
     * @return  string
     */
    function secureMe($string, $mode='html'){
        $objSQL = Core_Classes_coreObj::getDBO();

        switch(strtolower($mode)){
            case 'html':
                $string = htmlspecialchars_decode($string);
                $string = htmlspecialchars($string);
            break;

            case 'url':
                $string = urlencode($string);
            break;

            case 'sql':
            case 'mres':
                $string = $objSQL->escape($string);
            break;

            case 'langvar':
                $string = htmlspecialchars($string);
                $string = str_replace(array('&gt;', '&lt;', '&amp;', '&quot;'), array('>', '<', '&', '"'), $string);
            break;

            case 'num':
                if(!ctype_digit((string)$string)){
                    $string = preg_replace('/[^0-9]/', '', $string);
                }
            break;

            case 'alphanum':
                if(!ctype_alnum((string)$string)){
                    $string = preg_replace('/[^a-zA-Z0-9-_]/', '', $string);
                }
            break;
        }

        return $string;
    }

    /**
     * Turns a string SEO Friendly
     *
     * @version 1.0
     * @since   1.0.0
     *
     * @param   string    $text
     *
     * @return  string
     */
    function seo($text){
        static $search, $replace;

        $text = strtr($text, array('&amp' => ' and ', '/' => '-', '.' => '-'));
        $text = html_entity_decode($text);

            if (!$search) {
                $search = $replace = array();
                // Get the HTML entities table into an array
                $trans = get_html_translation_table(HTML_ENTITIES);
                // Go through the entity mappings one-by-one
                foreach ($trans as $literal => $entity) {
                    // Make sure we don't process any other characters such as fractions, quotes etc:
                    if (ord($literal) >= 192) {
                        // Get the accented form of the letter
                        $search[] = $literal;
                        // Get e.g. 'E' from the string '&Eacute'
                        $replace[] = $entity[1];
                    }
                }
            }
            str_replace($search, $replace, $text);

            $text = trim(preg_replace('/[^a-z \d\-]/i', '', $text));
            $text = strtr(strtolower($text), array(' ' => '-'));
            $text = preg_replace('/[\-]{2,}/', '-', $text);
            $text = rtrim($text, '-');
            if(is_number($text)) { $text = 'number-'.$text; } // numeric names would confuse everything
         return $text;
    }

    /**
     * Checks to see if the string is empty, checks against null, empty array and false
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string    $var
     *
     * @return  bool
     */
    function is_empty($var) {
        if(is_null($var) || empty($var) || (is_string($var) && trim($var)=='')){ return true; }
        if(is_array($var) && !count($var)){ return true; }
        if($var === false){ return true; }

        return false;
    }

    /**
     * Checks to see if the string is a number (0-9 only)
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string     $number
     *
     * @return  bool
     */
    function is_number($number) {
        return (ctype_digit((string)$number) ? true : false);
    }

    /**
     * Cuts down a string to the specified length
     *
     * @version 1.1
     * @since   0.7.0
     * @author  Dan Aldridge
     *
     * @param   string      $text
     * @param   int         $numb
     * @param   bool        $whiteSpace
     *
     * @return  string
     */
    function truncate($text, $numb=80, $whiteSpace=true) {
        //check to make sure $text is longer than $numb first
        if(strlen($text) < $numb) {
            return $text;
        }

        $text = substr($text, 0, $numb);
        if($whiteSpace === true){
            $text = substr($text, 0, strrpos($text, ' '));
        }

        if ((substr($text, -1)) == '.') {
            $text = substr($text, 0, (strrpos($text, '.')));
        }
        $etc = '...';
        return $text.$etc;
    }

    /**
     * Finds the nth occurance of a string
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   string   $haystack    haystack
     * @param   string   $needle      The needle to search for
     * @param   int      $nth         The occurance to search for
     *
     * @return  string
     */
    function strnstr($haystack, $needle, $nth){
        $max = strlen($haystack);
        $n = 0;
        for( $i=0; $i < $max; $i++ ){
            if( $haystack[$i] == $needle ){
                $n++;
                if( $n >= $nth ){
                    break;
                }
            }
        }
        $arr[] = substr($haystack, 0, $i);

        return $arr[0];
    }

    /**
     * Generates a random code
     *
     * @version 2.0
     * @since   0.6.0
     *
     * @param   int         $maxLength
     *
     * @return  string
     */
    function randCode($maxLength=6){
        $password = NULL;
        $possible = 'bcdfghjkmnrstvwxyz123456789';
        $i = 0;
        while(($i < $maxLength) && (strlen($possible) > 0)){ $i++;
            $character = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            $password .= $character;
        }
        return $password;
    }

    /**
     * Uses the BBCode Class to verify image
     *
     * @version 3.0
     * @since   1.0.0
     */
    function doImage($content) {
        $objBBCode = Core_Classes_coreObj::getLib('BBCode');

        $content = trim($objBBCode->UnHTMLEncode(strip_tags($content)));
        if (preg_match("/\\.(?:gif|jpeg|jpg|jpe|png)$/", $content)) {
            if (preg_match("/^[a-zA-Z0-9_][^:]+$/", $content)) {
                if (!preg_match("/(?:\\/\\.\\.\\/)|(?:^\\.\\.\\/)|(?:^\\/)/", $content)) {
                    $info = @getimagesize($content);
                    if ($info[2] == IMAGETYPE_GIF || $info[2] == IMAGETYPE_JPEG || $info[2] == IMAGETYPE_PNG) {
                        return htmlspecialchars($content);
                    }
                }
            } else if ($objBBCode->IsValidURL($content, false)) {
                return htmlspecialchars($content);
            }
        }
        return false;
    }

/**
  //
  //--Errors
  //
**/
    /**
     * Custom error handler for the cms.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     */
    function cmsError(){
        $args = func_get_args();
        $filename = explode((stristr(PHP_OS, 'WIN') ? '\\' : '/'), $args[2]);
        if($args[0] != 8){
            $msg = '<b>CMS Error:</b> <i>'.$args[1].'</i> in <b>'.
                        (User::$IS_ADMIN ? $args[2] : $filename[(count($filename)-1)]).
                    '</b> on line <b>'.$args[3].'</b>';

            if(defined('INSTALLER')){
                die($msg);
            }else{
                msg('ERR', $msg, null, null, null, false);
            }
        }
    }

/**
  //
  //-- MSG Functions
  //
**/
    /**
     * Displays a formatted error on screen.
     *
     * @version 3.0
     * @since   1.0.0
     */
    function msg($msg_type, $message, $tplVar=NULL, $title=NULL){
        $objPage = Core_Classes_coreObj::getPage();
        $objTPL = Core_Classes_coreObj::getTPL();
        $objModule = Core_Classes_coreObj::getModule();

        if(!is_object($objTPL) || !is_object($objPage)){ echo $message; exit; }

        $handle = '__msg_'.($tplVar===NULL ? rand(0, 1000) : $tplVar);
        $handle = (is_object($objModule) && $tplVar=='body') ? 'body' : $handle;
        $objTPL->set_filenames(array(
            $handle    => cmsROOT.'modules/core/views/module/message/default.tpl'
        ));

        switch(strtolower($msg_type)){
            case 'fail':    $img = '/'.root().'images/fail.png'; $type = 'error';    break;
            case 'ok':      $img = '/'.root().'images/ok.png';   $type = 'status';   break;
            case 'info':    $img = '/'.root().'images/info.png'; $type = 'warning';  break;

            default: $img = NULL; break;
        }

        $objTPL->assign_vars(array(
            'L_MSG_TYPE' => (is_empty($title) ? langVar('MSG_'.strtoupper($msg_type)) : $title),
            'L_MSG'      => $message,
            'IMG'        => isset($img) && !is_empty($img) ? '<img src="'.$img.'" style="height: 48px; width: 48px;">' : '',
            'ALIGN'      => 'left',
            'TYPE'       => $type,
        ));

        if($tplVar===NULL){
            $objTPL->parse($handle);
        }else if($tplVar=='return'){
            return $objTPL->get_html($handle);
        }else if($handle=='body'){
            $objTPL->parse($handle, false);
        }else{
            $objTPL->assign_var_from_handle($tplVar, $handle);
        }
    }

    /**
     * Displays a confirmation messagebox.
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $type
     * @param   string $msg
     * @param   string $title
     * @param   string $tplVar
     *
     * @return  bool
     */
    function confirmMsg($type, $msg, $title=NULL, $tplVar=NULL){
        $objPage = Core_Classes_coreObj::getPage();
        $objForm = Core_Classes_coreObj::getForm();
        $objUser = Core_Classes_coreObj::getUser();

        //check if we have confirmed either way yet
        if(!HTTP_POST){
            //setup redirects and session ids
            $_SESSION['site']['confirm']['return'] = (isset($_SERVER['HTTP_REFERER'])&&!is_empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'.root().'');
            $_SESSION['site']['confirm']['sessid'] = $sessid = $objUser->mkPassword($objUser->grab('username').time());

            //and the form, atm its gotta be crude, it'll be sexied up for the rebuild
            $newMsg = $objForm->start('msg', array('method' => 'POST'));
            $newMsg .= $msg.'<br />';
            $newMsg .= $objForm->inputbox('sessid', 'hidden', $sessid).'<br />';
            $newMsg .= '<div align="center">'.$objForm->button('submit', 'Continue').' '.$objForm->button('submit', 'Go Back', array('class'=>'black')).'</div>';
            $newMsg .= $objForm->finish();

            //use msg() to output and return false so the code wont exec below
            echo msg($type, $newMsg, $tplVar, $title);

            return false;
        }else{
            //now we have confirmed, lets do a little sanity checking
            $redir = $_SESSION['site']['confirm']['return'];

            //we have the sessid
            if(!isset($_POST['sessid'])){ hmsgDie('FAIL', 'Error: Please confirm your intentions via the form.'); }
            if($_POST['sessid']!=$_SESSION['site']['confirm']['sessid']){ hmsgDie('FAIL', 'Error: Could not verify your intentions.'); }

            //dont need this anymore
            unset($_SESSION['site']['confirm']);

            //make sure we actually have the submit
            if(!isset($_POST['submit'])){ hmsgDie('FAIL', 'Error: Could not verify your intentions.'); }

            //now check for what we expect and act accordingly
            if($_POST['submit']=='Continue'){
                return true;
            }

            if($_POST['submit']=='Go Back'){
                $objPage->redirect($redir, 3, 0);
                hmsgDie('INFO', 'Redirecting you back.');
            }

            //if we get here, they tried to play us, so lets just return false anyway
            return false;
        }
    }

    /**
     * Shows a message and then exit the current page with a footer.
     *
     * @version 2.0
     * @since   0.6.0
     */
    function msgDie($msg_type, $message, $line=null, $file=null, $query=null, $footer=true){
        $objTPL = Core_Classes_coreObj::getTPL();
        $objPage = Core_Classes_coreObj::getPage();

        //if(!is_object($objTPL) || !is_object($objPage)){ echo $message; exit; }
        $header = $objPage->getOptions('completed');
        if(!$header){
            $objPage->buildPage();

            $objPage->showHeader();
        }

        $objTPL->set_filenames(array(
            '__msgBody' => cmsROOT.'modules/core/views/module/message/default.tpl'
        ));

        $query = !is_empty($query) ? $query : null;
        $line  = !is_empty($line)  ? $line  : null;
        $file  = !is_empty($file)  ? $file  : null;

        switch(strtolower($msg_type)){
            case 'fail':    $img = '/'.root().'images/fail.png'; $type = 'error';    break;
            case 'ok':      $img = '/'.root().'images/ok.png';   $type = 'status';   break;
            case 'info':    $img = '/'.root().'images/info.png'; $type = 'warning';  break;

            default: $img = null; break;
        }

        $objTPL->assign_vars(array(
            'L_MSG_TYPE' => langVar('MSG_'.strtoupper($msg_type)),
            'L_MSG'      => $message,
            'QUERY'      => $query,
            'LINE'       => 'Line: '.$line,
            'FILE'       => 'File: '.$file,
            'IMG'        => isset($img) && !is_empty($img) ? '<img src="'.$img.'" style="height: 48px; width: 48px;">' : '',
            'ALIGN'      => 'center',
            'TYPE'       => $type,
        ));

        $gen_time = '0';
        $objTPL->parse('__msgBody');

        if($footer){
            $objPage->showFooter();
        }
        exit;
    }

    /**
     * Displays the header with an error.
     *
     * @version  1.0
     * @since    0.8.0
     */
    function hmsgDie($type, $msg){
        $objPage = Core_Classes_coreObj::getPage();

        // $doSimple = false;
        // if(HTTP_AJAX || isset($_GET['ajax']) || $objPage->getVar('simpleTpl')){
        //     $doSimple = true;
        // }

        $objPage->showHeader();
        msgDie($type, $msg, '', '', '');
    }

/**
  //
  //--bbcode_tags.php
  //
**/
    /**
     * Grab File Extension, Language and GeSHi Information
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   string $ext
     * @param   string $return
     *
     * @return  array
     */
    function grabLangInfo($ext, $return='ALL'){
        $lang = (isset($ext) && $ext !== NULL) ? strtolower($ext) : 'text';
        switch ($lang) {
            case 'htaccess':            $geshi = 'apache';              $ext = 'htaccess';              $lang = 'Apache CFG File';          break;
            case 'action script':       $geshi = 'actionscript';        $ext = 'as';                    $lang = 'Action Script';            break;

            case 'php':                 $geshi = 'php';                 $ext = 'php';                   $lang = 'PHP';                      break;

            case 'js':                  $geshi = 'javascript';          $ext = 'js';                    $lang = 'Javascript';               break;
            case 'jscript':             $geshi = 'javascript';          $ext = 'js';                    $lang = 'Javascript';               break;
            case 'javascript':          $geshi = 'javascript';          $ext = 'js';                    $lang = 'Javascript';               break;

            case 'coldfusion':          $geshi = 'cfm';                 $ext = 'cfm';                   $lang = 'ColdFusion';               break;
            case 'cfm':                 $geshi = 'cfm';                 $ext = 'cfm';                   $lang = 'ColdFusion';               break;

            case 'asp':                 $geshi = 'asp';                 $ext = 'asp';                   $lang = 'Active Server Page(ASP)';  break;

            case 'c':                   $geshi = 'c';                   $ext = 'c';                     $lang = 'C';                        break;

            case 'css':                 $geshi = 'css';                 $ext = 'css';                   $lang = 'CSS';                      break;

            case 'cpp':                 $geshi = 'cpp';                 $ext = 'cpp';                   $lang = 'C++';                      break;
            case 'c++':                 $geshi = 'cpp';                 $ext = 'cpp';                   $lang = 'C++';                      break;

            case 'c#':                  $geshi = 'csharp';              $ext = 'cs';                    $lang = 'C#';                       break;
            case 'csharp':              $geshi = 'csharp';              $ext = 'cs';                    $lang = 'C#';                       break;
            case 'cs':                  $geshi = 'csharp';              $ext = 'cs';                    $lang = 'C#';                       break;

            case 'html':                $geshi = 'html';                $ext = 'html';                  $lang = 'HTML';                     break;

            case 'pl':                  $geshi = 'perl';                $ext = 'pl';                    $lang = 'Perl';                     break;
            case 'perl':                $geshi = 'perl';                $ext = 'pl';                    $lang = 'Perl';                     break;

            case 'vb':                  $geshi = 'vb';                  $ext = 'vb';                    $lang = 'Visual Basic';             break;
            case 'vbs':                 $geshi = 'vbs';                 $ext = 'vbs';                   $lang = 'Visual Basic Script';      break;
            case 'vbnet':               $geshi = 'vbnet';               $ext = 'vb';                    $lang = 'Visual Basic.net';         break;
            case 'vb.net':              $geshi = 'vbnet';               $ext = 'vb';                    $lang = 'Visual Basic.net';         break;

            case 'asm':                 $geshi = 'asm';                 $ext = 'asm';                   $lang = 'ASM';                      break;

            case 'rb':                  $geshi = 'ruby';                $ext = 'rb';                    $lang = 'Ruby';                     break;

            case 'py':                  $geshi = 'python';              $ext = 'py';                    $lang = 'Python';                   break;
            case 'python':              $geshi = 'python';              $ext = 'py';                    $lang = 'Python';                   break;

            case 'pas':                 $geshi = 'pascal';              $ext = 'p';                     $lang = 'Pascal';                   break;

            case 'sh':                  $geshi = 'bash';                $ext = 'sh';                    $lang = 'Bash';                     break;

            case 'dos':                 $geshi = 'dos';                 $ext = 'bat';                   $lang = 'Batch';                    break;
            case 'batch':               $geshi = 'dos';                 $ext = 'bat';                   $lang = 'Batch';                    break;

            case 'java':                $geshi = 'java';                $ext = 'java';                  $lang = 'Java';                     break;
            case 'jsp':                 $geshi = 'java';                $ext = 'java';                  $lang = 'Java';                     break;

            case 'mysql':               $geshi = 'mysql';               $ext = 'sql';                   $lang = 'mySQL';                    break;

            case 'xml':                 $geshi = 'xml';                 $ext = 'xml';                   $lang = 'XML';                      break;

            case 'mirc':                $geshi = 'mirc';                $ext = 'mirc';                  $lang = 'mIRC Scripting';           break;

            case 'tpl':                 $geshi = 'smarty';              $ext = 'tpl';                   $lang = 'SMARTY';                   break;

            case 'whitespace':          $geshi = 'ws';                  $ext = 'ws';                    $lang = 'Whitespace';               break;

            case 'lol':                 $geshi = 'lolcode';             $ext = 'lol';                   $lang = 'LOLcode';                  break;
            case 'lolcode':             $geshi = 'lolcode';             $ext = 'lol';                   $lang = 'LOLcode';                  break;

            default:                    $geshi = 'text';                $ext = 'txt';                   $lang = 'Text';                     break;
        }
        $_return = array('ext' => $ext, 'lang'    => $lang, 'geshi' => $geshi);
        if($return != 'ALL' && isset($_return[$return])){
            return $_return[$return];
        }
        return $_return;
    }


//Various functions for those rulez
function doCode($content, $name=NULL, $lineNumbers=false, $killWS=true){
    $lang = (isset($name) && $name!==NULL ? strtolower($name) : 'text');

    $extInfo    = grabLangInfo($lang);
    $ext        = doArgs('ext', null, $extInfo);
    $lang       = doArgs('lang', null, $extInfo);
    $geshiExt   = doArgs('geshi', null, $extInfo);

    if(is_empty($content)){
        $lang = isset($lang) ? '='.$params.'' : '';
        return "[code$lang][/code]";
    }

    $content    = html_entity_decode(trim($content));
    $content    = str_replace(array("<br />", "\t", '    '), array('', '    ', "\t"), $content);

    if($killWS){
        $content = preg_replace('/[\n\r]+/', "\n", $content);
    }


    if(!$lineNumbers){
        if($ext!='php'){
            $geshi = Core_Classes_coreObj::getLib('GeSHi', array($content, $geshiExt));

            $geshi->set_header_type(GESHI_HEADER_PRE);
            $content = $geshi->parse_code();
        }

        if($ext=='php'){/*
            if(preg_match("#<\?[^php]#", $content))
                $content = str_replace("<?", "<?php", $content);

            if(!preg_match("#<(\?php|\?)#", $content))
                $content = "<?php".$content;

            if(!preg_match("#\?>#", $content))
                $content = $content."?>";

        */}
    }else{
        $geshi = Core_Classes_coreObj::getLib('GeSHi', array($content, $geshiExt));
        $geshi->set_header_type(GESHI_HEADER_PRE);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
        $content = $geshi->parse_code();
    }


    return "\n<div class=\"bbcode_code\">\n<div class=\"bbcode_code_head\">".$lang." Code: </div>\n<div class=\"bbcode_code_body\">".
                ($ext=='php'
                    ? (!$lineNumbers
                            ? highlight_string($content, true)
                            : $content)
                    : $content).
            "</div>\n</div>\n";
}

function bbcode_user_profile($bbcode, $action, $name, $default, $params, $content){
    global $objUser;

    if ($action == BBCODE_CHECK){ return true; }
    $link = isset($default) ? $default : 0;
    $a = $objUser->profile($content);
    $return = $a ? $a : 'Guest';
    return $return;
}

function bbcode_you($bbcode, $action, $name, $default, $params, $content){
    global $objUser;

    if ($action == BBCODE_CHECK){ return true; }

    #if($tag['_tag']=='you'){
        return $objUser->profile($objUser->grab('id'));
    #}
}

function bbcode_quote($bbcode, $action, $name, $default, $params, $content) {
    global $objUser;

    if($action == BBCODE_CHECK){ return true; }
    if(doArgs('name', false, $params)){
        $title = $objUser->profile($params['name'], RETURN_USER). ' wrote';
        if(doArgs('date', false, $params)){
            $title .= ' on '.secureMe(trim($params['date']));
        }
        $title .= ':';
        if(doArgs('url', false, $params)) {
            $url = trim($params['url']);
            if($bbcode->IsValidURL($url)){
                $title = '<a href="'.secureMe($params['url']).'">'.$title.'</a>';
            }
        }
    }else if(!is_string($default)){
        $title = 'Quote:';
    }else{
        $title = $objUser->profile($default, RETURN_USER). ' wrote';
    }

    return "\n<div class=\"bbcode_quote\">\n<div class=\"bbcode_quote_head\">"
    . $title . "</div>\n<div class=\"bbcode_quote_body\">"
    . $content . "</div>\n</div>\n";
}

/**
 * Execute a method within a module / class that you wouldn't normally have access to.
 *
 * @version 1.0
 * @since   1.0
 * @author  Daniel Noel-Davies
 *
 * @param   string  $class
 * @param   string  $method
 * @param   array   $parameters
 *
 * @return  mixed
 */
function reflectMethod( $class, $method, $parameters = array()) {
    if( !class_exists( $class ) ) {
        trigger_error( 'The class you are trying to call, dosen\'t exist.' );
        return false;
    }

    // Check the class and subsequent method are callable, else trigger an error
    if ( !method_exists( $class, $method ) || !is_callable( array( $class, $method ) ) ) {
        trigger_error( 'The method you are trying to call, dosen\'t exist or is not callable.' );
        return false;
    }

    // Retrieve the info we need about the class and method
    $refMethod = new ReflectionMethod( $class, $method );
    $params    = $refMethod->getParameters( );
    $args      = array();

    // Loop through the parameters the method asks for,
    //  and match them up with our arguments where possible
    foreach( $params as $k => $name ) {
        $var = $name->getName();

        // check if the var they asked for is in the params
        if(!isset($parameters[$var])){
            $args[$var] = null;
            continue;
        }

        // and then check if we have to throw the var at them as a reference
        if($name->isPassedByReference()){
            $args[$var] = &$parameters[$var];
        }else{
            $args[$var] = $parameters[$var];
        }
    }

    $objModule = new $class;
    $objModule->setVars(array(
        '_method' => $method,
        '_module' => $class,
        '_params' => $parameters,
    ));

    try {
        return $refMethod->invokeArgs( $objModule , $args );

    } catch( Exception $e ) {
        trigger_error( $e->getMessage() );
        return false;
    }
}

/**
 * Return a new instance of a class usually out of current scope
 *
 * @version 1.0
 * @since   1.0
 * @author  Daniel Noel-Davies
 *
 * @param   string  $class
 *
 * @return  object
 */
function reflectClass( $class ) {

    try {
        $object = new ReflectionClass( $class );

    } catch( Exception $e ) {
        trigger_error( $e->getMessage() );
    }

    if( is_object( $object ) ) {
        return $object->newInstance();

    } else {
        trigger_error( $e->getMessage() );
    }
}

?>