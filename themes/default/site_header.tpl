<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
{_META}
<title>{PAGE_TITLE} || {SITE_TITLE}</title>
{_JS_HEADER}
{_CSS}
<link rel="stylesheet" href="/{THEME_ROOT}style.css" type="text/css" />

<!-- BEGIN no_menu -->
<style>
    section#content{ margin: 15px -5px !important; }
</style>
<!-- END no_menu -->

</head>

<body>
<div id="site-wrapper" class="container-fluid">
    <header id="banner">
    <!--[if lte IE 7]>
        <div id="topBar" class="align-center"><div class="boxred">ERROR: You are using an unsupported version of Internet Explorer. This version has been deemed useless. Please switch to an updated version of IE, or consider switching to something that can actually parse a webpage. Like <a href="http://getfirefox.com"><strong>Firefox</strong></a>!</div></div>
        <div class="clear">&nbsp;</div>
    <![endif]-->
    <!-- BEGIN __MSG -->
        <div id="topBar"><div class="boxred">{__MSG.MESSAGE}</div></div>
        <div class="clear">&nbsp;</div>
    <!-- END __MSG -->
        <div id="topBar">
            <div class="float-left">{L_WELCOME}
                <!-- BEGIN IS_ONLINE -->
                 - <a href="{U_UCP}">{L_UCP}</a>
                <!-- END IS_ONLINE -->
                {ACP_LINK}
            </div>
            <div class="float-right">
                <!-- BEGIN IS_ONLINE -->
                <a href="{U_LOGOUT}">{L_LOGOUT}</a> || 
                <!-- END IS_ONLINE -->
                <!-- BEGIN NOT_LOGGED_IN -->
                <a href="{U_LOGIN}">{L_LOGIN}</a> || 
                <!-- END NOT_LOGGED_IN -->
                <span id="clock">{TIME}</span>
            </div>
        </div>
        <div id="logo">&nbsp;</div>
        
        <nav><ul>{TPL_MENU}</ul></nav>
    </header>

    <nav id="breadcrumbs">
        <span><strong>{L_BREADCRUMB}</strong> </span><span class="path">{BREADCRUMB}</span>
    </nav>

    <section id="content" class="grid_12">
        <!-- BEGIN no_menu -->
        <div id="pageContent" class="grid_12 no_menu">
        <!-- END no_menu -->
        <!-- BEGIN menu -->
        <div id="pageContent" class="grid_9">
        <!-- END menu -->