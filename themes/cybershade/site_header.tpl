<!DOCTYPE html>
<html lang="en" class="{_CSS_SELECTORS}">
<head>
{_META}
<title>{PAGE_TITLE} || {SITE_TITLE}</title>
{_CSS}
{_JS_HEADER}
</head>

<body class="{PAGE_CLASS}">

<header>
    <div class="container">
        <div id="topbar">
            <div class="logo"> Cybershade Inc </div>
            <div class="pull-right hidden-phone share-wrapper">
                <div class="share-action socicon-share"></div>
                <div class="share-container">
                    <a href="http://facebook.com/CSCMS" class="share-btn tl socicon-facebook"></a>
                    <a href="http://twitter.com/CybershadeCMS" class="share-btn tr socicon-twitter"></a>
                    <a href="http://github.com/Cybershade" class="share-btn bl socicon-github"></a>
                    <a href="contact.php" class="share-btn br socicon-envelope"></a>
                </div>
            </div>
        </div>
    </div>

    <div id="nav" class="navbar"><div class="navbar-inner">
        <div class="container">
            <ul class="nav">
                <li><a href="/{ROOT}">Home</a></li>
                <li><a href="/{ROOT}forum">Forum</a></li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Downloads <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">CMS v1.0</a></li>
                        <li><a href="#">Themes</a></li>
                        <li><a href="#">Modules</a></li>
                        <li><a href="#">Plugins</a></li>
                        <li><a href="#">Languages</a></li>
                        <li class="divider"></li>
                        <li class="nav-header">Nav header</li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>
                <li><a href="#">Pastebin</a></li>
                <li><a href="{U_LOGIN}">{L_LOGIN}</a></li>
            </ul>

        </div>
    </div></div>

    <div class="clearfix"></div>
</header>

<section class="container">
    <!-- BEGIN breadcrumbs -->
    <ul class="breadcrumb">
        <!-- BEGIN item -->
            <li><a href="{breadcrumbs.item.URL}">{breadcrumbs.item.NAME}</a> <span class="divider">/</span></li>
        <!-- END item -->
    </ul>
    <!-- END breadcrumbs -->

    <div class="row">


    <aside class="span4">
    <!-- BEGIN _CMSBLOCK -->
            <div class="row">
                {_CMSBLOCK.LEFT_MENU}
            </div>
    <!-- END _CMSBLOCK -->
    </aside>


    <!-- BEGIN menu -->
        <div id="pageContent" class="span8">
    <!-- END menu -->
    <!-- //BEGIN no_menu -->
        <div id="pageContent" class="span12">
    <!-- //END no_menu -->
