<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class AdminCP extends coreObj{

    public function __construct(){

        $this->mode   = doArgs('__mode',      null,         $_GET);
        $this->module = doArgs('__module',    'core',      $_GET);
        $this->action = doArgs('__action',    'dashboard',  $_GET);
        $this->extra  = doArgs('__extra',     null,         $_GET);

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

        $method = reflectMethod($this->module, $this->action, $this->extras);

        if( !$method ) {
            $objRoute->throwHTTP(404);
        }
    }

    public function getNav(){
        $objSQL = coreObj::getDBO();
        $objTPL = coreObj::getTPL();

        $acpROOT = '/'.root().'admin/';
        $nav = array(
            'Dashboard' => array(
                'icon' => 'faicon-dashboard',
                'url'  => $acpROOT,
            ),
            'System' => array(
                'icon' => 'faicon-cog',
                'subs' => array(
                    'Site Configuration' => array(
                        'icon' => 'faicon-search',
                        'url'  => $acpROOT.'core/config/',
                    ),
                    '' => array(
                        'icon' => 'faicon-search',
                        'url'  => $acpROOT.'',
                    ),
                )

            ),
            'Users' => array(
                'icon' => 'faicon-user',
                'subs' => array(
                    'Search' => array(
                        'icon' => 'faicon-search',
                        'url'  => $acpROOT.'core/users/search/',
                    ),
                )

            ),
            'Content' => array(
                'icon' => 'faicon-user',
                'url'  => $acpROOT,
            ),
        );

        $objTPL->assign_var('ACP_NAV', $this->generateNav());

    }

    private function generateNav( $links=array() ){
        return '<li>rawr</li>';
    }


    public function getNotifications(){

    }














}
?>