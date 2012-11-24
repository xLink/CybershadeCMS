<header>{TITLE}</header>
    
<div class="well">
    <!-- BEGIN login -->
    <ul>
        <!-- BEGIN errors -->
        <li><span class="error">{login.errors.ERROR}</span></li>
        <!-- END errors -->
    </ul>
    {login.FORM_START}
        <div class="row">
            <div class="span1">
                {login.L_USERNAME}:
            </div>
            <div class="span1">
                {login.F_USERNAME}
            </div>
        </div>
        <div class="row">
            <div class="span1">
                {login.L_PASSWORD}:
            </div>
            <div class="span1">
                {login.F_PASSWORD}
            </div>
        </div>
        <div class="row">
            <div class="span1">
                {login.L_REMME}
            </div>
            <div class="span1">
                {login.F_REMME}
            </div>
        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="span1">
                {login.SUBMIT} {login.REGISTER}
            </div>
        </div>
        {login.HIDDEN}
    {login.FORM_END}
    <!-- END login -->
</div>