{FORM_START}
    <div class="alert alert-info">
        <strong>Info!</strong> This form will apply permissions CMS Wide, for Content Specific Permissions, please visit the Content Manager in question.
    </div>

    <div class="tabbable tabs-left">
        <ul class="nav nav-tabs">
            <!-- BEGIN tabs -->
            <li><a href="#{tabs.ID}" data-toggle="tab">{tabs.NAME}</a></li>
            <!-- END tabs -->
        </ul>
        <div class="tab-content">
            <!-- BEGIN tabs -->
            <div class="tab-pane{tabs.ACTIVE}" id="{tabs.ID}">
                {tabs.CONTENT}
            </div>
            <!-- END tabs -->
        </div>
    </div>

    {FORM_TOKEN}
    <div class="form-actions">
        {FORM_SUBMIT} {FORM_RESET}
    </div>
{FORM_END}
