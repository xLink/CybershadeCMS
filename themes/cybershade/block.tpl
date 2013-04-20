<!-- BEGIN block -->

    <!-- BEGIN pre_end_row -->
    </div>
    <!-- END pre_end_row -->

    <!-- BEGIN start_row -->
    <div class="row-fluid">
    <!-- END start_row -->

    <!-- BEGIN 1col -->
    <div class="box span4" ontablet="span6" ondesktop="span4">
    <!-- END 1col -->
    <!-- BEGIN 2col -->
    <div class="box span8" ontablet="span12" ondesktop="span8">
    <!-- END 2col -->
    <!-- BEGIN 3col -->
    <div class="box span12">
    <!-- END 3col -->
        <div class="box-header">
            <span class="title"><i class="{block.ICON}"></i> {block.TITLE}</span>
            <ul class="box-toolbar">
                <!-- BEGIN setting -->
                    <li><a href="#" class="btn-setting"><i class="icon-wrench"></i></a></li>
                <!-- END setting -->
                <!-- BEGIN minimize -->
                    <li><a href="#" class="btn-minimize"><i class="icon-chevron-up"></i></a></li>
                <!-- END minimize -->
                <!-- BEGIN remove -->
                    <li><a href="#" class="btn-close"><i class="icon-remove"></i></a></li>
                <!-- END remove -->
                <!-- BEGIN custom -->
                    <li><a href="{block.custom.URL}" class="btn-custom {block.custom.CLASS}" title="{block.custom.TITLE}" {block.custom.EXTRA}><i class="{block.custom.ICON}"></i> {block.custom.LINK}</a></li>
                <!-- END custom -->
                <!-- BEGIN custom_html -->
                    <li>{block.custom_html.HTML}</li>
                <!-- END custom_html -->
            </ul>
        </div>
        <div class="box-content padded">
            {block.CONTENT}
        </div>
    </div>

    <!-- BEGIN end_row -->
    </div>
    <!-- END end_row -->

<!-- END block -->