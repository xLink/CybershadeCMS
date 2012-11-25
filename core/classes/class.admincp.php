<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class AdminCP extends coreObj{

    public function __construct($name, $options=array()){

        $this->mode   = doArgs('__mode',      null,         $options);
        $this->module = doArgs('__module',    'core',       $options);
        $this->action = doArgs('__action',    'dashboard',  $options);
        $this->extra  = doArgs('__extra',     null,         $options);

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

        $action = array($this->action);
        if( strpos($this->action, '/') !== false ){
            $action = explode('/', $this->action);
        }

        $method = reflectMethod($this->module, array_shift($action), $action);

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
                'icon' => 'dashboard',
                'url'  => $acpROOT,
            ),
            'System' => array(
                'icon' => 'cog',
                'subs' => array(
                    'Site Configuration' => array(
                        'icon' => 'search',
                        'url'  => $acpROOT.'core/config/',
                    ),
                    '' => array(
                        'icon' => 'search',
                        'url'  => $acpROOT.'',
                    ),
                )

            ),
            'Users' => array(
                'icon' => 'user',
                'subs' => array(
                    'Search' => array(
                        'icon' => 'search',
                        'url'  => $acpROOT.'core/users/search/',
                    ),
                    'Manage Users' => array(
                        'icon' => 'cogs',
                        'url'  => $acpROOT.'core/users/manage/',
                    ),
                    'Add New User' => array(
                        'icon' => 'search',
                        'url'  => $acpROOT.'core/users/add/',
                    ),
                )

            ),
            '--' => array(),
            'Modules' => array(
                'icon' => 'sign-blank',
                'url'  => $acpROOT,
            ),
            'Themes' => array(
                'icon' => 'picture',
                'url'  => $acpROOT,
            ),
            'Blocks' => array(
                'icon' => 'check-empty',
                'url'  => $acpROOT,
            ),
            'Plugins' => array(
                'icon' => 'folder-close',
                'url'  => $acpROOT,
            ),
            'Languages' => array(
                'icon' => 'globe',
                'url'  => $acpROOT,
            ),
        );

        $objTPL->assign_var('ACP_NAV', $this->generateNav($nav));
    }

    private function generateNav( $links=array() ){

        if( !count($links) ){
            return null;
        }

        $linkTPL = '<li><a href="%s"><i class="faicon-%s faicon-white"></i><span class="hidden-tablet"> %s</span></a>%s</li>';
        $subTPL = '<ul> %s </ul>';
        $spacerTPL = '<li> &nbsp; </li>';

        $output = null;
        foreach($links as $label => $link){

            if( doArgs('subs', false, $link) ){
                $subNav = sprintf( $subTPL, self::generateNav($link['subs']) );
                $output .= sprintf( $linkTPL, 'javascript:;', $link['icon'], $label, $subNav );

            }else if( $label === '--' ){
                $output .= $spacerTPL;


            }else if( doArgs('url', false, $link) ){
                $output .= sprintf( $linkTPL, $link['url'], $link['icon'], $label, null );

            }

        }

        return $output;
    }


    public function getNotifications(){

    }














}
?>