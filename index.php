<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');


$query = $objSQL->query(true)
            ->select(array('u.id', 'user_id' => 'ux.uid'))
            ->addField('u.username')
            ->addField('birthday')
            ->from(array('u' => '#__users'))
            ->leftJoin(array('ux' => '#__users_extras'))
                ->on('u.id = ux.uid')
            ->where('u.id = 1')
            ->orderBy('u.id', 'ASC')
            ->build();

$a = $objSQL->getTable($query);
echo dump($a, $query);

$query = $objSQL->query(true)
            ->select(array('u.id', 'user_id' => 'ux.uid'))
            ->addField('u.username')
            ->addField('birthday')
            ->from(array('u' => '#__users'))
            ->leftJoin(array('ux' => '#__users_extras'))
                ->on('u.id = ux.uid')
            ->where('u.id', '=', '1')
            ->orderBy('u.id', 'ASC')
            ->build();


$a = $objSQL->getTable($query);
echo dump($a, $query);

exit;
$a = $objSQL->getCount('#__users', 'id = 1');
echo dump($a, $objSQL->_query);
?>