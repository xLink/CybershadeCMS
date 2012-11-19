<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Admin_core extends Module{

    public function __construct(){

    }

/**
  //
  //-- Dashboard Section
  //
**/
    public function dashboard(){
        echo __METHOD__;
    }


/**
  //
  //-- Site Configuration
  //
**/
    public function siteConfiguration(){
        echo __METHOD__;
    }

/**
  //
  //-- User Admin Section
  //
**/
    public function rawr(){
        echo __METHOD__;
    }

    //public function index() {}

}
?>