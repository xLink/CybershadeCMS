<section id="{SECTION_ID}">
{FORM_START}
    <fieldset>
        <legend class="title">{FORM_TITLE}</legend>
        <!-- BEGIN form_error -->
        <div class="alert alert-error">{form_error.ERROR_MSG}</div>
        <div class="clear">&nbsp;</div>
        <!-- END form_error -->

        <!-- BEGIN _form_row -->
            <!-- BEGIN _field -->
            <div class="control-group">
                <!-- BEGIN _label -->
                <label for="{_form_row._field.L_LABELFOR}" class="control-label">
                <!-- END _label -->
                    {_form_row._field.L_LABEL}
                    <!-- BEGIN _desc -->
                    <br /><small class="wrap grid_3">{_form_row._field.F_INFO}</small>
                    <!-- END _desc -->
                <!-- BEGIN _label -->
                </label>
                <!-- END _label -->
                <div class="controls">
                    {_form_row._field.F_ELEMENT}
                </div>
            </div>
            <!-- END _field -->
        <!-- END _form_row -->
        <div class="control-group">
            <div class="form-actions">
                {FORM_SUBMIT} {FORM_RESET}
            </div>
        </div>
    </fieldset>
{FORM_END}
</section>
