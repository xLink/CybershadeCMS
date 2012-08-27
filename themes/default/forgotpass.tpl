<h2>{PAGE}</h2>
{FORGOT_MSG}<br /><br />
<hr />
{FORM_START}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<!-- BEGIN error -->
  <tr>
    <td class="error" colspan="2" align="center">{ERROR}</td>
  </tr>
<!-- END error -->
<!-- BEGIN form -->
  <tr>
    <td width="30%" valign="top">{form.KEY}</td>
    <td width="70%">{form.VALUE}</td>
  </tr>
<!-- END form -->
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
<!-- BEGIN captcha -->
  <tr>
    <td valign="top">{L_CAPTCHA}:<br />
      <small>{captcha.L_CAPTCHA_EXPLAIN}</small></td>
    <td>{captcha.CAPTCHA}</td>
  </tr>
<!-- END captcha -->
  <tr>
    <td colspan="2" align="center">{SUBMIT} | {RESET}</td>
  </tr>
</table>
