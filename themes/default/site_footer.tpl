		</div>
		
	<!-- BEGIN menu -->
        <aside id="sidebar" class="grid_3">
    <!-- END menu -->
            <!-- BEGIN left_menu -->
            <div class="sideMenu">
                <header class="title"><h4>{left_menu.TITLE}</h4></header>
                <section class="content corners-br">{left_menu.CONTENT}</section>
            </div>
            <!-- END left_menu -->
            <!-- BEGIN right_menu -->
            <div class="sideMenu">
                <header class="title"><h4>{right_menu.TITLE}</h4></header>
                <section class="content corners-br">{right_menu.CONTENT}</section>
            </div>
            <!-- END right_menu -->
    <!-- BEGIN menu -->
        </aside>
    <!-- END menu -->

    </section>

    <footer>
        {L_PAGE_GEN}<br />{L_SITE_COPYRIGHT}<br />{L_TPL_INFO}
    </footer>
<!-- BEGIN debug -->
    <div id="pageContent" class="grid_12">
        <!-- BEGIN graphs -->
        
        <div style="display:table; float:left; margin: 20px;">
            Query Exec Time<br />
            {debug.graphs.queryTimer}
        </div>
        
        <div style="display:table; float:left; margin: 20px;">
            Page Generation Time<br />
            {debug.graphs.pageGen}
        </div>
        
        <div style="display:table; float:left; margin: 20px;">
            Memory Usage<br />
            {debug.graphs.ramUsage}
        </div>
        
        <div class="clear"></div>
        <!-- END graphs -->
        
        <table width="100%" border="0">
            <tr class="thead padding">
                <td width="5%">Q Time</td>
                <td>Query</td>
            </tr>
        <!-- BEGIN info -->
            <tr class="{debug.info.CLASS}">
                <td align="center" valign="middle">{debug.info.TIME}</td>
                <td class="padding">{debug.info.QUERY}</td>
            </tr>
        <!-- END info -->
        </table>

        {debug.DEBUG}
    </div>    
<!-- END debug -->
</div></div>
<div id="bg-strip">&nbsp;</div>
<div id="spinner_"><img alt="spinner" id="spinner" src="/{ROOT}/images/ajax-loader.gif" /></div>
{_JS_FOOTER}
</body>
</html>