
<!-- BEGIN _form_row -->
    <!-- BEGIN _field -->
    <div class="control-group">
        <!-- BEGIN _label -->
        <label for="{_form_row._field.L_LABELFOR}" class="control-label">
        <!-- END _label -->
            {_form_row._field.L_LABEL}
        <!-- BEGIN _label -->
        </label>
        <!-- END _label -->
        <div class="controls">
            <!-- BEGIN _normal -->
            {_form_row._field.F_ELEMENT}
            <!-- END _normal -->
            <!-- BEGIN _prepend -->
            <div class="input-prepend">
                <span class="add-on">{_form_row._field._prepend.ADDON}</span>
                {_form_row._field.F_ELEMENT}
            </div>
            <!-- END _prepend -->
            <!-- BEGIN _append -->
            <div class="input-append">
                {_form_row._field.F_ELEMENT}
                <span class="add-on">{_form_row._field._append.ADDON}</span>
            </div>
            <!-- END _append -->
            <!-- BEGIN _desc -->
            <a rel="popover" data-toggle="popover" data-placement="right" data-content="{_form_row._field.F_INFO}" data-trigger="hover" href="#"><i class="icon-question-sign"></i></a>
            <!-- END _desc -->
        </div>
    </div>
    <!-- END _field -->
<!-- END _form_row -->
