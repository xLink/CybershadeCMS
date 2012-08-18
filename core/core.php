<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
||                    Coding Started 12/08/2012 :)                      ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
   //
   //-- START!
   //
**/

    $START_CMS_LOAD = microtime(true); $START_RAM_USE = memory_get_usage();
    $cmsROOT = (isset($cmsROOT) && !empty($cmsROOT) ? $cmsROOT : '');

    //we need constants.php, same deal as above
    $file = $cmsROOT.'core/constants.php';
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to locate/read the constants file.'));
        }else{ require_once($file); }

    error_reporting(LOCALHOST ? E_ALL & ~E_NOTICE | E_STRICT : 0);

//(cmsDEBUG ? memoryUsage('constants & error_reporting') : '');

    $file = cmsROOT.'core/debugFunctions.php';
        if(!is_readable($file) || !cmsDEBUG){
            function dump(){} function getExecInfo(){} function memoryUsage(){}
        }else{ require_once($file); }

//(cmsDEBUG ? memoryUsage('loaded debug funcs') : '');

    if(cmsDEBUG){
        require_once(cmsROOT.'core/php_error.php');
        \php_error\reportErrors(array(
          'snippet_num_lines'   => 20,
          'error_reporting_off' => 0,
          'error_reporting_on'  => E_ALL | E_STRICT,
          'background_text'     => 'Cybershade CMS',
        ));
//(cmsDEBUG ? memoryUsage('loaded debug funcs') : '');
    }


    //Lets set a simple error template up till we have the template engine going
    $errorTPL = '<h3>%s</h3> <p>%s Killing Process...</p>';
    @set_magic_quotes_runtime(false);

    //Check whether config files are present
    $file = cmsROOT.'assets/config.php';
        if(!is_file($file) || (file_get_contents($file) == '')){
            die(sprintf($errorTPL, 'Fatal Error', 'This seems to be your first time running. Are you looking for <a href="install/">Install/</a> ?'));
        }

        //make sure the file is readable, if so require it
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to read the configuration file, please ensure correct owner privledges are given.'));
        }else{ require_once($file); }

//(cmsDEBUG ? memoryUsage('loaded config') : '');

    //make sure we are running a compatible PHP Version
    if(PHP_VERSION_ID < '50300'){
        die(sprintf($errorTPL, 'Fatal Error - 500',
            'This server is not capable of running this CMS, please upgrade PHP to version 5.3+ before trying to continue.'));
    }

    $file = cmsROOT.'core/baseFunctions.php';
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to locate/read the baseFunctions file.'));
        }else{ require_once($file); }

//(cmsDEBUG ? memoryUsage('version check & basefunctions') : '');

    //kill magic quotes completely
    if(get_magic_quotes_gpc()!==false){
//(cmsDEBUG ? memoryUsage('anti magic quotes') : '');
        
        //strip all the global arrays
        recursiveArray($_POST,    'stripslashes');
        recursiveArray($_GET,     'stripslashes');
        recursiveArray($_COOKIE,  'stripslashes');
        recursiveArray($_REQUEST, 'stripslashes');
    }

    //set the default timezone
    if(function_exists('date_default_timezone_set')){
        //This gets set to GMT, this is due to CMS handling dates automatically
        date_default_timezone_set('Europe/London'); 
    }

//(cmsDEBUG ? memoryUsage('default timezone') : '');

/**
  //
  //-- Classes Setup
  //
**/
    require_once(cmsROOT.'core/classes/class.core.php');

//(cmsDEBUG ? memoryUsage('loaded base class') : '');

    // AUTOLOADER, I Choose You!
        //directories to use for the autoloading, these get glob'd over after
        coreObj::addClassDirs(cmsROOT.'core/classes/*.php');
        coreObj::addClassDirs(cmsROOT.'core/lib/*/class.*.php');
        $dirs = coreObj::addClassDirs(cmsROOT.'modules/*/class.*.php');

// echo dump($dirs, 'Loading Classes From', 'orange');exit;
//(cmsDEBUG ? memoryUsage('autoloader dirs') : '');

    spl_autoload_extensions('.php');
    spl_autoload_register(array('coreObj', 'loadClass'));

//(cmsDEBUG ? memoryUsage('autoloader registration') : '');

// $a = get_included_files();
// echo dump($a, 'Core - Loaded Files', 'orange');

    $objCore  = new coreObj;

        //cache setup
        $cachePath = cmsROOT.'cache/';
        if(is_dir($cachePath) && !is_writable($cachePath)){ @chmod($cachePath, 0775); }
        if(!is_writable($cachePath)){
            msgDie('FAIL', sprintf($errorTPL, 'Fatal Error', 'Could not set CHMOD permissions on "<i>cache/</i>" set to 775 to continue.'));
        }

        $cacheWritable = (is_writable($cachePath) ? true : false);

    $objSQL     = coreObj::getDBO();
    #$objSession = coreObj::getSession();
    $objHooks   = new plugins;
    $objTPL     = new template;

// $a = $objModule->moduleExists('core');
// echo dump($a, 'module exists');

// $a = get_included_files();
// echo dump($a, 'Core - Loaded Files', 'orange');

//(cmsDEBUG ? memoryUsage('everything else') : '');
//

// $a = memoryUsage();
// echo dump($a, 'Exec Info');

?>