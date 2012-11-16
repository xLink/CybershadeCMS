
<div class="span8 well">
    <!-- BEGIN login -->
    <ul>
        <!-- BEGIN errors -->
        <li><div class="alert alert-{login.errors.CLASS}">{login.errors.ERROR}</div></li>
        <!-- END errors -->
    </ul>
    {login.FORM_START}
        <div class="row">
            <div class="span2">
                {login.L_USERNAME}:
            </div>
            <div class="span2">
                {login.F_USERNAME}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {login.L_PASSWORD}:
            </div>
            <div class="span2">
                {login.F_PASSWORD}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {login.L_REMME}
            </div>
            <div class="span2">
                {login.F_REMME}
            </div>
        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="span2">
                {login.SUBMIT} {login.REGISTER}
            </div>
        </div>
        {login.HIDDEN}
    {login.FORM_END}
    <!-- END login -->
</div>