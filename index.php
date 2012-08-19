<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$tests = array();

$sql  = coreObj::getDBO('mysql');

if($objSQL->getClassName() == $sql->getClassName()){
    die('Same Class Name ;x');
}
/*$query = $objSQL->query(true)
            ->select(array('u.id', 'user_id' => 'ux.uid'))
            ->addField('u.username')
            ->addField('birthday')
            ->from(array('u' => '#__users'))
            ->leftJoin(array('ux' => '#__users_extras'))
                ->on('u.id', '=', 'ux.uid')
            ->where('u.id', '=', '1')
            ->orderBy('u.id', 'ASC')
            ->build();*/

$query = $objSQL->query(true)->select('*')->from('#__config')->build();

$a = $objSQL->config('db');

$test[$query][$objSQL->getClassName()]          = $objSQL->getColumnData('#__users', 'Field');
$test[$query][$sql->getClassName()]             =    $sql->getColumnData('#__users', 'Field');
$test[$query][$objSQL->getClassName().'_error'] = $objSQL->getError();
$test[$query][$sql->getClassName().'_error']    =    $sql->getError();
$test[$query][$objSQL->getClassName().'_rows']  = $objSQL->AffectedRows();
$test[$query][$sql->getClassName().'_rows']     =    $sql->AffectedRows();


echo dump($test, $objSQL->_query);
exit;
$a = $objSQL->getCount('#__users', 'id = 1');
echo dump($a, $objSQL->_query);
?>