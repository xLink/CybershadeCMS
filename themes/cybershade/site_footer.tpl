    </div>
</section>

<footer>
<div style="height: 200px;"></div>
<!-- BEGIN debug -->
    <div class="container">

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

        {debug.DEBUG}
    </div>
<!-- END debug -->
</footer>


{_JS_FOOTER}
</body>
</html>