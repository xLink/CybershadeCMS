<style>
.permissions.nav-tabs > li, .permissions.nav-pills > li{
    float: none;
}
</style>

{FORM_START}
<table class="table">
    <tr>
        <!-- BEGIN columns -->
        <th>{columns.NAME}</th>
        <!-- END columns -->
    </tr>
    <!-- BEGIN row -->
    <tr>
        <td>{row.NAME}</td>
        <!-- BEGIN group -->
        <td width="8%"><ul class="nav nav-pills permissions">
            <!-- BEGIN values -->
            <li class="{row.group.values.SELECTED}">
                <label for="{row.group.NAME}[{row.KEY}]_{row.group.values.COUNT}"><input type="radio" value="{row.group.values.VALUE_KEY}" id="{row.group.NAME}[{row.KEY}]_{row.group.values.COUNT}" name="{row.group.NAME}[{row.KEY}]"> {row.group.values.VALUE_NAME}</label>
            </li>
            <!-- END values -->
        </ul></td>
        <!-- END group -->
    </tr>
    <!-- END row -->
</table>
{FORM_HIDDEN}
<div class="modal-footer">
    {FORM_SUBMIT}
</div>
{FORM_END}