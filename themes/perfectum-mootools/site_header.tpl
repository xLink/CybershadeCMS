<!DOCTYPE html>
<html lang="en" class="{_CSS_SELECTORS}">
<head>
{_META}
<title>{PAGE_TITLE} || {SITE_TITLE}</title>
{_CSS}
{_JS_HEADER}
</head>

<body class="{PAGE_CLASS}">

    <div class="navbar hidden-phone">
        <div class="navbar-inner">
            <div class="container-fluid">
                <a class="brand" href="index.html"> <span>Cysha CMS ACP</span></a>

                <div class="nav-no-collapse header-nav">
                    <ul class="nav pull-right">
                        <li class="dropdown">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="icon-user icon-white"></i> {USERNAME}
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/{ROOT}"><i class="icon-home"></i> Site Home</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="container-fluid">
    <div class="row-fluid">

        <div class="span2 main-menu-span">
            <div class="nav-collapse sidebar-nav">
                <ul class="nav nav-tabs nav-stacked main-menu">
                    {ACP_NAV}
                    <!-- BEGIN menu -->

                        <!-- BEGIN normal -->
                        <li>
                            <a href="{menu.normal.URL}">
                                <!-- BEGIN icons -->
                                <i class="{menu.normal.icons.ICON} icon-white"></i>
                                <!-- END icons -->
                                <span class="hidden-tablet">{menu.normal.TITLE}</span>
                            </a>
                        </li>
                        <!-- END normal -->

                        <!-- BEGIN dropdown -->
                        <li>
                            <a class="dropmenu" href="#">
                                <!-- BEGIN icons -->
                                <i class="{menu.dropdown.icons.ICON} icon-white"></i>
                                <!-- END icons -->
                                <span class="hidden-tablet">{menu.dropdown.TITLE} <i class="icon-sort-down"></i></span>
                            </a>
                            <ul class="dropmenu">
                                <!-- BEGIN subnav -->
                                <li>
                                    <a class="submenu" href="{menu.dropdown.subnav.URL}">
                                        <!-- BEGIN icons -->
                                        <i class="{menu.dropdown.subnav.icons.ICON} icon-white"></i>
                                        <!-- END icons -->
                                        <span class="hidden-tablet"> {menu.dropdown.subnav.TITLE}</span>
                                    </a>
                                </li>
                                <!-- END subnav -->
                            </ul>
                        </li>
                        <!-- END dropdown -->

                    <!-- END menu -->
                </ul>
            </div>
        </div>

        <noscript>
            <div class="alert alert-block span10">
                <h4 class="alert-heading">Warning!</h4>
                <p>You need to have <a href="http://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> enabled to use this site.</p>
            </div>
        </noscript>

        <div id="content" class="span10">


        <!-- BEGIN breadcrumbs -->
        <nav id="breadcrumbs">
            <hr /><ul>
            <!-- BEGIN item -->
                <li class="breadcrumb"> <a href="{breadcrumbs.item.URL}">{breadcrumbs.item.NAME}</a> <span class="divider">/</span> </li>
            <!-- END item -->
            </ul><hr />
        </nav>
        <!-- END breadcrumbs -->