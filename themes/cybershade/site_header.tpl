<!DOCTYPE html>
<html lang="en" class="no-js">
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
            <nav class="pull-right"><ul>
                  <li><a href="#">Github</a></li>
                  <li><a href="#">Facebook</a></li>
                  <li><a href="#">Twitter</a></li>
            </ul></nav>
        </div>
    </div>

    <div id="nav" class="navbar"><div class="navbar-inner">
        <div class="container">
            <ul class="nav">
                <li><a href="#">Home</a></li>
                <li><a href="#">Link</a></li>
                <li><a href="#">Link</a></li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Dropdown <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li class="nav-header">Nav header</li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </div></div>


</header>

<section class="container">
    <div class="row">

        <aside class="span4">
            <div class="menu">
                <header>Main Menu</header>
                <section>
                    <ul class="nav nav-list">
                        <li class="nav-header">List header</li>
                        <li class="active"><a href="#">Home</a></li>
                        <li><a href="#">Library</a></li>
                        <li><a href="#">Applications</a></li>
                        <li class="nav-header">Another list header</li>
                        <li><a href="#">Profile</a></li>
                        <li><a href="#">Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Help</a></li>
                    </ul>
                </section>
            </div>

            <div class="menu">
                <header>Main Menu (no-padding)</header>
                <section class="no-padding">
                    <ul class="nav nav-list">
                        <li class="nav-header">List header</li>
                        <li class="active"><a href="#">Home</a></li>
                        <li><a href="#">Library</a></li>
                        <li><a href="#">Applications</a></li>
                        <li class="nav-header">Another list header</li>
                        <li><a href="#">Profile</a></li>
                        <li><a href="#">Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Help</a></li>
                    </ul>
                </section>
            </div>

            <div class="menu">
                <header>Latest Posts</header>
                <section>

                </section>
            </div>
        </aside>

        <div id="pageContent" class="span8">
            {THEME_TESTER}
        </div>
    </div>
</section>

<footer>

</footer>

{_JS_FOOTER}
</body>
</html>