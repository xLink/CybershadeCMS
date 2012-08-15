<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

//Added BBcode Rules
$objBBCode->CloneBB('url', 'link');
$objBBCode->CloneBB('url', 'linkit');

$objBBCode->AddRule('user', array(
    'mode'     => BBCODE_MODE_CALLBACK,
    'method'   => 'bbcode_user_profile',
    'class'    => 'link',
    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link'),
    'content'  => BBCODE_VERBATIM,
    'end_tag'  => BBCODE_REQUIRED,
));

$objBBCode->AddRule('noparse', array(
    'mode'     => BBCODE_MODE_SIMPLE,
    'class'    => 'inline',
    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link'),
    'content'  => BBCODE_VERBATIM,
    'end_tag'  => BBCODE_REQUIRED,
));

$objBBCode->AddRule('nosmilies', array(
    'mode'     => BBCODE_MODE_SIMPLE,
    'class'    => 'inline',
    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link'),
    'content'  => BBCODE_VERBATIM,
    'end_tag'  => BBCODE_REQUIRED,
));

$objBBCode->AddRule('small', array(
    'simple_start' => '<small>',
    'simple_end'   => '</small>',
    'class'        => 'inline',
    'allow_in'     => array('listitem', 'block', 'columns', 'inline', 'link'),
    'plain_start'  => '<small>',
    'plain_end'    => '</small>',
));

$objBBCode->AddRule('pre', array(
    'mode'         => BBCODE_MODE_SIMPLE,
    'end_tag'      => BBCODE_REQUIRED,
    'simple_start' => '<pre>',
    'simple_end'   => '</pre>',
    'allow_in'     => array('listitem', 'block', 'columns', 'inline'),
));

$objBBCode->AddRule('quote', array(
    'mode'          => BBCODE_MODE_CALLBACK,
    'method'        => "bbcode_quote",
    'allow_in'      => Array('listitem', 'block', 'columns'),
    'before_tag'    => "sns",    'after_tag' => "sns",
    'before_endtag' => "sns",    'after_endtag' => "sns",
    'plain_start'   => "\n<b>Quote:</b>\n",
    'plain_end'     => "\n",
));

$objBBCode->AddRule('you', array(
    'mode'     => BBCODE_MODE_CALLBACK,
    'end_tag'  => BBCODE_PROHIBIT,
    'content'  => BBCODE_PROHIBIT,
    'method'   => 'bbcode_you',
    'class'    => 'link',
    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link'),
));

$x = 0;
while($x <= 6){
    $objBBCode->AddRule('h'.$x, Array(
        'simple_start' => '<h'.$x.'>',
        'simple_end'   => '</h'.$x.'>',
        'class'        => 'inline',
        'allow_in'     => Array('listitem', 'block', 'columns', 'inline', 'link'),
    ));
    $x++;
}

//load smilies in
$pack = is_empty($objCore->config('site', 'smilie_pack')) ? $objCore->config('site', 'smilie_pack') : 'default';
$smilieDir = cmsROOT.'images/smilies/'.$pack.'/';
if(is_dir($smilieDir) && is_readable($smilieDir.'smilies.txt')){
    $smilies = file($smilieDir.'smilies.txt');
    if(count($smilies)){
        foreach($smilies as $line){
            $s = explode(' ', $line);
            if(!isset($s[0]) || !isset($s[1])){ continue; }
            $objBBCode->AddSmiley($s[0], $pack.'/'.$s[1]);
        }
    }
}


?>