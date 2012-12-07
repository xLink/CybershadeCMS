<section id="{SECTION_ID}">
{FORM_START}
    <fieldset{EXTRA}>
        <legend class="title">{FORM_TITLE}</legend>
        <!-- BEGIN form_error -->
        <div class="boxred padding">{form_error.ERROR_MSG}</div>
        <div class="clear">&nbsp;</div>
        <!-- END form_error -->

        <!-- BEGIN _form_row -->
            <!-- BEGIN _field -->
            <div class="formRow">
                <!-- BEGIN _label -->
                <label for="{_form_row._field.L_LABELFOR}">
                <!-- END _label -->
                    {_form_row._field.L_LABEL}
                    <!-- BEGIN _desc -->
                    <br /><small class="wrap grid_3">{_form_row._field.F_INFO}</small>
                    <!-- END _desc -->
                <!-- BEGIN _label -->
                </label>
                <!-- END _label -->
                {_form_row._field.F_ELEMENT}
            </div><div class="clear">&nbsp;</div>
            <!-- END _field -->
        <!-- END _form_row -->
        <div class="clear">&nbsp;</div>
        <div class="align-center"> {FORM_SUBMIT} {FORM_RESET} </div>
    </fieldset>
{FORM_END}
</section>