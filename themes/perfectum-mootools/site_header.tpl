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
                        <li class="dropdown hidden-phone">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="icon-warning-sign icon-white"></i> <span class="label label-important hidden-phone">2</span> <span class="label label-success hidden-phone">11</span>
                            </a>
                            <ul class="dropdown-menu notifications">
                                <li> <span class="dropdown-menu-title">You have 11 notifications</span> </li>

                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>
                                <li> <a href="#"> + <i class="icon-user"></i> <span class="message">Log info here</span> <span class="time">1 mins</span> </a> </li>

                                <li> <a class="dropdown-menu-sub-footer">View all notifications</a> </li>
                            </ul>
                        </li>


                        <li class="dropdown hidden-phone">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="icon-envelope icon-white"></i> <span class="label label-success hidden-phone">9</span>
                            </a>
                            <ul class="dropdown-menu messages">
                                <li> <span class="dropdown-menu-title">You have 9 messages</span> </li>

                                <li>
                                    <a href="#">
                                        <span class="avatar"><img src="img/avatar.jpg" alt="Avatar"></span>
                                        <span class="header">
                                            <span class="from">
                                                Łukasz Holeczek
                                             </span>
                                            <span class="time">
                                                6 min
                                            </span>
                                        </span>
                                        <span class="message">
                                            Lorem ipsum dolor sit amet consectetur adipiscing elit, et al commore
                                        </span>
                                    </a>
                                </li>

                                <li> <a class="dropdown-menu-sub-footer">View all messages</a> </li>
                            </ul>
                        </li>


                        <li>
                            <a class="btn" href="#">
                                <i class="icon-wrench icon-white"></i>
                            </a>
                        </li>

                        <li class="dropdown">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="icon-user icon-white"></i>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="#"><i class="icon-home"></i> Site Home</a></li>
                                <li><a href="#"><i class="icon-user"></i> Profile</a></li>
                                <li><a href="login.html"><i class="icon-signout"></i> Logout</a></li>
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