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

//Lets set a simple error template up till we have the template engine going
$errorTPL = '<h3>%s</h3> <p>%s Killing Process...</p>';
@set_magic_quotes_runtime(false);

    // we need constants.php
    $file = $cmsROOT.'core/constants.php';
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to locate/read the constants file.'));
        }else{ require_once($file); }

//error_reporting(LOCALHOST ? E_ALL & ~E_NOTICE | E_STRICT : 0);
error_reporting(E_ALL & ~E_NOTICE | E_STRICT);

    $file = cmsROOT.'core/debugFunctions.php';
        if(!is_readable($file) || !cmsDEBUG){
            function dump(){} function getExecInfo(){} function memoryUsage(){}
        }else{ require_once($file); }

    if(cmsDEBUG && false){
        require_once(cmsROOT.'core/php_error.php');
        \php_error\reportErrors(array(
            'snippet_num_lines'   => 20,
            'error_reporting_off' => 0,
            'error_reporting_on'  => E_ALL | E_STRICT,
            'background_text'     => 'Cybershade CMS',
        ));
    }

    //Check whether config files are present
    $file = cmsROOT.'assets/config.php';
        if(!is_file($file) || (file_get_contents($file) == '')){
            die(sprintf($errorTPL, 'Fatal Error', 'This seems to be your first time running. Are you looking for <a href="install/">Install/</a> ?'));
        }

        //make sure the file is readable, if so require it
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to read the configuration file, please ensure correct privileges are given.'));
        }else{ require_once($file); }

    //make sure we are running a compatible PHP Version
    if(PHP_VERSION_ID < '50300'){
        die(sprintf($errorTPL, 'Fatal Error - 500',
            'This server is not capable of running this CMS, please upgrade PHP to version 5.3+ before trying to continue.'));
    }

    // spawn the session now we got the config
    if( !isset($_SESSION) ){
        if( isset($config['db']['ckefix']) ){
            session_name( md5($config['db']['ckefix']) );
        }
        session_start();
    }


    $file = cmsROOT.'core/baseFunctions.php';
        if(!is_readable($file)){
            die(sprintf($errorTPL, 'Fatal Error - 404', 'We have been unable to locate/read the baseFunctions file.'));
        }else{ require_once($file); }

//kill magic quotes completely
if(get_magic_quotes_gpc() != false){

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

/**
//
//-- Classes Setup
//
**/
$loader = require_once(cmsROOT.'vendor/autoload.php');
//require_once(cmsROOT.'core/classes/coreobj.php');

// AUTOLOADER, I Choose You!
spl_autoload_extensions('.php');
spl_autoload_register(array($loader, 'loadClass'));
//spl_autoload_register(array('coreObj', 'loadClass'));

    $objCore    = new CSCMS\Core\Classes\coreObj;
        $objCore->addConfig($config);
// echo dump($dirs, 'Loading Classes From', 'orange');exit;

    // Instance plugins so we can add hooks as early as possible.
    $objPlugin  = $objCore::getPlugins();

$objPlugin->hook('CMS_PRE_SETUP_COMPLETE');

    $objCache   = $objCore::getCache();

    $confCache  = $objCache->load( 'config' );
        $objCore->addConfig($confCache);

    $objSession = $objCore::getSession();
        $objSession->trackerInit();

    $objDebug   = $objCore::getDebug();
    $objRoute   = $objCore::getRoute();
        $objRoute->modifyGET();

        if( is_object($objDebug) ){
            set_error_handler(array($objDebug, 'errorHandler'));
        }

(cmsDEBUG ? memoryUsage('Core: Loaded..') : '');

$objPlugin->hook('CMS_SETUP_COMPLETE');


?>