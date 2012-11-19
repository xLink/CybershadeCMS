<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class AdminCP extends coreObj{

    public function __construct(){

        $this->mode   = doArgs('__mode',      null,         $_GET);
        $this->module = doArgs('__module',    'admin',      $_GET);
        $this->action = doArgs('__action',    'dashboard',  $_GET);
        $this->extra  = doArgs('__extra',     null,         $_GET);
        echo dump($_GET);
    }

    public function output(){
        $objTPL = coreObj::getTPL();

        if( !$objTPL->isHandle('body') ){
            msgDie('FAIL', 'No output received from module.');
        }else{
            echo $objTPL->get_html('body');
        }

    }

    public function invokeRoute(){
        // Get instanced
        $objRoute = coreObj::getRoute();
        
        $this->module = 'Admin_'.$this->module;
        
        // try {
        //     reflectMethod($this->module, $this->action, $this->extras);
        // } catch ( Exception $e ) {
        //     $objRoute->throwHTTP(404);
        // }

        // Or try

        $method = reflectMethod($this->module, $this->action, $this->extras);

        if( !$method ) {
            $objRoute->throwHTTP(404);
        }
    }
}
?>