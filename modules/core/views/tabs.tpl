<ul class="nav nav-tabs tab-menu">
    <!-- BEGIN tabs -->
    <li class="{tabs.ACTIVE}"><a href="#{tabs.ID}" data-toggle="tab">{tabs.NAME}</a></li>
    <!-- END tabs -->
</ul>
<div class="tab-content">
    <!-- BEGIN form_error -->
    <div class="alert alert-error">{form_error.ERROR_MSG}</div>
    <div class="clear">&nbsp;</div>
    <!-- END form_error -->
    <!-- BEGIN form_info -->
    <div class="alert alert-info">{form_info.INFO_MSG}</div>
    <div class="clear">&nbsp;</div>
    <!-- END form_info -->

    <!-- BEGIN tabs -->
    <div class="tab-pane{tabs.ACTIVE}" id="{tabs.ID}">
        {tabs.CONTENT}
    </div>
    <!-- END tabs -->
</div>
