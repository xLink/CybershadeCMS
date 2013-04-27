<div class="span8 well">
    <!-- BEGIN register -->
    <ul>
        <!-- BEGIN errors -->
        <li><div class="alert alert-{register.errors.CLASS}">{register.errors.ERROR}</div></li>
        <!-- END errors -->
    </ul>
    <h4>Personal Information</h4>
    <hr />
    {register.FORM_START}
        <div class="row">
            <div class="span2">
                {register.L_USERNAME}:
            </div>
            <div class="span2">
                {register.F_USERNAME}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {register.L_PASSWORD}:
            </div>
            <div class="span2">
                {register.F_PASSWORD}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {register.L_PASSWORD_CONFIRM}:
            </div>
            <div class="span2">
                {register.F_PASSWORD_CONFIRM}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {register.L_EMAIL_ADDRESS}:
            </div>
            <div class="span2">
                {register.F_EMAIL_ADDRESS}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {register.L_EMAIL_ADDRESS_CONFIRM}:
            </div>
            <div class="span2">
                {register.F_EMAIL_ADDRESS_CONFIRM}
            </div>
        </div>
        <h4>Settings</h4>
        <hr />
<!--         <div class="row">
            <div class="span2">
                {register.L_REFERER}:
            </div>
            <div class="span2">
                {register.L_REFERER}
            </div>
        </div> -->
        <div class="row">
            <div class="span3">
                {register.L_RECEIVE_EMAILS_ADMINS}:
            </div>
            <div class="span2">
                {register.F_RECEIVE_EMAILS_ADMINS}
            </div>
        </div>
        <div class="row">
            <div class="span3">
                {register.L_RECEIVE_EMAILS_USERS}:
            </div>
            <div class="span2">
                {register.F_RECEIVE_EMAILS_USERS}
            </div>
        </div>
        <div class="row">
            <div class="span2">
                {register.L_REMME}
            </div>
            <div class="span2">
                {register.F_REMME}
            </div>
        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="span2">
                {register.SUBMIT}
            </div>
        </div>
        {register.HIDDEN}
    {register.FORM_END}
    <!-- END register -->
</div>