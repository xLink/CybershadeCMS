<!-- BEGIN thread -->
<table width="100%" border="0" cellspacing="1" cellpadding="2" class="content corners" name="post_{thread.ID}" id="post_{thread.ID}">
  <tr>
    <td valign="top" width="20%" class="{thread.ROW}" align="center">
    <div class="padding">
        {thread.AUTHOR_IO} {thread.AUTHOR}<br /> 
        {thread.USERTITLE}
    </div>
    {thread.AVATAR}
    <div class="padding">
        {thread.POSTCOUNT}<br />
        {thread.LOCATION}
    </div>
    </td>
    <td valign="top" class="{thread.ROW}">
    <div class="padding block">
        <div class="float-left">
            {thread.TIME}
        </div>
        <div class="float-right">
            <!-- BEGIN edit -->
                <a href="{thread.edit.URL}"{thread.edit.EXTRA}><img src="{thread.edit.IMG}" alt="{thread.edit.TEXT}" title="{thread.edit.TEXT}" /></a>
            <!-- END edit -->
            <!-- BEGIN del -->
                <a href="{thread.del.URL}"><img src="{thread.del.IMG}" alt="{thread.del.TEXT}" title="{thread.del.TEXT}" /></a> 
            <!-- END del -->
            <!-- BEGIN quote -->
                <a href="{thread.quote.URL}"{thread.quote.EXTRA}><img src="{thread.quote.IMG}" alt="{thread.quote.TEXT}" title="{thread.quote.TEXT}" /></a>
            <!-- END quote -->
        </div>
        <div class="clear"></div>
        <hr />
        <div class="padding" id="post_id_{thread.ID}">{thread.POST}</div>
    </div>
    <!-- BEGIN sig -->
    <div class="clear"></div>
    <div align="center"><img src="/{ROOT}images/h-divide.png" /></div><br />{thread.SIGNATURE}
    <!-- END sig -->
    </td>
  </tr>
  <tr>
    <td colspan="2" valign="top" class="{thread.ROW}">
    <div class="float-left">{thread.EDITED}</div>
    <div class="float-right">{thread.IP}</div>
    </td>
  </tr>
</table><br />
<!-- END thread -->
