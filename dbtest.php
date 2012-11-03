<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
define('INDEX_CHECK', true);
define('cmsDEBUG', true);
include_once('core/core.php');

$objPage->setTheme();

$objPage->setTitle('Test');

$objPage->buildPage();
$objPage->showHeader();

    $tests = array();

    $objSQL = coreObj::getDBO();

    $query = $objSQL->queryBuilder()
        ->select(array('u.id', 'user_id' => 'ux.uid'))
        ->addField('u.username')
        ->addField('birthday')
        ->from(array('u' => '#__ussers'))
        ->leftJoin(array('ux' => '#__users_extras'))
            ->on('u.id', '=', 'ux.uid')
        ->where('u.id', '=', '1')
        ->orderBy('u.id', 'ASC')
        ->build();


    $test[$query][$objSQL->getClassName()]          = $objSQL->fetchAll($query);

    $test[$query][$objSQL->getClassName().'_error'] = $objSQL->getError();

    $test[$query][$objSQL->getClassName().'_rows']  = $objSQL->AffectedRows();


    echo dump($test, $objSQL->_query);

$objPage->showFooter();
?>