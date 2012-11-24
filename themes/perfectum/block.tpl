<!-- BEGIN block -->
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
            <h2><i class="icon-{block.ICON}"></i><span class="break"></span>{block.TITLE}</h2>
            <div class="box-icon">
                <!-- BEGIN setting -->
                    <a href="#" class="btn-setting"><i class="icon-wrench"></i></a>
                <!-- END setting -->
                <!-- BEGIN minimize -->
                    <a href="#" class="btn-minimize"><i class="icon-chevron-up"></i></a>
                <!-- END minimize -->
                <!-- BEGIN remove -->
                    <a href="#" class="btn-close"><i class="icon-remove"></i></a>
                <!-- END remove -->
            </div>
        </div>
        <div class="box-content">
            {block.CONTENT}
        </div>
    </div>
<!-- END block -->