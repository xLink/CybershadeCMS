<!-- BEGIN forum -->
<div id="cat_{forum.ID}" class="content catClear corners">
    <div class="title iblock corners-top{forum.CLASS}">
      <!-- BEGIN expand -->
        <div class="float-left catIcon">
            <img src="{forum.EXPAND}" id="img_{forum.ID}" data-mode="{forum.MODE}" name="{forum.ID}" />
        </div>
      <!-- END expand -->
        <div class="float-left catName">
            <h4>{forum.CAT}</h4>
        </div>
    </div>
    <div id="f_{forum.ID}" style="{forum.DISPLAY}">
    <table width="100%" border="0" cellspacing="1" cellpadding="5">
    <!-- BEGIN row -->
      <tr class="{forum.row.ROW}">
        <td width="4%" rowspan="2" valign="middle" align="center"><img src="{forum.row.CAT_ICO}" /></td>
        <td colspan="2">
            <span class="float-left bold" id="forum_title"><a href="{forum.row.URL}">{forum.row.CAT}</a></span>
            <!-- BEGIN subs -->
            <span class="float-right">
                <!-- BEGIN cats -->
                <img src="{forum.row.subs.cats.IMG}" /> <a href="{forum.row.subs.cats.URL}">{forum.row.subs.cats.NAME}</a>
                <!-- END cats -->
            </span>
            <!-- END subs -->
        </td>
      </tr>
      <tr class="{forum.row.ROW}">
        <td>
            {forum.row.DESC}<br />
            <span class="float-left">
            <!-- BEGIN counts -->
            <strong>{forum.row.L_TCOUNT}:</strong> {forum.row.T_COUNT} | <strong>{forum.row.T_PCOUNT}:</strong> {forum.row.P_COUNT}
            <!-- END counts -->
            &nbsp;</span>
            <span class="float-right">{forum.row.L_MODS} {forum.row.C_MODS}</span>
        </td>
        <td width="40%" data-url="{forum.row.LP_URL}">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center"><a href="{forum.row.LP_URL}">{forum.row.LP_TITLE}</a> {forum.row.LP_AUTHOR}<br />{forum.row.LP_TIME}</td>
                <td>{forum.row.LP_REPLY}</td>
            </tr>
            </table>
        </td>
      </tr>
    <!-- END row -->
    </table>
    </div>
    <div class="clear"></div>
</div>
<!-- END forum -->