<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }
if(!isset($LANG_LOAD)){ die('Error: Cannot access directly.'); }

//control panels - general
$_lang['L_OVERVIEW']            = 'Overview';
$_lang['L_SITE_OVERVIEW']       = 'Website Overview';
$_lang['L_SYS_INFO']            = 'System Information';
$_lang['L_CORE_SETTINGS']       = 'Core Settings';

$_lang['L_ENABLE']              = 'Enable';
$_lang['L_DISABLE']             = 'Disable';
$_lang['L_SET_UPDATED']         = 'Successfully updated settings. Returning you to the panel.';
$_lang['L_SET_NOT_UPDATED']     = 'Error: Some settings were not saved.<br />%s<br />Redirecting you back.';

//UCP: default
$_lang['L_ACCOUNT_PANEL']       = 'Account Settings';

$_lang['L_EMAIL']               = 'Email Address';
$_lang['L_CHANGE_EMAIL']        = '<font color="red">Warning:</font> If you change your email address,<br />'.
                                    'you will be logged out and required to activate<br /> your user account again.';

$_lang['L_CHANGE_PWDS']         = 'Change Account Password';
$_lang['F_NEW_PASS_CONF']       = 'Password Change Confirmation';
$_lang['L_OLD_PASSWD']          = 'Old Password';
$_lang['L_NEW_PASSWD']          = 'New Password';
$_lang['L_CONF_PASSWD']         = 'Confirm New Password';

$_lang['L_PIN_UPDATE']          = 'PIN Code Update';
$_lang['L_NEW_PIN_CONF']        = 'PIN Update Confirmation';
$_lang['L_OLD_PIN']             = 'Old PIN Code';
$_lang['L_NEW_PIN']             = 'New PIN Code';
$_lang['L_CONF_PIN']            = 'Verify New PIN Code';

$_lang['L_USERNAME_UPDATE']     = 'The username you chose contained incorrect characters.';
$_lang['L_EMAIL_UPDATE']        = 'Your email address has been changed.';
$_lang['L_EMAIL_ACTIVATION']    = 'Your email address has been changed. You must now reactivate your account with the email that has been sent to your email address.';
$_lang['L_PASS_WRONG']          = 'The passwords you have entered do not match.';
$_lang['L_INVALID_PASS']        = 'The old password you provided is incorrect. Cannot update your password.';
$_lang['L_CHANGED_PASS']        = 'Password has been updated.';
$_lang['L_PIN_UPDATE_OK']       = 'PIN has been updated.';
$_lang['L_PIN_UPDATE_FAIL']     = 'Could not update PIN, Old PIN or Password given was incorrect.';

//UCP: general
$_lang['L_WEBSITE_PANEL']       = 'Website Settings';

$_lang['L_SITE_SETTINGS']       = 'Site Wide Settings';
$_lang['L_FORUM_SETTINGS']      = 'Forum Settings';

$_lang['L_SEX']                 = 'Sex';
$_lang['L_SEX_F']               = 'Female';
$_lang['L_SEX_M']               = 'Male';
$_lang['L_SEX_U']               = 'Unknown';

$_lang['L_USER_COLORING']       = 'Username Coloring';
$_lang['L_SITE_TEMPLATE']       = 'Site Template';
$_lang['L_QUICK_REPLY']         = 'Quick Reply';
$_lang['L_PRIV_EMAIL']          = 'Make Email Private';
$_lang['L_AUTO_WATCH']          = 'Auto Watch Threads';
$_lang['L_TIMEZONE']            = 'Timezone';
$_lang['L_QUICK_REPLIES']       = 'Quick Replies';

//UCP: global langvars
$_lang['L_PRO_UPDATE_SUCCESS']  = 'Profile update was successful.';
$_lang['L_REQUIRED_INFO']       = 'Required Information';
$_lang['L_NO_CHANGES']          = 'There are no changes to be made.';

$_lang['L_YES']                 = 'Yes';
$_lang['L_NO']                  = 'No';

$_lang['L_ENABLED']             = 'Enabled';
$_lang['L_DISABLED']            = 'Disabled';

//UCP: contact info
$_lang['L_CONTACT_INFO']        = 'Contact Information';

//UCP: whitelist panel
$_lang['L_WHITELIST_PANEL']     = 'Whitelist Settings';

$_lang['L_IPRANGE']             = 'IP Range %d';
$_lang['L_NEWRANGE']            = 'New Range';
$_lang['L_IPRANGE_DESC']        = '<br /><ul><li>Please Denote IP Ranges in the following manner: 255.255.255.*</li><li>An IP Range can contain 4 Subnet Masks, and therefore 4 groups of numbers / astrix\'s</li><li><strong>Note:</strong> For security purposes, you cannot add *.*.*.* as a valid Range as this is the same as disabling this setting.</li></ul>';

