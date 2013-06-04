<div class="row">
    <div class="span2">
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

    <div class="span10">
        {CONTENT_BODY}
    </div>
</div>
