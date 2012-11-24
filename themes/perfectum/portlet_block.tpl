<!-- BEGIN portlet -->
<div class="box span{portlet.COLUMN_SPAN}" ontablet="span6" ondesktop="span{portlet.COLUMN_SPAN}">
	<div class="box-header">
		<h2><i class="icon-{portlet.ICON}"></i><span class="break"></span>{TITLE}</h2>
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
		{CONTENT}
	</div>
</div>
<!-- END portlet -->