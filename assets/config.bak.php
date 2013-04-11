<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){die('Error: Cannot access directly.');}

//some db settings and the like etc
    $config['db']['driver']        = 'mysqli';
    $config['db']['host']          = 'localhost';
    $config['db']['username']      = '';
    $config['db']['password']      = '';
    $config['db']['database']      = '';
    $config['db']['port']          = '3306';
    $config['db']['prefix']        = 'cscms_';
//the cookie prefix
    $config['db']['ckefix']        = 'CMS_';

//some settings for the cron
    $config['cron']['hourly_time'] = (3600); //1 Hour
    $config['cron']['daily_time']  = (3600*24); //1 Day
    $config['cron']['weekly_time'] = (3600*24*7); //1 Week

//some default settings, incase the cms dies before getting
//the chance to populate the config array.
    $config['cms']['name']         = 'Cybershade CMS';
    $config['cms']['version']      = 'N/A';
    $config['site']['title']       = 'Cybershade CMS';
    $config['site']['theme']       = 'default';
    $config['site']['language']    = 'en';

?>
