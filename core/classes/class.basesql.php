<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * SQL Class interface, defines the needed functionality for the SQL Drivers
 *
 * @version     1.0
 * @since       1.0.0
 * @author      Dan Aldridge
 */
interface Core_Classes_baseSQL{

    public function __construct($config);

    public static function getInstance($name=null, $options=array());

    public function selectDB($db);
    public function connect();
    public function disconnect();
    public function getError();

    public function escape($string);
    public function freeResult();
    public function query($query);
    public function results($key);
    public function affectedRows();
    public function fetchInsertId();

}

?>