//ACP: core settings
$_lang['L_SITE_CONFIG']         = 'Website Configuration';
$_lang['L_CUSTOMIZE']           = 'Customization';
$_lang['L_REG_LOGIN']           = 'Registration / Login';

$_lang['L_SITE_TITLE']          = 'Site Title';
$_lang['L_SITE_SLOGAN']         = 'Site Slogan';
$_lang['L_ADMIN_EMAIL']         = 'Administrator Email';
$_lang['L_INDEX_MODULE']        = 'Index Default Module';
$_lang['L_SITE_TZ']             = 'Site Timezone';
$_lang['L_DST']                 = 'Daylight Saving Time';
$_lang['L_DEF_DATE_FORMAT']     = 'Default Date Format';
$_lang['L_DEF_LANG']            = 'Default Language';
$_lang['L_DEF_THEME']           = 'Default Theme';
$_lang['L_THEME_OVERRIDE']      = 'Override Site Theme';
$_lang['L_ALLOW_REGISTER']      = 'Allow Registrations';
$_lang['L_EMAIL_ACTIVATE']      = 'Email Activation';
$_lang['L_MAX_LOGIN_TRIES']     = 'Max Login Tries';
$_lang['L_USERNAME_EDIT']       = 'Editable Usernames';
$_lang['L_GANALYTICS']          = 'Google Analytics Key';

$_lang['L_DESC_IMODULE']        = 'This setting controls the active functionality you have running on the website index(home page). For more advanced configuration check the module Administration panel.';
$_lang['L_DESC_SITE_TZ']        = 'This will change the time globally across the site, unless the user has overridden it.';
$_lang['L_DESC_DEF_DATE']       = 'The default date format. You can use [url="?mode=dateFormats"]date formats[/url] for more information about configuring it.';
$_lang['L_DESC_DEF_THEME']      = 'This will be the theme guests and users who havent configured their profiles will see.';
$_lang['L_DESC_THEME_OVERRIDE'] = 'If this is enabled [b]ALL users[/b] will see the default theme';
$_lang['L_DESC_ALLOW_REGISTER'] = 'If disabled, users will not be allowed to register on the website.';
$_lang['L_DESC_EMAIL_ACTIVATE'] = 'Make the users have to validate their accounts via email before being allowed to login.';
$_lang['L_DESC_MAX_LOGIN']      = 'Once a user exceeds this, he is banned for a predefined time.';
$_lang['L_DESC_REMME']          = 'If disabled, users will not be allowed to use the remember me to automatically login.';
$_lang['L_DESC_GANALYTICS']     = 'This allows you to use Google Analytics directly with the CMS.';

//ACP: reCaptcha
$_lang['L_RECAPTCHA']           = 'reCaptcha';
$_lang['L_RECAPTCHA_SETTINGS']  = 'reCaptcha Settings';

$_lang['L_PUB_KEY']             = 'Public Key';
$_lang['L_PRIV_KEY']            = 'Private Key';

//ACP: Site Maintenance
$_lang['L_SITE_MAINTENANCE']    = 'Site Maintenance';
$_lang['L_MAIN_DESC']           = 'This section enables you to disable the website whilst you work on it. It will be unavalible to everyone apart from logged in administrators. The login form will also be enabled just incase your logged out for any reason.';
$_lang['L_DISABLE_SITE']        = 'Disable Site';
$_lang['L_DISABLE_MSG']         = 'Disable Message';

//ACP: file registry
$_lang['L_FILE_REG']            = 'File Registry';
$_lang['L_DELETED']             = 'Deleted';
$_lang['L_OK']                  = 'Ok';
$_lang['L_FC_CHANGED']          = 'Changed %s ago';
$_lang['L_FILENAME']            = 'Filename';
$_lang['L_FILE_STATUS']         = 'File Status';
$_lang['L_CHECK_FH']            = 'Check File Hashes';
$_lang['L_UPDATE_FH']           = 'Update File Hashes';
$_lang['L_CHANGED_ONLY']        = 'Show Changed Files Only';

//ACP: System Info
$_lang['L_SYS_INFO']            = 'System Information';
$_lang['L_SYSINFO_MSG']         = 'This panel is ment to be an informative guide to help both you as end users and also us as the developers of this CMS. Please be careful about sharing this information as it can be useful to hackers as much as its useful for us developers to debug your problems. Your current configuration information is avalible with the link in this box, any sensitive information has been stripped out of this.';

//ACP: Plugin Management
$_lang['L_PLUGIN_MANAGE']       = 'Plugin Management';
$_lang['L_PLUGIN_INSTALL']      = 'Installed Plugins';
$_lang['L_PLUGIN_CATALOGUE']    = 'Plugin Catalogue';

//ACP: Module Management
$_lang['L_MOD_MANAGE']          = 'Module Management';
$_lang['L_MOD_INSTALL']         = 'Installed Modules';
$_lang['L_MOD_CATALOGUE']       = 'Module Catalogue';

















?>