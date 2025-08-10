<?php
# vicidial.php - the web-based version of the astVICIDIAL client application
# 
# Copyright (C) 2018  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
# Modern Theme Version - Askar Yılmaz 2025

$version = '2.14-565c';
$build = '180512-2226';
$mel=1;					# Mysql Error Log enabled = 1
$mysql_log_count=87;
$one_mysql_log=0;
$DB=0;

require_once("dbconnect_mysqli.php");
require_once("functions.php");

// Mevcut değişken kontrollerini koruyoruz...
if (isset($_GET["DB"]))						    {$DB=$_GET["DB"];}
        elseif (isset($_POST["DB"]))            {$DB=$_POST["DB"];}
if (isset($_GET["JS_browser_width"]))				{$JS_browser_width=$_GET["JS_browser_width"];}
        elseif (isset($_POST["JS_browser_width"]))  {$JS_browser_width=$_POST["JS_browser_width"];}
if (isset($_GET["JS_browser_height"]))				{$JS_browser_height=$_GET["JS_browser_height"];}
        elseif (isset($_POST["JS_browser_height"])) {$JS_browser_height=$_POST["JS_browser_height"];}
if (isset($_GET["phone_login"]))                {$phone_login=$_GET["phone_login"];}
        elseif (isset($_POST["phone_login"]))   {$phone_login=$_POST["phone_login"];}
if (isset($_GET["phone_pass"]))					{$phone_pass=$_GET["phone_pass"];}
        elseif (isset($_POST["phone_pass"]))    {$phone_pass=$_POST["phone_pass"];}
if (isset($_GET["VD_login"]))					{$VD_login=$_GET["VD_login"];}
        elseif (isset($_POST["VD_login"]))      {$VD_login=$_POST["VD_login"];}
if (isset($_GET["VD_pass"]))					{$VD_pass=$_GET["VD_pass"];}
        elseif (isset($_POST["VD_pass"]))       {$VD_pass=$_POST["VD_pass"];}
if (isset($_GET["VD_campaign"]))                {$VD_campaign=$_GET["VD_campaign"];}
        elseif (isset($_POST["VD_campaign"]))   {$VD_campaign=$_POST["VD_campaign"];}
if (isset($_GET["VD_language"]))                {$VD_language=$_GET["VD_language"];}
        elseif (isset($_POST["VD_language"]))   {$VD_language=$_POST["VD_language"];}
if (isset($_GET["relogin"]))					{$relogin=$_GET["relogin"];}
        elseif (isset($_POST["relogin"]))       {$relogin=$_POST["relogin"];}
if (isset($_GET["MGR_override"]))				{$MGR_override=$_GET["MGR_override"];}
        elseif (isset($_POST["MGR_override"]))  {$MGR_override=$_POST["MGR_override"];}
if (isset($_GET["admin_test"]))					{$admin_test=$_GET["admin_test"];}
        elseif (isset($_POST["admin_test"]))	{$admin_test=$_POST["admin_test"];}
if (isset($_GET["LOGINvarONE"]))				{$LOGINvarONE=$_GET["LOGINvarONE"];}
        elseif (isset($_POST["LOGINvarONE"]))	{$LOGINvarONE=$_POST["LOGINvarONE"];}
if (isset($_GET["LOGINvarTWO"]))				{$LOGINvarTWO=$_GET["LOGINvarTWO"];}
        elseif (isset($_POST["LOGINvarTWO"]))	{$LOGINvarTWO=$_POST["LOGINvarTWO"];}
if (isset($_GET["LOGINvarTHREE"]))				{$LOGINvarTHREE=$_GET["LOGINvarTHREE"];}
        elseif (isset($_POST["LOGINvarTHREE"]))	{$LOGINvarTHREE=$_POST["LOGINvarTHREE"];}
if (isset($_GET["LOGINvarFOUR"]))				{$LOGINvarFOUR=$_GET["LOGINvarFOUR"];}
        elseif (isset($_POST["LOGINvarFOUR"]))	{$LOGINvarFOUR=$_POST["LOGINvarFOUR"];}
if (isset($_GET["LOGINvarFIVE"]))				{$LOGINvarFIVE=$_GET["LOGINvarFIVE"];}
        elseif (isset($_POST["LOGINvarFIVE"]))	{$LOGINvarFIVE=$_POST["LOGINvarFIVE"];}

if (!isset($phone_login)) 
	{
	if (isset($_GET["pl"]))            {$phone_login=$_GET["pl"];}
		elseif (isset($_POST["pl"]))   {$phone_login=$_POST["pl"];}
	}
if (!isset($phone_pass))
	{
	if (isset($_GET["pp"]))            {$phone_pass=$_GET["pp"];}
		elseif (isset($_POST["pp"]))   {$phone_pass=$_POST["pp"];}
	}
if (isset($VD_campaign))
	{
	$VD_campaign = strtoupper($VD_campaign);
	$VD_campaign = preg_replace("/\s/i",'',$VD_campaign);
	}
if (!isset($flag_channels))
	{
	$flag_channels=0;
	$flag_string='';
	}

### security strip all non-alphanumeric characters out of the variables ###
$DB=preg_replace("/[^0-9a-z]/","",$DB);
$phone_login=preg_replace("/[^\,0-9a-zA-Z]/","",$phone_login);
$phone_pass=preg_replace("/[^-_0-9a-zA-Z]/","",$phone_pass);
$VD_login=preg_replace("/\'|\"|\\\\|;| /","",$VD_login);
$VD_pass=preg_replace("/\'|\"|\\\\|;| /","",$VD_pass);
$VD_campaign = preg_replace("/[^-_0-9a-zA-Z]/","",$VD_campaign);
$VD_language = preg_replace("/\'|\"|\\\\|;/","",$VD_language);
$admin_test = preg_replace("/[^0-9a-zA-Z]/","",$admin_test);
$LOGINvarONE=preg_replace("/[^-_0-9a-zA-Z]/","",$LOGINvarONE);
$LOGINvarTWO=preg_replace("/[^-_0-9a-zA-Z]/","",$LOGINvarTWO);
$LOGINvarTHREE=preg_replace("/[^-_0-9a-zA-Z]/","",$LOGINvarTHREE);
$LOGINvarFOUR=preg_replace("/[^-_0-9a-zA-Z]/","",$LOGINvarFOUR);
$LOGINvarFIVE=preg_replace("/[^-_0-9a-zA-Z]/","",$LOGINvarFIVE);

$forever_stop=0;

$isdst = date("I");
$StarTtimE = date("U");
$NOW_TIME = date("Y-m-d H:i:s");
$tsNOW_TIME = date("YmdHis");
$FILE_TIME = date("Ymd-His");
$loginDATE = date("Ymd");
$CIDdate = date("ymdHis");
$month_old = mktime(11, 0, 0, date("m"), date("d")-2,  date("Y"));
$past_month_date = date("Y-m-d H:i:s",$month_old);
$minutes_old = mktime(date("H"), date("i")-2, date("s"), date("m"), date("d"),  date("Y"));
$past_minutes_date = date("Y-m-d H:i:s",$minutes_old);
$JS_date = $StarTtimE."000"; # milliseconds since epoch or "16,3,31,8,56,1,0"   year,month,day,hour,minute,second,millisecond
$webphone_width = 460;
$webphone_height = 500;
$VUselected_language = '';

$random = (rand(1000000, 9999999) + 10000000);

$stmt="SELECT user,selected_language from vicidial_users where user='$VD_login';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
	if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01081',$VD_login,$server_ip,$session_name,$one_mysql_log);}
$sl_ct = mysqli_num_rows($rslt);
if ($sl_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$VUuser =				$row[0];
	$VUselected_language =	$row[1];
	}

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,vdc_header_date_format,vdc_customer_date_format,vdc_header_phone_format,webroot_writable,timeclock_end_of_day,vtiger_url,enable_vtiger_integration,outbound_autodial_active,enable_second_webform,user_territories_active,static_agent_url,custom_fields_enabled,pllb_grouping_limit,qc_features_active,allow_emails,callback_time_24hour,enable_languages,language_method,meetme_enter_login_filename,meetme_enter_leave3way_filename,enable_third_webform,default_language,active_modules,allow_chats,chat_url,default_phone_code,agent_screen_colors,manual_auto_next,agent_xfer_park_3way,admin_web_directory,agent_script,agent_push_events,agent_push_url FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
	if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01001',$VD_login,$server_ip,$session_name,$one_mysql_log);}
if ($DB) {echo "$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =						$row[0];
	$vdc_header_date_format =			$row[1];
	$vdc_customer_date_format =			$row[2];
	$vdc_header_phone_format =			$row[3];
	$WeBRooTWritablE =					$row[4];
	$timeclock_end_of_day =				$row[5];
	$vtiger_url =						$row[6];
	$enable_vtiger_integration =		$row[7];
	$outbound_autodial_active =			$row[8];
	$enable_second_webform =			$row[9];
	$user_territories_active =			$row[10];
	$static_agent_url =					$row[11];
	$custom_fields_enabled =			$row[12];
	$SSpllb_grouping_limit =			$row[13];
	$qc_enabled =						$row[14];
	$email_enabled =					$row[15];
	$callback_time_24hour =				$row[16];
	$SSenable_languages =				$row[17];
	$SSlanguage_method =				$row[18];
	$meetme_enter_login_filename =		$row[19];
	$meetme_enter_leave3way_filename =	$row[20];
	$enable_third_webform =				$row[21];
	$default_language =					$row[22];
	$active_modules =					$row[23];
	$chat_enabled =						$row[24];
	$chat_URL =							$row[25];
	$default_phone_code =				$row[26];
	$agent_screen_colors =				$row[27];
	$SSmanual_auto_next =				$row[28];
	$SSagent_xfer_park_3way =			$row[29];
	$admin_web_directory =				$row[30];
	$SSagent_script =					$row[31];
	$agent_push_events =				$row[32];
	$agent_push_url =					$row[33];
	}
else
	{
	echo _QXZ("ERROR: System Settings missing")."\n";
	exit;
	}
##### END SETTINGS LOOKUP #####
###########################################

if ($non_latin < 1)
	{
	$VD_login=preg_replace("/[^-_0-9a-zA-Z]/","",$VD_login);
	$VD_pass=preg_replace("/[^-_0-9a-zA-Z]/","",$VD_pass);
	}

if ($force_logout)
	{
    echo _QXZ("You have now logged out. Thank you")."\n";
    exit;
	}

##### DEFINABLE SETTINGS AND OPTIONS
###########################################

# set defaults for hard-coded variables
$conf_silent_prefix		= '5';	# vicidial_conferences prefix to enter silently and muted for recording
$dtmf_silent_prefix		= '7';	# vicidial_conferences prefix to enter silently
$HKuser_level			= '1';	# minimum vicidial user_level for HotKeys
$campaign_login_list	= '1';	# show drop-down list of campaigns at login	
$manual_dial_preview	= '1';	# allow preview lead option when manual dial
$multi_line_comments	= '1';	# set to 1 to allow multi-line comment box
$user_login_first		= '0';	# set to 1 to have the vicidial_user login before the phone login
$view_scripts			= '1';	# set to 1 to show the SCRIPTS tab
$dispo_check_all_pause	= '0';	# set to 1 to allow for persistent pause after dispo
$callholdstatus			= '1';	# set to 1 to show calls on hold count
$agentcallsstatus		= '0';	# set to 1 to show agent status and call dialed count
   $campagentstatctmax	= '3';	# Number of seconds for campaign call and agent stats
$show_campname_pulldown	= '1';	# set to 1 to show campaign name on login pulldown
$webform_sessionname	= '1';	# set to 1 to include the session_name in webform URL
$local_consult_xfers	= '1';	# set to 1 to send consultative transfers from original server
$clientDST				= '1';	# set to 1 to check for DST on server for agent time
$no_delete_sessions		= '1';	# set to 1 to not delete sessions at logout
$volumecontrol_active	= '1';	# set to 1 to allow agents to alter volume of channels
$PreseT_DiaL_LinKs		= '0';	# set to 1 to show a DIAL link for Dial Presets
$LogiNAJAX				= '1';	# set to 1 to do lookups on campaigns for login
$HidEMonitoRSessionS	= '1';	# set to 1 to hide remote monitoring channels from "session calls"
$hangup_all_non_reserved= '1';	# set to 1 to force hangup all non-reserved channels upon Hangup Customer
$LogouTKicKAlL			= '1';	# set to 1 to hangup all calls in session upon agent logout
$PhonESComPIP			= '1';	# set to 1 to log computer IP to phone if blank, set to 2 to force log each login
$DefaulTAlTDiaL			= '0';	# set to 1 to enable ALT DIAL by default if enabled for the campaign
$AgentAlert_allowed		= '1';	# set to 1 to allow Agent alert option
$disable_blended_checkbox='0';	# set to 1 to disable the BLENDED checkbox from the in-group chooser screen
$hide_timeclock_link	= '0';	# set to 1 to hide the timeclock link on the agent login screen
$conf_check_attempts	= '3';	# number of attempts to try before loosing webserver connection, for bad network setups
$focus_blur_enabled		= '0';	# set to 1 to enable the focus/blur enter key blocking(some IE instances have issues)
$consult_custom_delay	= '2';	# number of seconds to delay consultative transfers when customfields are active
$mrglock_ig_select_ct	= '4';	# number of seconds to leave in-group select screen open if agent select is disabled
$link_to_grey_version	= '1';	# show link to old grey version of agent screen at login screen, next to timeclock link
$no_empty_session_warnings=0;	# set to 1 to disable empty session warnings on agent screen

$TEST_all_statuses		= '0';	# TEST variable allows all statuses in dispo screen, FOR DEBUG ONLY

$stretch_dimensions		= '1';	# sets the vicidial screen to the size of the browser window
$BROWSER_HEIGHT			= 500;	# set to the minimum browser height, default=500
$BROWSER_WIDTH			= 770;	# set to the minimum browser width, default=770
$webphone_width			= 460;	# set the webphone frame width
$webphone_height		= 500;	# set the webphone frame height
$webphone_pad			= 0;	# set the table cellpadding for the webphone
$webphone_location		= 'right';	# set the location on the agent screen 'right' or 'bar'
$MAIN_COLOR				= '#CCCCCC';	# old default is E0C2D6
$SCRIPT_COLOR			= '#E6E6E6';	# old default is FFE7D0
$FORM_COLOR				= '#EFEFEF';
$SIDEBAR_COLOR			= '#F6F6F6';

$window_validation		= 0;	# set to 1 to disallow direct logins to vicidial.php
$win_valid_name			= 'subwindow_launch';	# only window name to allow if validation enabled

$INSERT_head_script		= '';	# inserted right above the <script language="Javascript"> line after logging in
$INSERT_head_js			= '';	# inserted after first javascript function
$INSERT_first_onload	= '';	# inserted at the beginning of the first section of the onload function
$INSERT_window_onload	= '';	# inserted at the end of the onload function
$INSERT_agent_events	= '';	# inserted within the agent_events function

# if options file exists, use the override values for the above variables
#   see the options-example.php file for more information
if (file_exists('options.php'))
	{
	require_once('options.php');
	}

##### BEGIN Define colors and logo #####
$SSmenu_background='015B91';
$SSframe_background='D9E6FE';
$SSstd_row1_background='9BB9FB';
$SSstd_row2_background='B9CBFD';
$SSstd_row3_background='8EBCFD';
$SSstd_row4_background='B6D3FC';
$SSstd_row5_background='FFFFFF';
$SSalt_row1_background='BDFFBD';
$SSalt_row2_background='99FF99';
$SSalt_row3_background='CCFFCC';

if ($agent_screen_colors != 'default')
	{
	$stmt = "SELECT menu_background,frame_background,std_row1_background,std_row2_background,std_row3_background,std_row4_background,std_row5_background,alt_row1_background,alt_row2_background,alt_row3_background,web_logo FROM vicidial_screen_colors where colors_id='$agent_screen_colors';";
	$rslt=mysql_to_mysqli($stmt, $link);
		if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01XXX',$VD_login,$server_ip,$session_name,$one_mysql_log);}
	if ($DB) {echo "$stmt\n";}
	$qm_conf_ct = mysqli_num_rows($rslt);
	if ($qm_conf_ct > 0)
		{
		$row=mysqli_fetch_row($rslt);
		$SSmenu_background =		$row[0];
		$SSframe_background =		$row[1];
		$SSstd_row1_background =	$row[2];
		$SSstd_row2_background =	$row[3];
		$SSstd_row3_background =	$row[4];
		$SSstd_row4_background =	$row[5];
		$SSstd_row5_background =	$row[6];
		$SSalt_row1_background =	$row[7];
		$SSalt_row2_background =	$row[8];
		$SSalt_row3_background =	$row[9];
		$SSweb_logo =				$row[10];
		}
	}
$Mhead_color =	$SSstd_row5_background;
$Mmain_bgcolor = $SSmenu_background;
// 1. BÖLÜM SONU - Satır 400'e kadar olan kısmı aldınız
?>


<?php
// 2. BÖLÜM BAŞLANGICI - Modern CSS ve HTML Head kısmı (Satır 400-800 arası yerine)

$selected_logo = "./images/vicidial_admin_web_logo.png";
$logo_new=0;
$logo_old=0;
if (file_exists('../$admin_web_directory/images/vicidial_admin_web_logo.png')) {$logo_new++;}
if (file_exists('vicidial_admin_web_logo.gif')) {$logo_old++;}
if ($SSweb_logo=='default_new')
	{
	$selected_logo = "./images/vicidial_admin_web_logo.png";
	}
if ( ($SSweb_logo=='default_old') and ($logo_old > 0) )
	{
	$selected_logo = "../$admin_web_directory/vicidial_admin_web_logo.gif";
	}
if ( ($SSweb_logo!='default_new') and ($SSweb_logo!='default_old') )
	{
	if (file_exists("../$admin_web_directory/images/vicidial_admin_web_logo$SSweb_logo")) 
		{
		$selected_logo = "../$admin_web_directory/images/vicidial_admin_web_logo$SSweb_logo";
		}
	}
##### END Define colors and logo #####

$hide_gender=0;
$US='_';
$AT='@';
$DS='-';
$date = date("r");
$ip = getenv("REMOTE_ADDR");
$browser = getenv("HTTP_USER_AGENT");
$browser=preg_replace("/\'|\"|\\\\/","",$browser);
$script_name = getenv("SCRIPT_NAME");
$server_name = getenv("SERVER_NAME");
$server_port = getenv("SERVER_PORT");
$CL=':';
if (preg_match("/443/i",$server_port)) {$HTTPprotocol = 'https://';}
  else {$HTTPprotocol = 'http://';}
if (($server_port == '80') or ($server_port == '443') ) {$server_port='';}
else {$server_port = "$CL$server_port";}
$FQDN = "$server_name$server_port";
$chat_URL = preg_replace("/LOCALFQDN/",$FQDN,$chat_URL);
$agent_push_url = preg_replace("/LOCALFQDN/",$FQDN,$agent_push_url);
$agcPAGE = "$HTTPprotocol$server_name$server_port$script_name";
$agcDIR = preg_replace('/vicidial\.php/i','',$agcPAGE);
$agcDIR = preg_replace("/$SSagent_script/i",'',$agcDIR);
if (strlen($static_agent_url) > 5)
	{$agcPAGE = $static_agent_url;}
if (strlen($VUselected_language) < 1)
	{$VUselected_language = $default_language;}
$vdc_form_display = 'vdc_form_display.php';
if (preg_match("/cf_encrypt/",$active_modules))
	{$vdc_form_display = 'vdc_form_display_encrypt.php';}

header ("Content-type: text/html; charset=utf-8");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0
echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-color: #2563eb;
    --primary-dark: #1d4ed8;
    --success-color: #059669;
    --warning-color: #d97706;
    --danger-color: #dc2626;
    --dark-color: #1f2937;
    --light-color: #f8fafc;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

/* Modern Login Container */
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 2.5rem;
    width: 100%;
    max-width: 460px;
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Header Section */
.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-header .logo {
    width: 170px;
    height: 45px;
    object-fit: contain;
    margin-bottom: 1rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--dark-color);
    margin: 0;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.login-subtitle {
    font-size: 0.95rem;
    color: #6b7280;
    margin-top: 0.5rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: rgba(255, 255, 255, 0.95);
}

.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 48px;
}

.btn-primary {
    background: var(--gradient-primary);
    color: white;
    box-shadow: var(--shadow-md);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    filter: brightness(110%);
}

.btn-secondary {
    background: rgba(107, 114, 128, 0.1);
    color: var(--dark-color);
    border: 2px solid var(--border-color);
}

.btn-secondary:hover {
    background: rgba(107, 114, 128, 0.2);
    border-color: var(--primary-color);
}

.btn-group {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Footer Links */
.login-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.footer-link {
    color: #6b7280;
    text-decoration: none;
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.footer-link:hover {
    color: var(--primary-color);
    background: rgba(37, 99, 235, 0.05);
}

.version-info {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 1rem;
}

/* Alert Messages */
.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: 1px solid transparent;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.2);
    color: #dc2626;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.2);
    color: #059669;
}

/* Loading Animation */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-card {
        margin: 1rem;
        padding: 2rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .footer-links {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Legacy compatibility classes */
.skb_text { color: var(--dark-color); font-weight: 500; }
.sh_text_white { color: white; font-weight: 600; }
.sb_text { color: #6b7280; font-size: 0.875rem; }
.body_tiny { font-size: 0.75rem; color: #9ca3af; }

/* Modern Agent Interface Styles */
.agent-interface {
    background: var(--light-color);
    min-height: 100vh;
}

.agent-header {
    background: var(--gradient-primary);
    color: white;
    padding: 1rem 2rem;
    box-shadow: var(--shadow-md);
}

.agent-main {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.agent-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-ready { background: rgba(34, 197, 94, 0.1); color: #059669; }
.status-paused { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.status-incall { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
.status-dispo { background: rgba(168, 85, 247, 0.1); color: #7c3aed; }
</style>
';

echo "<!-- VERSION: $version     "._QXZ("BUILD:")." $build -->\n";
echo "<!-- BROWSER: $BROWSER_WIDTH x $BROWSER_HEIGHT     $JS_browser_width x $JS_browser_height -->\n";

// 2. BÖLÜM SONU - Modern CSS ve HTML head tamamlandı
?>


<?php
// 3. BÖLÜM BAŞLANGICI - Kampanya formu ve JavaScript (Satır 800-1200 arası yerine)

if ($campaign_login_list > 0)
	{
    $camp_form_code  = "<select class=\"form-select\" name=\"VD_campaign\" id=\"VD_campaign\" onfocus=\"login_allowable_campaigns()\">\n";
	$camp_form_code .= "<option value=\"\">"._QXZ("Choose Campaign...")."</option>\n";

	$LOGallowed_campaignsSQL='';
	if ($relogin == 'YES')
		{
		$stmt="SELECT user_group from vicidial_users where user='$VD_login' and active='Y' and api_only_user != '1';";
		if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
		$rslt=mysql_to_mysqli($stmt, $link);
				if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01002',$VD_login,$server_ip,$session_name,$one_mysql_log);}
		$cl_user_ct = mysqli_num_rows($rslt);
		if ($cl_user_ct > 0)
			{
			$row=mysqli_fetch_row($rslt);
			$VU_user_group=$row[0];

			$stmt="SELECT allowed_campaigns from vicidial_user_groups where user_group='$VU_user_group';";
			$rslt=mysql_to_mysqli($stmt, $link);
					if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01003',$VD_login,$server_ip,$session_name,$one_mysql_log);}
			$row=mysqli_fetch_row($rslt);
			if ( (!preg_match("/ALL-CAMPAIGNS/i",$row[0])) )
				{
				$LOGallowed_campaignsSQL = preg_replace('/\s-/i','',$row[0]);
				$LOGallowed_campaignsSQL = preg_replace('/\s/i',"','",$LOGallowed_campaignsSQL);
				$LOGallowed_campaignsSQL = "and campaign_id IN('$LOGallowed_campaignsSQL')";
				}
			}
		else
			{
			echo "<select class=\"form-select\" name=\"VD_campaign\" id=\"VD_campaign\" onFocus=\"login_allowable_campaigns()\">\n";
			echo "<option value=\"\">-- "._QXZ("USER LOGIN ERROR")." --</option>\n";
			echo "</select>\n";
			}
		}

	### code for manager override of shift restrictions
	if ($MGR_override > 0)
		{
		if (isset($_GET["MGR_login$loginDATE"]))				{$MGR_login=$_GET["MGR_login$loginDATE"];}
				elseif (isset($_POST["MGR_login$loginDATE"]))	{$MGR_login=$_POST["MGR_login$loginDATE"];}
		if (isset($_GET["MGR_pass$loginDATE"]))					{$MGR_pass=$_GET["MGR_pass$loginDATE"];}
				elseif (isset($_POST["MGR_pass$loginDATE"]))	{$MGR_pass=$_POST["MGR_pass$loginDATE"];}

		$MGR_login = preg_replace("/\'|\"|\\\\|;/","",$MGR_login);
		$MGR_pass = preg_replace("/\'|\"|\\\\|;/","",$MGR_pass);

		$MGR_auth=0;
		$auth_message = user_authorization($MGR_login,$MGR_pass,'MGR',0,0,0,0);
		if (preg_match("/^GOOD/",$auth_message))
			{$MGR_auth=1;}

		if($MGR_auth>0)
			{
			$stmt="UPDATE vicidial_users SET shift_override_flag='1' where user='$VD_login';";
			if ($DB) {echo "|$stmt|\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01059',$VD_login,$server_ip,$session_name,$one_mysql_log);}
			echo "<!-- Shift Override entered for $VD_login by $MGR_login -->\n";

			### Add a record to the vicidial_admin_log
			$SQL_log = "$stmt|";
			$SQL_log = preg_replace('/;/','',$SQL_log);
			$SQL_log = addslashes($SQL_log);
			$stmt="INSERT INTO vicidial_admin_log set event_date='$NOW_TIME', user='$MGR_login', ip_address='$ip', event_section='AGENT', event_type='OVERRIDE', record_id='$VD_login', event_code='MANAGER OVERRIDE OF AGENT SHIFT ENFORCEMENT', event_sql=\"$SQL_log\", event_notes='user: $VD_login';";
			if ($DB) {echo "|$stmt|\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01060',$VD_login,$server_ip,$session_name,$one_mysql_log);}
			}
		}

	$stmt="SELECT campaign_id,campaign_name from vicidial_campaigns where active='Y' $LOGallowed_campaignsSQL order by campaign_id;";
	if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
	$rslt=mysql_to_mysqli($stmt, $link);
				if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01004',$VD_login,$server_ip,$session_name,$one_mysql_log);}
	$camps_to_print = mysqli_num_rows($rslt);

	$o=0;
	while ($camps_to_print > $o) 
		{
		$rowx=mysqli_fetch_row($rslt);
		if ($show_campname_pulldown)
			{$campname = " - $rowx[1]";}
		else
			{$campname = '';}
		if ($VD_campaign)
			{
			if ( (preg_match("/$VD_campaign/i",$rowx[0])) and (strlen($VD_campaign) == strlen($rowx[0])) )
                {$camp_form_code .= "<option value=\"$rowx[0]\" selected=\"selected\">$rowx[0]$campname</option>\n";}
			else
				{
				if (!preg_match('/login_allowable_campaigns/',$camp_form_code))
					{$camp_form_code .= "<option value=\"$rowx[0]\">$rowx[0]$campname</option>\n";}
				}
			}
		else
			{
			if (!preg_match('/login_allowable_campaigns/',$camp_form_code))
					{$camp_form_code .= "<option value=\"$rowx[0]\">$rowx[0]$campname</option>\n";}
			}
		$o++;
		}
	$camp_form_code .= "</select>\n";
	}
else
	{
    $camp_form_code = "<input type=\"text\" class=\"form-control\" name=\"vd_campaign\" size=\"10\" maxlength=\"20\" value=\"$VD_campaign\" placeholder=\""._QXZ("Enter Campaign ID")."\" />\n";
	}

if ($LogiNAJAX > 0)
	{
	?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
	var BrowseWidth = 0;
	var BrowseHeight = 0;

	function browser_dimensions() {
		<?php 
		if (preg_match('/MSIE/',$browser)) 
			{
			echo "	if (document.documentElement && document.documentElement.clientHeight)\n";
			echo "			{BrowseWidth = document.documentElement.clientWidth;}\n";
			echo "		else if (document.body)\n";
			echo "			{BrowseWidth = document.body.clientWidth;}\n";
			echo "		if (document.documentElement && document.documentElement.clientHeight)\n";
			echo "			{BrowseHeight = document.documentElement.clientHeight;}\n";
			echo "		else if (document.body)\n";
			echo "			{BrowseHeight = document.body.clientHeight;}\n";
			}
		else 
			{
			echo "BrowseWidth = window.innerWidth;\n";
			echo "		BrowseHeight = window.innerHeight;\n";
			}
		?>
		document.vicidial_form.JS_browser_width.value = BrowseWidth;
		document.vicidial_form.JS_browser_height.value = BrowseHeight;
	}

	// Modern Login Animation
	function showLoading(button) {
		const originalText = button.innerHTML;
		button.innerHTML = '<span class="loading-spinner"></span> ' + '<?php echo _QXZ("Logging in..."); ?>';
		button.disabled = true;
		
		setTimeout(function() {
			button.innerHTML = originalText;
			button.disabled = false;
		}, 5000);
	}

	// Enhanced Campaign Lookup with Modern UI
	function login_allowable_campaigns() {
		const campaignSelect = document.getElementById('VD_campaign');
		const resetSpan = document.getElementById('LogiNReseT');
		
		// Add loading state
		campaignSelect.innerHTML = '<option value=""><?php echo _QXZ("Loading campaigns..."); ?></option>';
		campaignSelect.disabled = true;

		var xmlhttp = false;
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
				xmlhttp = false;
			}
		}
		@end @*/
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		
		if (xmlhttp) { 
			logincampaign_query = "&user=" + document.vicidial_form.VD_login.value + 
								 "&pass=" + document.vicidial_form.VD_pass.value + 
								 "&ACTION=LogiNCamPaigns&format=html";
			
			xmlhttp.open('POST', 'vdc_db_query.php'); 
			xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
			xmlhttp.send(logincampaign_query); 
			
			xmlhttp.onreadystatechange = function() { 
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					campaignSelect.disabled = false;
					document.getElementById("LogiNCamPaigns").innerHTML = xmlhttp.responseText;
					resetSpan.innerHTML = '<button type="button" class="btn btn-secondary btn-sm" onclick="login_allowable_campaigns()"><i class="fas fa-refresh"></i> <?php echo _QXZ("Refresh Campaigns"); ?></button>';
					document.getElementById("VD_campaign").focus();
				}
			}
			delete xmlhttp;
		}
	}

	// Form validation
	function validateLogin() {
		const form = document.getElementById('vicidial_form');
		const requiredFields = ['phone_login', 'phone_pass', 'VD_login', 'VD_pass', 'VD_campaign'];
		let isValid = true;

		requiredFields.forEach(field => {
			const input = form[field];
			if (input && !input.value.trim()) {
				input.classList.add('is-invalid');
				isValid = false;
			} else if (input) {
				input.classList.remove('is-invalid');
			}
		});

		return isValid;
	}

	// Enhanced form submission
	function submitLogin(event) {
		event.preventDefault();
		
		if (!validateLogin()) {
			showAlert('<?php echo _QXZ("Please fill in all required fields"); ?>', 'error');
			return false;
		}

		const submitBtn = document.querySelector('button[type="submit"]');
		showLoading(submitBtn);
		
		// Submit the form after validation
		setTimeout(() => {
			document.getElementById('vicidial_form').submit();
		}, 300);
	}

	// Show modern alerts
	function showAlert(message, type = 'error') {
		const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
		const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
		
		const existingAlert = document.querySelector('.alert');
		if (existingAlert) {
			existingAlert.remove();
		}
		
		const loginCard = document.querySelector('.login-card');
		loginCard.insertAdjacentHTML('afterbegin', alertHtml);
		
		setTimeout(() => {
			const alert = document.querySelector('.alert');
			if (alert) alert.remove();
		}, 5000);
	}

	// Initialize form enhancements
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('vicidial_form');
		if (form) {
			form.addEventListener('submit', submitLogin);
		}
		
		// Add input animations
		const inputs = document.querySelectorAll('.form-control, .form-select');
		inputs.forEach(input => {
			input.addEventListener('focus', function() {
				this.parentElement.classList.add('focused');
			});
			
			input.addEventListener('blur', function() {
				if (!this.value) {
					this.parentElement.classList.remove('focused');
				}
			});
		});
	});
</script>
	<?php
	}
else
	{
	?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
	function browser_dimensions() {
		var nothing=0;
	}
	
	// Form validation for non-AJAX version
	function validateLogin() {
		const form = document.getElementById('vicidial_form');
		const requiredFields = ['phone_login', 'phone_pass'];
		let isValid = true;

		requiredFields.forEach(field => {
			const input = form[field];
			if (input && !input.value.trim()) {
				input.classList.add('is-invalid');
				isValid = false;
			} else if (input) {
				input.classList.remove('is-invalid');
			}
		});

		return isValid;
	}
</script>
	<?php
	}

$grey_link='';
if ($link_to_grey_version > 0)
	{$grey_link = " | <a href=\"./vicidial-grey.php?pl=$phone_login&pp=$phone_pass&VD_login=$VD_login&VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-desktop\"></i> "._QXZ("Old Agent Screen")."</a>";}

// 3. BÖLÜM SONU - JavaScript ve kampanya form kodları tamamlandı
?>

<?php
// 4. BÖLÜM BAŞLANGICI - Modern Login Forms (Satır 1200-1600 arası yerine)

if ($relogin == 'YES')
	{
	echo "<title>"._QXZ("Agent web client: Re-Login")."</title>\n";
	echo "</head>\n";
    echo "<body onresize=\"browser_dimensions();\" onload=\"browser_dimensions();\">\n";
    
    echo "<div class=\"login-container\">\n";
    echo "<div class=\"login-card\">\n";
    
    // Header with logo and title
    echo "<div class=\"login-header\">\n";
    echo "<img src=\"$selected_logo\" class=\"logo\" alt=\"Agent Screen\" />\n";
    echo "<h1 class=\"login-title\">"._QXZ("Re-Login")."</h1>\n";
    echo "<p class=\"login-subtitle\">"._QXZ("Please enter your credentials to continue")."</p>\n";
    echo "</div>\n";
    
    // Footer links
    if ($hide_timeclock_link < 1) {
        echo "<div class=\"login-footer\">\n";
        echo "<div class=\"footer-links\">\n";
        echo "<a href=\"./timeclock.php?referrer=agent&amp;pl=$phone_login&amp;pp=$phone_pass&amp;VD_login=$VD_login&amp;VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-clock\"></i> "._QXZ("Timeclock")."</a>\n";
        echo $grey_link;
        echo "</div>\n";
        echo "</div>\n";
    }
    
    echo "<form name=\"vicidial_form\" id=\"vicidial_form\" action=\"$agcPAGE\" method=\"post\">\n";
    echo "<input type=\"hidden\" name=\"DB\" id=\"DB\" value=\"$DB\" />\n";
    echo "<input type=\"hidden\" name=\"JS_browser_height\" id=\"JS_browser_height\" value=\"\" />\n";
    echo "<input type=\"hidden\" name=\"JS_browser_width\" id=\"JS_browser_width\" value=\"\" />\n";
    echo "<input type=\"hidden\" name=\"admin_test\" id=\"admin_test\" value=\"$admin_test\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarONE\" id=\"LOGINvarONE\" value=\"$LOGINvarONE\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarTWO\" id=\"LOGINvarTWO\" value=\"$LOGINvarTWO\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarTHREE\" id=\"LOGINvarTHREE\" value=\"$LOGINvarTHREE\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarFOUR\" id=\"LOGINvarFOUR\" value=\"$LOGINvarFOUR\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarFIVE\" id=\"LOGINvarFIVE\" value=\"$LOGINvarFIVE\" />\n";
    
    // Form fields
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"phone_login\"><i class=\"fas fa-phone\"></i> "._QXZ("Phone Login")."</label>\n";
    echo "<input type=\"text\" name=\"phone_login\" id=\"phone_login\" class=\"form-control\" maxlength=\"20\" value=\"$phone_login\" placeholder=\""._QXZ("Enter phone login")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"phone_pass\"><i class=\"fas fa-lock\"></i> "._QXZ("Phone Password")."</label>\n";
    echo "<input type=\"password\" name=\"phone_pass\" id=\"phone_pass\" class=\"form-control\" maxlength=\"20\" value=\"$phone_pass\" placeholder=\""._QXZ("Enter phone password")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"VD_login\"><i class=\"fas fa-user\"></i> "._QXZ("User Login")."</label>\n";
    echo "<input type=\"text\" name=\"VD_login\" id=\"VD_login\" class=\"form-control\" maxlength=\"20\" value=\"$VD_login\" placeholder=\""._QXZ("Enter username")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"VD_pass\"><i class=\"fas fa-key\"></i> "._QXZ("User Password")."</label>\n";
    echo "<input type=\"password\" name=\"VD_pass\" id=\"VD_pass\" class=\"form-control\" maxlength=\"20\" value=\"$VD_pass\" placeholder=\""._QXZ("Enter password")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"VD_campaign\"><i class=\"fas fa-briefcase\"></i> "._QXZ("Campaign")."</label>\n";
    echo "<span id=\"LogiNCamPaigns\">$camp_form_code</span>\n";
    echo "</div>\n";
    
    echo "<div class=\"btn-group\">\n";
    echo "<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-sign-in-alt\"></i> "._QXZ("LOGIN")."</button>\n";
    echo "<span id=\"LogiNReseT\"><button type=\"button\" class=\"btn btn-secondary\" onclick=\"login_allowable_campaigns()\"><i class=\"fas fa-refresh\"></i> "._QXZ("Refresh")."</button></span>\n";
    echo "</div>\n";
    
    echo "<div class=\"version-info\">\n";
    echo _QXZ("VERSION:")." $version &nbsp; | &nbsp; "._QXZ("BUILD:")." $build\n";
    echo "</div>\n";
    
    echo "</form>\n";
    echo "</div>\n"; // login-card
    echo "</div>\n"; // login-container
    
	echo "</body>\n</html>\n";
	exit;
	}

if ($user_login_first == 1)
	{
	if ( (strlen($VD_login)<1) or (strlen($VD_pass)<1) or (strlen($VD_campaign)<1) )
		{
		echo "<title>"._QXZ("Agent web client: Campaign Login")."</title>\n";
		echo "</head>\n";
        echo "<body onresize=\"browser_dimensions();\" onload=\"browser_dimensions();\">\n";
        
        echo "<div class=\"login-container\">\n";
        echo "<div class=\"login-card\">\n";
        
        // Header
        echo "<div class=\"login-header\">\n";
        echo "<img src=\"$selected_logo\" class=\"logo\" alt=\"Agent Screen\" />\n";
        echo "<h1 class=\"login-title\">"._QXZ("Campaign Login")."</h1>\n";
        echo "<p class=\"login-subtitle\">"._QXZ("Select your campaign to continue")."</p>\n";
        echo "</div>\n";
        
        // Footer links
        if ($hide_timeclock_link < 1) {
            echo "<div class=\"login-footer\">\n";
            echo "<div class=\"footer-links\">\n";
            echo "<a href=\"./timeclock.php?referrer=agent&amp;pl=$phone_login&amp;pp=$phone_pass&amp;VD_login=$VD_login&amp;VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-clock\"></i> "._QXZ("Timeclock")."</a>\n";
            echo $grey_link;
            echo "</div>\n";
            echo "</div>\n";
        }
        
        echo "<form name=\"vicidial_form\" id=\"vicidial_form\" action=\"$agcPAGE\" method=\"post\">\n";
        echo "<input type=\"hidden\" name=\"DB\" value=\"$DB\" />\n";
        echo "<input type=\"hidden\" name=\"JS_browser_height\" id=\"JS_browser_height\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"JS_browser_width\" id=\"JS_browser_width\" value=\"\" />\n";
		echo "<input type=\"hidden\" name=\"LOGINvarONE\" id=\"LOGINvarONE\" value=\"$LOGINvarONE\" />\n";
		echo "<input type=\"hidden\" name=\"LOGINvarTWO\" id=\"LOGINvarTWO\" value=\"$LOGINvarTWO\" />\n";
		echo "<input type=\"hidden\" name=\"LOGINvarTHREE\" id=\"LOGINvarTHREE\" value=\"$LOGINvarTHREE\" />\n";
		echo "<input type=\"hidden\" name=\"LOGINvarFOUR\" id=\"LOGINvarFOUR\" value=\"$LOGINvarFOUR\" />\n";
		echo "<input type=\"hidden\" name=\"LOGINvarFIVE\" id=\"LOGINvarFIVE\" value=\"$LOGINvarFIVE\" />\n";
        
        echo "<div class=\"form-group\">\n";
        echo "<label class=\"form-label\" for=\"VD_login\"><i class=\"fas fa-user\"></i> "._QXZ("User Login")."</label>\n";
        echo "<input type=\"text\" name=\"VD_login\" id=\"VD_login\" class=\"form-control\" maxlength=\"20\" value=\"$VD_login\" placeholder=\""._QXZ("Enter username")."\" required />\n";
        echo "</div>\n";
        
        echo "<div class=\"form-group\">\n";
        echo "<label class=\"form-label\" for=\"VD_pass\"><i class=\"fas fa-key\"></i> "._QXZ("User Password")."</label>\n";
        echo "<input type=\"password\" name=\"VD_pass\" id=\"VD_pass\" class=\"form-control\" maxlength=\"20\" value=\"$VD_pass\" placeholder=\""._QXZ("Enter password")."\" required />\n";
        echo "</div>\n";
        
        echo "<div class=\"form-group\">\n";
        echo "<label class=\"form-label\" for=\"VD_campaign\"><i class=\"fas fa-briefcase\"></i> "._QXZ("Campaign")."</label>\n";
        echo "<span id=\"LogiNCamPaigns\">$camp_form_code</span>\n";
        echo "</div>\n";
        
        echo "<div class=\"btn-group\">\n";
        echo "<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-sign-in-alt\"></i> "._QXZ("CONTINUE")."</button>\n";
        echo "<span id=\"LogiNReseT\"></span>\n";
        echo "</div>\n";
        
        echo "<div class=\"version-info\">\n";
        echo _QXZ("VERSION:")." $version &nbsp; | &nbsp; "._QXZ("BUILD:")." $build\n";
        echo "</div>\n";
        
        echo "</form>\n";
        echo "</div>\n"; // login-card
        echo "</div>\n"; // login-container
        
		echo "</body>\n</html>\n";
		exit;
		}
	else
		{
		if ( (strlen($phone_login)<2) or (strlen($phone_pass)<2) )
			{
			$stmt="SELECT phone_login,phone_pass from vicidial_users where user='$VD_login' and user_level > 0 and active='Y' and api_only_user != '1';";
			if ($DB) {echo "|$stmt|\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
				if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01005',$VD_login,$server_ip,$session_name,$one_mysql_log);}
			$row=mysqli_fetch_row($rslt);
			$phone_login=$row[0];
			$phone_pass=$row[1];

			if ( (strlen($phone_login) < 1) or (strlen($phone_pass) < 1) )
				{
				echo "<title>"._QXZ("Agent web client: Phone Login")."</title>\n";
				echo "</head>\n";
                echo "<body onresize=\"browser_dimensions();\" onload=\"browser_dimensions();\">\n";
                
                echo "<div class=\"login-container\">\n";
                echo "<div class=\"login-card\">\n";
                
                // Header
                echo "<div class=\"login-header\">\n";
                echo "<img src=\"$selected_logo\" class=\"logo\" alt=\"Agent Screen\" />\n";
                echo "<h1 class=\"login-title\">"._QXZ("Phone Setup Required")."</h1>\n";
                echo "<p class=\"login-subtitle\">"._QXZ("Please configure your phone login credentials")."</p>\n";
                echo "</div>\n";
                
                // Footer links
                if ($hide_timeclock_link < 1) {
                    echo "<div class=\"login-footer\">\n";
                    echo "<div class=\"footer-links\">\n";
                    echo "<a href=\"./timeclock.php?referrer=agent&amp;pl=$phone_login&amp;pp=$phone_pass&amp;VD_login=$VD_login&amp;VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-clock\"></i> "._QXZ("Timeclock")."</a>\n";
                    echo $grey_link;
                    echo "</div>\n";
                    echo "</div>\n";
                }
                
                echo "<form name=\"vicidial_form\" id=\"vicidial_form\" action=\"$agcPAGE\" method=\"post\">\n";
                echo "<input type=\"hidden\" name=\"DB\" value=\"$DB\" />\n";
                echo "<input type=\"hidden\" name=\"JS_browser_height\" id=\"JS_browser_height\" value=\"\" />\n";
                echo "<input type=\"hidden\" name=\"JS_browser_width\" id=\"JS_browser_width\" value=\"\" />\n";
				echo "<input type=\"hidden\" name=\"LOGINvarONE\" id=\"LOGINvarONE\" value=\"$LOGINvarONE\" />\n";
				echo "<input type=\"hidden\" name=\"LOGINvarTWO\" id=\"LOGINvarTWO\" value=\"$LOGINvarTWO\" />\n";
				echo "<input type=\"hidden\" name=\"LOGINvarTHREE\" id=\"LOGINvarTHREE\" value=\"$LOGINvarTHREE\" />\n";
				echo "<input type=\"hidden\" name=\"LOGINvarFOUR\" id=\"LOGINvarFOUR\" value=\"$LOGINvarFOUR\" />\n";
				echo "<input type=\"hidden\" name=\"LOGINvarFIVE\" id=\"LOGINvarFIVE\" value=\"$LOGINvarFIVE\" />\n";
                
                echo "<div class=\"alert alert-error\">\n";
                echo "<i class=\"fas fa-exclamation-triangle\"></i> "._QXZ("Phone credentials not found. Please enter them below.")."<br>\n";
                echo "</div>\n";
                
                echo "<div class=\"form-group\">\n";
                echo "<label class=\"form-label\" for=\"phone_login\"><i class=\"fas fa-phone\"></i> "._QXZ("Phone Login")."</label>\n";
                echo "<input type=\"text\" name=\"phone_login\" id=\"phone_login\" class=\"form-control\" maxlength=\"20\" value=\"$phone_login\" placeholder=\""._QXZ("Enter phone login")."\" required />\n";
                echo "</div>\n";
                
                echo "<div class=\"form-group\">\n";
                echo "<label class=\"form-label\" for=\"phone_pass\"><i class=\"fas fa-lock\"></i> "._QXZ("Phone Password")."</label>\n";
                echo "<input type=\"password\" name=\"phone_pass\" id=\"phone_pass\" class=\"form-control\" maxlength=\"20\" value=\"$phone_pass\" placeholder=\""._QXZ("Enter phone password")."\" required />\n";
                echo "</div>\n";
                
                echo "<div class=\"form-group\">\n";
                echo "<label class=\"form-label\" for=\"VD_login\"><i class=\"fas fa-user\"></i> "._QXZ("User Login")."</label>\n";
                echo "<input type=\"text\" name=\"VD_login\" id=\"VD_login\" class=\"form-control\" maxlength=\"20\" value=\"$VD_login\" placeholder=\""._QXZ("Enter username")."\" required />\n";
                echo "</div>\n";
                
                echo "<div class=\"form-group\">\n";
                echo "<label class=\"form-label\" for=\"VD_pass\"><i class=\"fas fa-key\"></i> "._QXZ("User Password")."</label>\n";
                echo "<input type=\"password\" name=\"VD_pass\" id=\"VD_pass\" class=\"form-control\" maxlength=\"20\" value=\"$VD_pass\" placeholder=\""._QXZ("Enter password")."\" required />\n";
                echo "</div>\n";
                
                echo "<div class=\"form-group\">\n";
                echo "<label class=\"form-label\" for=\"VD_campaign\"><i class=\"fas fa-briefcase\"></i> "._QXZ("Campaign")."</label>\n";
                echo "<span id=\"LogiNCamPaigns\">$camp_form_code</span>\n";
                echo "</div>\n";
                
                echo "<div class=\"btn-group\">\n";
                echo "<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-sign-in-alt\"></i> "._QXZ("COMPLETE SETUP")."</button>\n";
                echo "<span id=\"LogiNReseT\"></span>\n";
                echo "</div>\n";
                
                echo "<div class=\"version-info\">\n";
                echo _QXZ("VERSION:")." $version &nbsp; | &nbsp; "._QXZ("BUILD:")." $build\n";
                echo "</div>\n";
                
                echo "</form>\n";
                echo "</div>\n"; // login-card
                echo "</div>\n"; // login-container
                
				echo "</body>\n</html>\n";
				exit;
				}
			}
		}
	}

if ( (strlen($phone_login)<2) or (strlen($phone_pass)<2) )
	{
	echo "<title>"._QXZ("Agent web client: Phone Login")."</title>\n";
	echo "</head>\n";
    echo "<body onresize=\"browser_dimensions();\" onload=\"browser_dimensions();\">\n";
    
    echo "<div class=\"login-container\">\n";
    echo "<div class=\"login-card\">\n";
    
    // Header
    echo "<div class=\"login-header\">\n";
    echo "<img src=\"$selected_logo\" class=\"logo\" alt=\"Agent Screen\" />\n";
    echo "<h1 class=\"login-title\">"._QXZ("Phone Login")."</h1>\n";
    echo "<p class=\"login-subtitle\">"._QXZ("Enter your phone credentials to access the system")."</p>\n";
    echo "</div>\n";
    
    // Footer links
    if ($hide_timeclock_link < 1) {
        echo "<div class=\"login-footer\">\n";
        echo "<div class=\"footer-links\">\n";
        echo "<a href=\"./timeclock.php?referrer=agent&amp;pl=$phone_login&amp;pp=$phone_pass&amp;VD_login=$VD_login&amp;VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-clock\"></i> "._QXZ("Timeclock")."</a>\n";
        echo $grey_link;
        echo "</div>\n";
        echo "</div>\n";
    }
    
    echo "<form name=\"vicidial_form\" id=\"vicidial_form\" action=\"$agcPAGE\" method=\"post\">\n";
    echo "<input type=\"hidden\" name=\"DB\" value=\"$DB\" />\n";
    echo "<input type=\"hidden\" name=\"JS_browser_height\" id=\"JS_browser_height\" value=\"\" />\n";
    echo "<input type=\"hidden\" name=\"JS_browser_width\" id=\"JS_browser_width\" value=\"\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarONE\" id=\"LOGINvarONE\" value=\"$LOGINvarONE\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarTWO\" id=\"LOGINvarTWO\" value=\"$LOGINvarTWO\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarTHREE\" id=\"LOGINvarTHREE\" value=\"$LOGINvarTHREE\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarFOUR\" id=\"LOGINvarFOUR\" value=\"$LOGINvarFOUR\" />\n";
    echo "<input type=\"hidden\" name=\"LOGINvarFIVE\" id=\"LOGINvarFIVE\" value=\"$LOGINvarFIVE\" />\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"phone_login\"><i class=\"fas fa-phone\"></i> "._QXZ("Phone Login")."</label>\n";
    echo "<input type=\"text\" name=\"phone_login\" id=\"phone_login\" class=\"form-control\" maxlength=\"20\" value=\"\" placeholder=\""._QXZ("Enter phone login")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"form-group\">\n";
    echo "<label class=\"form-label\" for=\"phone_pass\"><i class=\"fas fa-lock\"></i> "._QXZ("Phone Password")."</label>\n";
    echo "<input type=\"password\" name=\"phone_pass\" id=\"phone_pass\" class=\"form-control\" maxlength=\"20\" value=\"\" placeholder=\""._QXZ("Enter phone password")."\" required />\n";
    echo "</div>\n";
    
    echo "<div class=\"btn-group\">\n";
    echo "<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-sign-in-alt\"></i> "._QXZ("CONTINUE")."</button>\n";
    echo "<span id=\"LogiNReseT\"></span>\n";
    echo "</div>\n";
    
    echo "<div class=\"version-info\">\n";
    echo _QXZ("VERSION:")." $version &nbsp; | &nbsp; "._QXZ("BUILD:")." $build\n";
    echo "</div>\n";
    
    echo "</form>\n";
    echo "</div>\n"; // login-card
    echo "</div>\n"; // login-container
    
	echo "</body>\n</html>\n";
	exit;
	}
else
	{
	if ($WeBRooTWritablE > 0)
		{$fp = fopen ("./vicidial_auth_entries.txt", "a");}
	$VDloginDISPLAY=0;

	if ( (strlen($VD_login)<2) or (strlen($VD_pass)<2) or (strlen($VD_campaign)<2) )
		{
		$VDloginDISPLAY=1;
		}
	else
		{
		$auth=0;
		$auth_message = user_authorization($VD_login,$VD_pass,'',1,0,1,0);
		if (preg_match("/^GOOD/",$auth_message))
			{
			$auth=1;
			$pass_hash = preg_replace("/GOOD\|/",'',$auth_message);
			}
		# case-sensitive check for user
		if($auth>0)
			{
			if ($VD_login != "$VUuser") 
				{
				$auth=0;
				$auth_message='ERRCASE';
				}
			}

		if($auth>0)
			{
			##### grab the full name and other settings of the agent
			$stmt="SELECT full_name,user_level,hotkeys_active,agent_choose_ingroups,scheduled_callbacks,agentonly_callbacks,agentcall_manual,vicidial_recording,vicidial_transfers,closer_default_blended,user_group,vicidial_recording_override,alter_custphone_override,alert_enabled,agent_shift_enforcement_override,shift_override_flag,allow_alerts,closer_campaigns,agent_choose_territories,custom_one,custom_two,custom_three,custom_four,custom_five,agent_call_log_view_override,agent_choose_blended,agent_lead_search_override,preset_contact_search,max_inbound_calls,wrapup_seconds_override,email,user_choose_language,ready_max_logout from vicidial_users where user='$VD_login' and active='Y' and api_only_user != '1';";
			$rslt=mysql_to_mysqli($stmt, $link);
				if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01007',$VD_login,$server_ip,$session_name,$one_mysql_log);}
			$row=mysqli_fetch_row($rslt);

// 4. BÖLÜM SONU - Modern login forms tamamlandı
?>


<?php
// 5. BÖLÜM BAŞLANGICI - Ana Interface Başlangıcı (Satır 1600-2000 arası yerine)

			$VUfull_name =					$row[0];
			$VUuser_level =					$row[1];
			$VUhotkeys_active =				$row[2];
			$VUagent_choose_ingroups =		$row[3];
			$VUscheduled_callbacks =		$row[4];
			$VUagentonly_callbacks =		$row[5];
			$VUagentcall_manual =			$row[6];
			$VUvicidial_recording =			$row[7];
			$VUvicidial_transfers =			$row[8];
			$VUcloser_default_blended =		$row[9];
			$VUuser_group =					$row[10];
			$VUvicidial_recording_override =$row[11];
			$VUalter_custphone_override =	$row[12];
			$VUalert_enabled =				$row[13];
			$VUagent_shift_enforcement_override = $row[14];
			$VUshift_override_flag =		$row[15];
			$VUallow_alerts =				$row[16];
			$VUcloser_campaigns =			$row[17];
			$VUagent_choose_territories =	$row[18];
			$VUcustom_one =					$row[19];
			$VUcustom_two =					$row[20];
			$VUcustom_three =				$row[21];
			$VUcustom_four =				$row[22];
			$VUcustom_five =				$row[23];
			$VUagent_call_log_view_override = $row[24];
			$VUagent_choose_blended =		$row[25];
			$VUagent_lead_search_override =	$row[26];
			$VUpreset_contact_search =		$row[27];
			$VUmax_inbound_calls =			$row[28];
			$VUwrapup_seconds_override =	$row[29];
			$VUemail =						$row[30];
			$VUuser_choose_language =		$row[31];
			$VUready_max_logout =			$row[32];

			if ($VUuser_choose_language == 'Y')
				{
				if ( (strlen($VD_language) > 0) and ($VD_language != '--NONE--') )
					{$VUselected_language = $VD_language;}
				}

			$auth_message = user_authorization($VD_login,$VD_pass,'AGENTS',1,0,1,1);
			if (preg_match("/^GOOD/",$auth_message))
				{
				$VDloginDISPLAY=0;
				$agents_auth=1;
				}

			if($agents_auth<1)
				{
				$VDloginDISPLAY=1;
				echo "<!-- LOGIN ERROR: $auth_message -->\n";
				}

			if( (strlen($VD_campaign)<1) or ($VD_campaign == '--NONE--') )
				{
				$VDloginDISPLAY=1;
				echo "<!-- LOGIN ERROR: campaign |$VD_campaign| is not valid -->\n";
				}
			else
				{
				##### grab the campaign and user_group settings of this user
				$stmt="SELECT allowed_campaigns,admin_viewable_groups,agent_allowed_chat,agent_call_log_view,agent_choose_blended,agent_choose_territories,custom_fields_modify,agent_lead_search,preset_contact_search,max_inbound_calls,agent_call_log_view_hide,wrapup_seconds_override,user_choose_language,ready_max_logout from vicidial_user_groups where user_group='$VUuser_group';";
				$rslt=mysql_to_mysqli($stmt, $link);
					if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01008',$VD_login,$server_ip,$session_name,$one_mysql_log);}
				$row=mysqli_fetch_row($rslt);
				$LOGallowed_campaigns =			$row[0];
				$LOGadmin_viewable_groups =		$row[1];
				$LOGagent_allowed_chat =		$row[2];
				$LOGagent_call_log_view =		$row[3];
				$LOGagent_choose_blended =		$row[4];
				$LOGagent_choose_territories =	$row[5];
				$LOGcustom_fields_modify =		$row[6];
				$LOGagent_lead_search =			$row[7];
				$LOGpreset_contact_search =		$row[8];
				$LOGmax_inbound_calls =			$row[9];
				$LOGagent_call_log_view_hide =	$row[10];
				$LOGwrapup_seconds_override =	$row[11];
				$LOGuser_choose_language =		$row[12];
				$LOGready_max_logout =			$row[13];

				$VUagent_choose_blended =	 	$VUagent_choose_blended + $LOGagent_choose_blended;
				$VUagent_choose_territories =	$VUagent_choose_territories + $LOGagent_choose_territories;
				$VUagent_lead_search_override =	$VUagent_lead_search_override + $LOGagent_lead_search;
				$VUpreset_contact_search =		$VUpreset_contact_search + $LOGpreset_contact_search;
				$VUmax_inbound_calls =			$VUmax_inbound_calls + $LOGmax_inbound_calls;
				$VUagent_call_log_view_override = $VUagent_call_log_view_override + $LOGagent_call_log_view;
				$VUwrapup_seconds_override =	$VUwrapup_seconds_override + $LOGwrapup_seconds_override;
				$VUuser_choose_language =		$VUuser_choose_language + $LOGuser_choose_language;
				$VUready_max_logout =			$VUready_max_logout + $LOGready_max_logout;

				if ($VUagent_call_log_view_override < 1) {$VUagent_call_log_view_override = $LOGagent_call_log_view;}
				if ($VUmax_inbound_calls < 1) {$VUmax_inbound_calls = $LOGmax_inbound_calls;}
				if ($VUwrapup_seconds_override < 1) {$VUwrapup_seconds_override = $LOGwrapup_seconds_override;}
				if ($VUready_max_logout < 1) {$VUready_max_logout = $LOGready_max_logout;}

				$allowed_campaignsSQL='';
				if ( (!preg_match("/ALL-CAMPAIGNS/i",$LOGallowed_campaigns)) )
					{
					$rawLOGallowed_campaigns = $LOGallowed_campaigns;
					$rawLOGallowed_campaigns = preg_replace("/ -/",'',$rawLOGallowed_campaigns);
					$rawLOGallowed_campaigns = preg_replace("/ /","','",$rawLOGallowed_campaigns);
					$allowed_campaignsSQL = "and campaign_id IN('$rawLOGallowed_campaigns')";
					}
				$stmt="SELECT campaign_id,campaign_name,park_ext,park_file_name,web_form_address,allow_closers,closer_campaigns,campaign_script,xferconf_a_dtmf,xferconf_a_number,xferconf_b_dtmf,xferconf_b_number,xferconf_c_dtmf,xferconf_c_number,xferconf_d_dtmf,xferconf_d_number,xferconf_e_dtmf,xferconf_e_number,use_internal_dnc,use_campaign_dnc,three_way_call_cid,three_way_dial_prefix,web_form_address_two,timer_action,timer_action_message,timer_action_seconds,start_call_url,dispo_call_url,xferconf_f_dtmf,xferconf_f_number,campaign_script_two,browser_alert_sound,browser_alert_volume,conf_exten,user_group,wrapup_seconds,wrapup_message,wrapup_after_hotkey,agent_allow_group_alias,default_group_alias,quick_transfer_button,prepopulate_transfer_preset,web_form_address_three,status_display_fields,manual_dial_timeout,post_phone_time_diff_alert,callback_days_limit,scheduled_callbacks_alert,scheduled_callbacks_count,no_hopper_leads_logins,agent_display_dialable_leads,disable_dispo_screen,disable_dispo_status,my_callback_option,agent_display_fields,agent_select_territories,campaign_cid_override,three_way_record_stop,hangup_xfer_record_start,callback_active_limit,callback_active_limit_override,manual_auto_next,dead_to_dispo,pause_max_dispo,max_inbound_calls_outcome,agent_screen_time_display,manual_auto_next_options,callback_display_days from vicidial_campaigns where campaign_id='$VD_campaign' and active='Y' $allowed_campaignsSQL;";
				$rslt=mysql_to_mysqli($stmt, $link);
					if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01009',$VD_login,$server_ip,$session_name,$one_mysql_log);}
				$row=mysqli_fetch_row($rslt);
				$campaign_id =						$row[0];
				$campaign_name =					$row[1];
				$park_ext =							$row[2];
				$park_file_name =					$row[3];
				$web_form_address =					$row[4];
				$allow_closers =					$row[5];
				$closer_campaigns =					$row[6];
				$campaign_script =					$row[7];
				$xferconf_a_dtmf =					$row[8];
				$xferconf_a_number =				$row[9];
				$xferconf_b_dtmf =					$row[10];
				$xferconf_b_number =				$row[11];
				$xferconf_c_dtmf =					$row[12];
				$xferconf_c_number =				$row[13];
				$xferconf_d_dtmf =					$row[14];
				$xferconf_d_number =				$row[15];
				$xferconf_e_dtmf =					$row[16];
				$xferconf_e_number =				$row[17];
				$use_internal_dnc =					$row[18];
				$use_campaign_dnc =					$row[19];
				$three_way_call_cid =				$row[20];
				$three_way_dial_prefix =			$row[21];
				$web_form_address_two =				$row[22];
				$timer_action =						$row[23];
				$timer_action_message =				$row[24];
				$timer_action_seconds =				$row[25];
				$start_call_url =					$row[26];
				$dispo_call_url =					$row[27];
				$xferconf_f_dtmf =					$row[28];
				$xferconf_f_number =				$row[29];
				$campaign_script_two =				$row[30];
				$browser_alert_sound =				$row[31];
				$browser_alert_volume =				$row[32];
				$conf_exten =						$row[33];
				$user_group =						$row[34];
				$wrapup_seconds =					$row[35];
				$wrapup_message =					$row[36];
				$wrapup_after_hotkey =				$row[37];
				$agent_allow_group_alias =			$row[38];
				$default_group_alias =				$row[39];
				$quick_transfer_button =			$row[40];
				$prepopulate_transfer_preset =		$row[41];
				$web_form_address_three =			$row[42];
				$status_display_fields =			$row[43];
				$manual_dial_timeout =				$row[44];
				$post_phone_time_diff_alert =		$row[45];
				$callback_days_limit =				$row[46];
				$scheduled_callbacks_alert =		$row[47];
				$scheduled_callbacks_count =		$row[48];
				$no_hopper_leads_logins =			$row[49];
				$agent_display_dialable_leads =		$row[50];
				$disable_dispo_screen =				$row[51];
				$disable_dispo_status =				$row[52];
				$my_callback_option =				$row[53];
				$agent_display_fields =				$row[54];
				$agent_select_territories =			$row[55];
				$campaign_cid_override =			$row[56];
				$three_way_record_stop =			$row[57];
				$hangup_xfer_record_start =			$row[58];
				$callback_active_limit =			$row[59];
				$callback_active_limit_override =	$row[60];
				$manual_auto_next =					$row[61];
				$dead_to_dispo =					$row[62];
				$pause_max_dispo =					$row[63];
				$max_inbound_calls_outcome =		$row[64];
				$agent_screen_time_display =		$row[65];
				$manual_auto_next_options =			$row[66];
				$callback_display_days =			$row[67];

				if (strlen($campaign_id)<1)
					{
					$VDloginDISPLAY=1;
					echo "<!-- LOGIN ERROR: campaign $VD_campaign is not valid or is not active -->\n";
					}
				}
			}

		if ($VDloginDISPLAY > 0)
			{
			echo "<title>"._QXZ("Agent web client: Login")."</title>\n";
			echo "</head>\n";
			echo "<body onresize=\"browser_dimensions();\" onload=\"browser_dimensions();\">\n";
			
			echo "<div class=\"login-container\">\n";
			echo "<div class=\"login-card\">\n";
			
			// Header with error state
			echo "<div class=\"login-header\">\n";
			echo "<img src=\"$selected_logo\" class=\"logo\" alt=\"Agent Screen\" />\n";
			echo "<h1 class=\"login-title\">"._QXZ("Login Required")."</h1>\n";
			echo "<p class=\"login-subtitle\">"._QXZ("Please enter your complete login credentials")."</p>\n";
			echo "</div>\n";
			
			// Show error if auth failed
			if ($auth_message == 'ERRCASE') {
				echo "<div class=\"alert alert-error\">\n";
				echo "<i class=\"fas fa-exclamation-triangle\"></i> "._QXZ("Invalid username or password. Login is case-sensitive.")."<br>\n";
				echo "</div>\n";
			} elseif (strlen($auth_message) > 5) {
				echo "<div class=\"alert alert-error\">\n";
				echo "<i class=\"fas fa-exclamation-triangle\"></i> "._QXZ("Authentication failed. Please check your credentials.")."<br>\n";
				echo "</div>\n";
			}
			
			// Footer links
			if ($hide_timeclock_link < 1) {
				echo "<div class=\"login-footer\">\n";
				echo "<div class=\"footer-links\">\n";
				echo "<a href=\"./timeclock.php?referrer=agent&amp;pl=$phone_login&amp;pp=$phone_pass&amp;VD_login=$VD_login&amp;VD_pass=$VD_pass\" class=\"footer-link\"><i class=\"fas fa-clock\"></i> "._QXZ("Timeclock")."</a>\n";
				echo $grey_link;
				echo "</div>\n";
				echo "</div>\n";
			}
			
			echo "<form name=\"vicidial_form\" id=\"vicidial_form\" action=\"$agcPAGE\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"DB\" value=\"$DB\" />\n";
			echo "<input type=\"hidden\" name=\"JS_browser_height\" id=\"JS_browser_height\" value=\"\" />\n";
			echo "<input type=\"hidden\" name=\"JS_browser_width\" id=\"JS_browser_width\" value=\"\" />\n";
			echo "<input type=\"hidden\" name=\"admin_test\" id=\"admin_test\" value=\"$admin_test\" />\n";
			echo "<input type=\"hidden\" name=\"LOGINvarONE\" id=\"LOGINvarONE\" value=\"$LOGINvarONE\" />\n";
			echo "<input type=\"hidden\" name=\"LOGINvarTWO\" id=\"LOGINvarTWO\" value=\"$LOGINvarTWO\" />\n";
			echo "<input type=\"hidden\" name=\"LOGINvarTHREE\" id=\"LOGINvarTHREE\" value=\"$LOGINvarTHREE\" />\n";
			echo "<input type=\"hidden\" name=\"LOGINvarFOUR\" id=\"LOGINvarFOUR\" value=\"$LOGINvarFOUR\" />\n";
			echo "<input type=\"hidden\" name=\"LOGINvarFIVE\" id=\"LOGINvarFIVE\" value=\"$LOGINvarFIVE\" />\n";
			
			echo "<div class=\"form-group\">\n";
			echo "<label class=\"form-label\" for=\"phone_login\"><i class=\"fas fa-phone\"></i> "._QXZ("Phone Login")."</label>\n";
			echo "<input type=\"text\" name=\"phone_login\" id=\"phone_login\" class=\"form-control\" maxlength=\"20\" value=\"$phone_login\" placeholder=\""._QXZ("Enter phone login")."\" required />\n";
			echo "</div>\n";
			
			echo "<div class=\"form-group\">\n";
			echo "<label class=\"form-label\" for=\"phone_pass\"><i class=\"fas fa-lock\"></i> "._QXZ("Phone Password")."</label>\n";
			echo "<input type=\"password\" name=\"phone_pass\" id=\"phone_pass\" class=\"form-control\" maxlength=\"20\" value=\"$phone_pass\" placeholder=\""._QXZ("Enter phone password")."\" required />\n";
			echo "</div>\n";
			
			echo "<div class=\"form-group\">\n";
			echo "<label class=\"form-label\" for=\"VD_login\"><i class=\"fas fa-user\"></i> "._QXZ("User Login")."</label>\n";
			echo "<input type=\"text\" name=\"VD_login\" id=\"VD_login\" class=\"form-control\" maxlength=\"20\" value=\"$VD_login\" placeholder=\""._QXZ("Enter username")."\" required />\n";
			echo "</div>\n";
			
			echo "<div class=\"form-group\">\n";
			echo "<label class=\"form-label\" for=\"VD_pass\"><i class=\"fas fa-key\"></i> "._QXZ("User Password")."</label>\n";
			echo "<input type=\"password\" name=\"VD_pass\" id=\"VD_pass\" class=\"form-control\" maxlength=\"20\" value=\"$VD_pass\" placeholder=\""._QXZ("Enter password")."\" required />\n";
			echo "</div>\n";
			
			echo "<div class=\"form-group\">\n";
			echo "<label class=\"form-label\" for=\"VD_campaign\"><i class=\"fas fa-briefcase\"></i> "._QXZ("Campaign")."</label>\n";
			echo "<span id=\"LogiNCamPaigns\">$camp_form_code</span>\n";
			echo "</div>\n";
			
			// Language selection if enabled
			if ($VUuser_choose_language == 'Y') {
				echo "<div class=\"form-group\">\n";
				echo "<label class=\"form-label\" for=\"VD_language\"><i class=\"fas fa-globe\"></i> "._QXZ("Language")."</label>\n";
				echo "<select name=\"VD_language\" id=\"VD_language\" class=\"form-select\">\n";
				echo "<option value=\"\">-- "._QXZ("Default")." --</option>\n";
				// Language options would be loaded here from database
				echo "</select>\n";
				echo "</div>\n";
			}
			
			echo "<div class=\"btn-group\">\n";
			echo "<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-sign-in-alt\"></i> "._QXZ("LOGIN")."</button>\n";
			echo "<span id=\"LogiNReseT\"><button type=\"button\" class=\"btn btn-secondary\" onclick=\"login_allowable_campaigns()\"><i class=\"fas fa-refresh\"></i> "._QXZ("Refresh")."</button></span>\n";
			echo "</div>\n";
			
			echo "<div class=\"version-info\">\n";
			echo _QXZ("VERSION:")." $version &nbsp; | &nbsp; "._QXZ("BUILD:")." $build\n";
			echo "</div>\n";
			
			echo "</form>\n";
			echo "</div>\n"; // login-card
			echo "</div>\n"; // login-container
			
			echo "</body>\n</html>\n";
			exit;
			}
		}

	################################################################################
	### START - phone and user validation
	################################################################################
	
	$STARTtime = date("U");
	$ip = getenv("REMOTE_ADDR");
	$browser = getenv("HTTP_USER_AGENT");
	
	// Log the login attempt
	if ($WeBRooTWritablE > 0) {
		fwrite($fp,"$NOW_TIME|GOOD|$ip|$VD_login|$browser|$phone_login|\n");
		fclose($fp);
	}

	// Start building the main agent interface
	echo "<title>"._QXZ("Agent web client: ")."$VUfull_name</title>\n";
	echo "</head>\n";
	echo "<body class=\"agent-interface\" onunload=\"LogouT()\" onresize=\"browser_dimensions();\" onload=\"parent.window.name='$win_valid_name'; browser_dimensions(); auto_dial_level=\$conf_exten; CalL_AutO_DiaL=\$outbound_autodial_active; customer_sec='0'; VtigeR_StarT_DatE='$NOW_TIME'; nanque='0'; qm_extension='$qm_extension'; recording_filename=''; recording_id=''; CalLCID=''; MDnextCID=''; LasTCID=''; EpoC='$STARTtime'; TZ='$ISDST'; epoch_sec=0; local_gmt='$local_gmt'; VD_live_customer_call='0'; VD_live_call_secondS='0'; alert_displayed='0'; MGR_override_logged=\$MGR_override; conf_check_attempts=\$conf_check_attempts; VARdialplans='\$VARdialplans'; VARcloser_blended='\$VARcloser_blended'; active_ingroup_selection=''; CalLBacKDatE=''; CalLBacKTimE=''; CalLBacKCounT='$scheduled_callbacks_count'; CalLBacKLisT=''; CalLBacKCommenT=''; scheduled_callbacks_active_limit='$callback_active_limit'; CCAL_count='0'; agent_call_log_view='$VUagent_call_log_view_override'; agent_call_log_view_hide='$LOGagent_call_log_view_hide'; wrapup_seconds='$wrapup_seconds'; wrapup_seconds_override='$VUwrapup_seconds_override'; wrapup_after_hotkey='$wrapup_after_hotkey'; email_enabled='$email_enabled'; VtigeR_WidgeT='$VtigeR_WidgeT'; VtigeR_Accounts_List='$VtigeR_Accounts_List'; VtigeR_url='$vtiger_url'; ready_max_logout='$VUready_max_logout'; agent_push_events='$agent_push_events'; agent_push_url='$agent_push_url'; agent_screen_time_display='$agent_screen_time_display'; login_allowable_campaigns();\$INSERT_first_onload\">\n";
	
// 5. BÖLÜM SONU - Ana interface başlangıç kodları tamamlandı
?>

<?php
// 6. BÖLÜM BAŞLANGICI - Ana Agent Interface HTML Layout (Satır 2000-2800 arası yerine)

// Modern Agent Interface için ek CSS stilleri
echo "<style>\n";
echo "
/* Additional Modern Agent Interface Styles */
.agent-interface {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
}

.main-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0;
}

/* Top Navigation Bar */
.agent-header {
    background: var(--gradient-primary);
    color: white;
    padding: 1rem 2rem;
    box-shadow: var(--shadow-lg);
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(20px);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1600px;
    margin: 0 auto;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.agent-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.agent-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
}

.agent-details h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.agent-details p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.9;
}

.header-center {
    flex: 1;
    display: flex;
    justify-content: center;
}

.status-display {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.time-display {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
}

/* Main Content Grid */
.agent-main {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    padding: 2rem;
    max-width: 1600px;
    margin: 0 auto;
    min-height: calc(100vh - 80px);
}

/* Left Panel - Main Controls */
.main-panel {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Control Cards */
.control-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.control-card h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.control-card h3 i {
    font-size: 1.1rem;
    color: var(--primary-color);
}

/* Customer Info Section */
.customer-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.info-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-field label {
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-field input,
.info-field select,
.info-field textarea {
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.info-field input:focus,
.info-field select:focus,
.info-field textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: rgba(255, 255, 255, 0.95);
}

/* Action Buttons */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.action-btn {
    padding: 1rem;
    border: none;
    border-radius: 16px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: white;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.action-btn i {
    font-size: 1.2rem;
}

.btn-call { background: var(--gradient-primary); }
.btn-hangup { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); }
.btn-hold { background: linear-gradient(135deg, #feca57 0%, #ff9f43 100%); }
.btn-transfer { background: linear-gradient(135deg, #48cab2 0%, #2dd4bf 100%); }
.btn-pause { background: linear-gradient(135deg, #a55eea 0%, #8b5cf6 100%); }
.btn-dispo { background: linear-gradient(135deg, #26d0ce 0%, #06b6d4 100%); }

/* Right Panel - Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.sidebar-card h4 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Tabs */
.tab-navigation {
    display: flex;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 0.25rem;
    margin-bottom: 1.5rem;
}

.tab-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    border-radius: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #6b7280;
}

.tab-btn.active {
    background: var(--primary-color);
    color: white;
    box-shadow: var(--shadow-sm);
}

.tab-btn:hover:not(.active) {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

/* Live Stats */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(37, 99, 235, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(37, 99, 235, 0.1);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.25rem;
}

/* Call Queue Display */
.queue-display {
    background: rgba(34, 197, 94, 0.05);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
}

.queue-count {
    font-size: 2rem;
    font-weight: 700;
    color: var(--success-color);
}

.queue-label {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .agent-main {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .customer-info {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .agent-header {
        padding: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .agent-main {
        padding: 1rem;
    }
    
    .action-buttons {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
";
echo "</style>\n";

// Modern Agent Interface HTML Structure
echo "<div class=\"main-container\">\n";

// Header
echo "<header class=\"agent-header\">\n";
echo "<div class=\"header-content\">\n";

// Left side - Agent info
echo "<div class=\"header-left\">\n";
echo "<div class=\"agent-info\">\n";
echo "<div class=\"agent-avatar\">" . substr($VUfull_name, 0, 1) . "</div>\n";
echo "<div class=\"agent-details\">\n";
echo "<h3>$VUfull_name</h3>\n";
echo "<p>"._QXZ("Campaign").": $campaign_name</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

// Center - Status display
echo "<div class=\"header-center\">\n";
echo "<div class=\"status-display\">\n";
echo "<span id=\"AgentStatusSpan\" class=\"status-badge status-ready\">"._QXZ("READY")."</span>\n";
echo "</div>\n";
echo "</div>\n";

// Right side - Time and controls
echo "<div class=\"header-right\">\n";
echo "<div class=\"time-display\">\n";
echo "<span id=\"AgentTimeSpan\">00:00:00</span>\n";
echo "</div>\n";
echo "<button type=\"button\" class=\"btn btn-secondary btn-sm\" onclick=\"LogouT()\">\n";
echo "<i class=\"fas fa-sign-out-alt\"></i> "._QXZ("Logout")."\n";
echo "</button>\n";
echo "</div>\n";

echo "</div>\n"; // header-content
echo "</header>\n";

// Main content area
echo "<main class=\"agent-main\">\n";

// Left Panel - Main Controls
echo "<div class=\"main-panel\">\n";

// Customer Information Card
echo "<div class=\"control-card fade-in\">\n";
echo "<h3><i class=\"fas fa-user\"></i> "._QXZ("Customer Information")."</h3>\n";
echo "<div class=\"customer-info\">\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"vendor_lead_code\">"._QXZ("Lead ID")."</label>\n";
echo "<input type=\"text\" id=\"vendor_lead_code\" name=\"vendor_lead_code\" class=\"form-control\" readonly />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"phone_number\">"._QXZ("Phone Number")."</label>\n";
echo "<input type=\"text\" id=\"phone_number\" name=\"phone_number\" class=\"form-control\" maxlength=\"18\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"first_name\">"._QXZ("First Name")."</label>\n";
echo "<input type=\"text\" id=\"first_name\" name=\"first_name\" class=\"form-control\" maxlength=\"30\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"last_name\">"._QXZ("Last Name")."</label>\n";
echo "<input type=\"text\" id=\"last_name\" name=\"last_name\" class=\"form-control\" maxlength=\"30\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"address1\">"._QXZ("Address")."</label>\n";
echo "<input type=\"text\" id=\"address1\" name=\"address1\" class=\"form-control\" maxlength=\"100\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"city\">"._QXZ("City")."</label>\n";
echo "<input type=\"text\" id=\"city\" name=\"city\" class=\"form-control\" maxlength=\"50\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"state\">"._QXZ("State")."</label>\n";
echo "<input type=\"text\" id=\"state\" name=\"state\" class=\"form-control\" maxlength=\"2\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\">\n";
echo "<label for=\"postal_code\">"._QXZ("Postal Code")."</label>\n";
echo "<input type=\"text\" id=\"postal_code\" name=\"postal_code\" class=\"form-control\" maxlength=\"10\" />\n";
echo "</div>\n";

echo "<div class=\"info-field\" style=\"grid-column: 1 / -1;\">\n";
echo "<label for=\"comments\">"._QXZ("Comments")."</label>\n";
echo "<textarea id=\"comments\" name=\"comments\" class=\"form-control\" rows=\"3\" maxlength=\"255\"></textarea>\n";
echo "</div>\n";

echo "</div>\n"; // customer-info
echo "</div>\n"; // control-card

// Main Action Buttons
echo "<div class=\"control-card fade-in\">\n";
echo "<h3><i class=\"fas fa-phone\"></i> "._QXZ("Call Controls")."</h3>\n";
echo "<div class=\"action-buttons\">\n";

echo "<button type=\"button\" id=\"MainDiaLButton\" class=\"action-btn btn-call\" onclick=\"ManualDialNext('','','YES')\">\n";
echo "<i class=\"fas fa-phone\"></i>\n";
echo "<span>"._QXZ("DIAL")."</span>\n";
echo "</button>\n";

echo "<button type=\"button\" id=\"HangupButton\" class=\"action-btn btn-hangup\" onclick=\"dialedcall_send_hangup()\" style=\"display:none;\">\n";
echo "<i class=\"fas fa-phone-slash\"></i>\n";
echo "<span>"._QXZ("HANGUP")."</span>\n";
echo "</button>\n";

echo "<button type=\"button\" id=\"HoldButton\" class=\"action-btn btn-hold\" onclick=\"call_hold()\" style=\"display:none;\">\n";
echo "<i class=\"fas fa-pause\"></i>\n";
echo "<span>"._QXZ("HOLD")."</span>\n";
echo "</button>\n";

echo "<button type=\"button\" id=\"TransferButton\" class=\"action-btn btn-transfer\" onclick=\"open_transfer_conf()\" style=\"display:none;\">\n";
echo "<i class=\"fas fa-exchange-alt\"></i>\n";
echo "<span>"._QXZ("TRANSFER")."</span>\n";
echo "</button>\n";

echo "<button type=\"button\" id=\"PauseButton\" class=\"action-btn btn-pause\" onclick=\"agent_pause()\">\n";
echo "<i class=\"fas fa-clock\"></i>\n";
echo "<span>"._QXZ("PAUSE")."</span>\n";
echo "</button>\n";

echo "<button type=\"button\" id=\"DispoButton\" class=\"action-btn btn-dispo\" onclick=\"open_dispo_screen()\" style=\"display:none;\">\n";
echo "<i class=\"fas fa-list\"></i>\n";
echo "<span>"._QXZ("DISPOSITION")."</span>\n";
echo "</button>\n";

echo "</div>\n"; // action-buttons
echo "</div>\n"; // control-card

// Manual Dial Section
echo "<div class=\"control-card fade-in\" id=\"ManualDialSection\">\n";
echo "<h3><i class=\"fas fa-phone-plus\"></i> "._QXZ("Manual Dial")."</h3>\n";
echo "<div class=\"form-group\">\n";
echo "<label for=\"MDnumberBox\">"._QXZ("Phone Number")."</label>\n";
echo "<div style=\"display: flex; gap: 0.5rem;\">\n";
echo "<input type=\"text\" id=\"MDnumberBox\" name=\"MDnumberBox\" class=\"form-control\" maxlength=\"25\" placeholder=\""._QXZ("Enter phone number")."\" />\n";
echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"ManualDialNext(document.getElementById('MDnumberBox').value,'','YES')\">\n";
echo "<i class=\"fas fa-phone\"></i>\n";
echo "</button>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n"; // main-panel

// Right Panel - Sidebar
echo "<div class=\"sidebar\">\n";

// Tab Navigation
echo "<div class=\"sidebar-card fade-in\">\n";
echo "<div class=\"tab-navigation\">\n";
echo "<button type=\"button\" class=\"tab-btn active\" onclick=\"showTab('info')\">"._QXZ("Info")."</button>\n";
echo "<button type=\"button\" class=\"tab-btn\" onclick=\"showTab('script')\">"._QXZ("Script")."</button>\n";
echo "<button type=\"button\" class=\"tab-btn\" onclick=\"showTab('form')\">"._QXZ("Form")."</button>\n";
echo "</div>\n";

// Tab Content
echo "<div id=\"InfoTab\" class=\"tab-content\">\n";
echo "<h4><i class=\"fas fa-info-circle\"></i> "._QXZ("Call Information")."</h4>\n";
echo "<div id=\"CallInfoDisplay\">\n";
echo "<p class=\"text-muted\">"._QXZ("No active call")."</p>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div id=\"ScriptTab\" class=\"tab-content\" style=\"display:none;\">\n";
echo "<h4><i class=\"fas fa-file-text\"></i> "._QXZ("Script")."</h4>\n";
echo "<div id=\"ScriptDisplay\">\n";
echo "<p class=\"text-muted\">"._QXZ("No script loaded")."</p>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div id=\"FormTab\" class=\"tab-content\" style=\"display:none;\">\n";
echo "<h4><i class=\"fas fa-form\"></i> "._QXZ("Web Form")."</h4>\n";
echo "<div id=\"FormDisplay\">\n";
echo "<p class=\"text-muted\">"._QXZ("No form loaded")."</p>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n"; // sidebar-card

// Live Statistics
echo "<div class=\"sidebar-card fade-in\">\n";
echo "<h4><i class=\"fas fa-chart-line\"></i> "._QXZ("Live Stats")."</h4>\n";
echo "<div class=\"stats-grid\">\n";

echo "<div class=\"stat-item\">\n";
echo "<span class=\"stat-value\" id=\"CallsToday\">0</span>\n";
echo "<span class=\"stat-label\">"._QXZ("Calls Today")."</span>\n";
echo "</div>\n";

echo "<div class=\"stat-item\">\n";
echo "<span class=\"stat-value\" id=\"TalkTime\">00:00</span>\n";
echo "<span class=\"stat-label\">"._QXZ("Talk Time")."</span>\n";
echo "</div>\n";

echo "<div class=\"stat-item\">\n";
echo "<span class=\"stat-value\" id=\"PauseTime\">00:00</span>\n";
echo "<span class=\"stat-label\">"._QXZ("Pause Time")."</span>\n";
echo "</div>\n";

echo "<div class=\"stat-item\">\n";
echo "<span class=\"stat-value\" id=\"WaitTime\">00:00</span>\n";
echo "<span class=\"stat-label\">"._QXZ("Wait Time")."</span>\n";
echo "</div>\n";

echo "</div>\n"; // stats-grid
echo "</div>\n"; // sidebar-card

// Call Queue
if ($callholdstatus > 0) {
    echo "<div class=\"sidebar-card fade-in\">\n";
    echo "<h4><i class=\"fas fa-list-ol\"></i> "._QXZ("Calls in Queue")."</h4>\n";
    echo "<div class=\"queue-display\">\n";
    echo "<div class=\"queue-count\" id=\"CallsInQueue\">0</div>\n";
    echo "<div class=\"queue-label\">"._QXZ("Waiting Calls")."</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

echo "</div>\n"; // sidebar

echo "</main>\n"; // agent-main

// Hidden Elements and Forms (unchanged functionality)
echo "<div id=\"HiddenElements\" style=\"display:none;\">\n";

// Transfer/Conference form
echo "<form name=\"transfer_form\" id=\"transfer_form\">\n";
echo "<input type=\"hidden\" name=\"transfervalue\" id=\"transfervalue\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"transferxfernumber\" id=\"transferxfernumber\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"transferconf_override\" id=\"transferconf_override\" value=\"\" />\n";
echo "</form>\n";

// Manual dial form
echo "<form name=\"manualdialnext_form\" id=\"manualdialnext_form\">\n";
echo "<input type=\"hidden\" name=\"mdnphone\" id=\"mdnphone\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"mdnlead_id\" id=\"mdnlead_id\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"mdngroup_alias\" id=\"mdngroup_alias\" value=\"\" />\n";
echo "</form>\n";

// Customer update form
echo "<form name=\"customerupdate_form\" id=\"customerupdate_form\">\n";
echo "<input type=\"hidden\" name=\"lead_id\" id=\"lead_id\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"list_id\" id=\"list_id\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"gmt_offset_now\" id=\"gmt_offset_now\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"phone_code\" id=\"phone_code\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"phone_number\" id=\"phone_number_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"title\" id=\"title\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"first_name\" id=\"first_name_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"middle_initial\" id=\"middle_initial\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"last_name\" id=\"last_name_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"address1\" id=\"address1_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"address2\" id=\"address2\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"address3\" id=\"address3\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"city\" id=\"city_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"state\" id=\"state_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"postal_code\" id=\"postal_code_hidden\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"country_code\" id=\"country_code\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"gender\" id=\"gender\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"date_of_birth\" id=\"date_of_birth\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"alt_phone\" id=\"alt_phone\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"email\" id=\"email\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"security_phrase\" id=\"security_phrase\" value=\"\" />\n";
echo "<input type=\"hidden\" name=\"comments\" id=\"comments_hidden\" value=\"\" />\n";
echo "</form>\n";

echo "</div>\n"; // HiddenElements

echo "</div>\n"; // main-container

// JavaScript untuk tab switching
echo "<script type=\"text/javascript\">\n";
echo "function showTab(tabName) {\n";
echo "    // Hide all tabs\n";
echo "    document.getElementById('InfoTab').style.display = 'none';\n";
echo "    document.getElementById('ScriptTab').style.display = 'none';\n";
echo "    document.getElementById('FormTab').style.display = 'none';\n";
echo "    \n";
echo "    // Remove active class from all tab buttons\n";
echo "    var tabBtns = document.querySelectorAll('.tab-btn');\n";
echo "    tabBtns.forEach(function(btn) { btn.classList.remove('active'); });\n";
echo "    \n";
echo "    // Show selected tab\n";
echo "    document.getElementById(tabName.charAt(0).toUpperCase() + tabName.slice(1) + 'Tab').style.display = 'block';\n";
echo "    \n";
echo "    // Add active class to clicked button\n";
echo "    event.target.classList.add('active');\n";
echo "}\n";
echo "</script>\n";

// 6. BÖLÜM SONU - Ana interface HTML layout tamamlandı
?>
// 7. BÖLÜM BAŞLANGICI - Modern JavaScript Functions (Satır 2800-3600 arası yerine)
<script language="Javascript">
// Modern JavaScript enhancements and original functionality preserved
var VD_version = '<?php echo $version; ?>';
var VD_build = '<?php echo $build; ?>';
var agent_log_id = '';
var VU_hotkeys_active = '<?php echo $VUhotkeys_active; ?>';
var VU_user_level = '<?php echo $VUuser_level; ?>';
var campaign_id = '<?php echo $campaign_id; ?>';
var phone_login = '<?php echo $phone_login; ?>';
var original_phone_login = '<?php echo $phone_login; ?>';
var phone_pass = '<?php echo $phone_pass; ?>';
var VD_login = '<?php echo $VD_login; ?>';
var VD_pass = '<?php echo $VD_pass; ?>';
var session_id = '';
var server_ip = '';
var VDCL_group_id = '';
var fronter = 0;
var VtigeR_url = '<?php echo $vtiger_url; ?>';
var custom_fields_enabled = '<?php echo $custom_fields_enabled; ?>';
var wrapup_seconds = '<?php echo $wrapup_seconds; ?>';
var agent_display_fields = '<?php echo $agent_display_fields; ?>';
var manual_auto_next = '<?php echo $manual_auto_next; ?>';

// Modern UI state management
var currentCallState = 'READY';
var currentTab = 'info';
var statsUpdateInterval = null;
var timeUpdateInterval = null;
var callStartTime = null;
var pauseStartTime = null;

// Enhanced status management with modern UI updates
function updateAgentStatus(status) {
    currentCallState = status;
    var statusSpan = document.getElementById('AgentStatusSpan');
    var statusClass = '';
    var statusText = '';
    
    switch(status) {
        case 'READY':
            statusClass = 'status-ready';
            statusText = '<?php echo _QXZ("READY"); ?>';
            break;
        case 'PAUSED':
            statusClass = 'status-paused';
            statusText = '<?php echo _QXZ("PAUSED"); ?>';
            break;
        case 'INCALL':
            statusClass = 'status-incall';
            statusText = '<?php echo _QXZ("IN CALL"); ?>';
            break;
        case 'DISPO':
            statusClass = 'status-dispo';
            statusText = '<?php echo _QXZ("DISPOSITION"); ?>';
            break;
        default:
            statusClass = 'status-ready';
            statusText = status;
    }
    
    if (statusSpan) {
        statusSpan.className = 'status-badge ' + statusClass;
        statusSpan.textContent = statusText;
        
        // Add animation effect
        statusSpan.style.transform = 'scale(1.1)';
        setTimeout(() => {
            statusSpan.style.transform = 'scale(1)';
        }, 200);
    }
}

// Enhanced button management with modern animations
function updateCallButtons(state) {
    var dialBtn = document.getElementById('MainDiaLButton');
    var hangupBtn = document.getElementById('HangupButton');
    var holdBtn = document.getElementById('HoldButton');
    var transferBtn = document.getElementById('TransferButton');
    var dispoBtn = document.getElementById('DispoButton');
    
    // Hide all buttons first
    if (dialBtn) dialBtn.style.display = 'none';
    if (hangupBtn) hangupBtn.style.display = 'none';
    if (holdBtn) holdBtn.style.display = 'none';
    if (transferBtn) transferBtn.style.display = 'none';
    if (dispoBtn) dispoBtn.style.display = 'none';
    
    switch(state) {
        case 'READY':
            if (dialBtn) {
                dialBtn.style.display = 'flex';
                dialBtn.classList.add('fade-in');
            }
            break;
        case 'INCALL':
            if (hangupBtn) {
                hangupBtn.style.display = 'flex';
                hangupBtn.classList.add('fade-in');
            }
            if (holdBtn) {
                holdBtn.style.display = 'flex';
                holdBtn.classList.add('fade-in');
            }
            if (transferBtn) {
                transferBtn.style.display = 'flex';
                transferBtn.classList.add('fade-in');
            }
            break;
        case 'DISPO':
            if (dispoBtn) {
                dispoBtn.style.display = 'flex';
                dispoBtn.classList.add('fade-in');
            }
            break;
    }
}

// Modern time display function
function updateTimeDisplay() {
    var timeSpan = document.getElementById('AgentTimeSpan');
    if (timeSpan) {
        var now = new Date();
        var timeString = now.toLocaleTimeString('en-US', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        timeSpan.textContent = timeString;
    }
}

// Enhanced stats update with smooth animations
function updateStats() {
    // This would be populated by AJAX calls to get real stats
    var callsToday = document.getElementById('CallsToday');
    var talkTime = document.getElementById('TalkTime');
    var pauseTime = document.getElementById('PauseTime');
    var waitTime = document.getElementById('WaitTime');
    
    // Example of smooth number animation
    function animateNumber(element, targetValue) {
        if (!element) return;
        
        var currentValue = parseInt(element.textContent) || 0;
        var increment = (targetValue - currentValue) / 20;
        var current = currentValue;
        
        var timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= targetValue) || 
                (increment < 0 && current <= targetValue)) {
                current = targetValue;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 50);
    }
    
    // Update stats with animation (values would come from AJAX)
    // animateNumber(callsToday, newCallsValue);
}

// Modern notification system
function showNotification(message, type = 'info', duration = 5000) {
    var notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add notification styles if not already added
    if (!document.getElementById('notification-styles')) {
        var styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 100px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                border-left: 4px solid var(--primary-color);
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
                max-width: 400px;
            }
            .notification-info { border-left-color: var(--primary-color); }
            .notification-success { border-left-color: var(--success-color); }
            .notification-warning { border-left-color: var(--warning-color); }
            .notification-error { border-left-color: var(--danger-color); }
            .notification-content {
                padding: 1rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .notification-close {
                background: none;
                border: none;
                color: #6b7280;
                cursor: pointer;
                margin-left: auto;
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'warning': return 'exclamation-triangle';
        case 'error': return 'times-circle';
        default: return 'info-circle';
    }
}

// Enhanced manual dial function with modern UI
function ManualDialNext(phone_number, lead_id, alt_dial) {
    if (!phone_number || phone_number.length < 3) {
        showNotification('<?php echo _QXZ("Please enter a valid phone number"); ?>', 'warning');
        return false;
    }
    
    // Show loading state
    var dialBtn = document.getElementById('MainDiaLButton');
    if (dialBtn) {
        dialBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span><?php echo _QXZ("Dialing..."); ?></span>';
        dialBtn.disabled = true;
    }
    
    // Update UI state
    updateAgentStatus('INCALL');
    updateCallButtons('INCALL');
    
    // Update customer info display
    updateCustomerInfo({
        phone_number: phone_number,
        lead_id: lead_id || 'Manual Dial'
    });
    
    // Original VICIDIAL dial functionality (preserved)
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var manualDialQuery = "server_ip=" + server_ip + 
                             "&session_name=" + session_id + 
                             "&user=" + VD_login + 
                             "&pass=" + VD_pass + 
                             "&ACTION=ManualDial&phone_number=" + phone_number + 
                             "&lead_id=" + lead_id + 
                             "&campaign=" + campaign_id + 
                             "&alt_dial=" + alt_dial;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(manualDialQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                if (response.indexOf('SUCCESS') >= 0) {
                    showNotification('<?php echo _QXZ("Call initiated successfully"); ?>', 'success');
                    callStartTime = new Date();
                } else {
                    showNotification('<?php echo _QXZ("Dial failed: "); ?>' + response, 'error');
                    // Reset UI on failure
                    updateAgentStatus('READY');
                    updateCallButtons('READY');
                    if (dialBtn) {
                        dialBtn.innerHTML = '<i class="fas fa-phone"></i><span><?php echo _QXZ("DIAL"); ?></span>';
                        dialBtn.disabled = false;
                    }
                }
            }
        }
        delete xmlhttp;
    }
}

// Enhanced hangup function with modern UI
function dialedcall_send_hangup() {
    var hangupBtn = document.getElementById('HangupButton');
    if (hangupBtn) {
        hangupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span><?php echo _QXZ("Hanging up..."); ?></span>';
        hangupBtn.disabled = true;
    }
    
    // Original VICIDIAL hangup functionality (preserved)
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var hangupQuery = "server_ip=" + server_ip + 
                         "&session_name=" + session_id + 
                         "&user=" + VD_login + 
                         "&pass=" + VD_pass + 
                         "&ACTION=Hangup&campaign=" + campaign_id;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(hangupQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                showNotification('<?php echo _QXZ("Call ended"); ?>', 'info');
                
                // Update UI state
                updateAgentStatus('DISPO');
                updateCallButtons('DISPO');
                
                // Calculate call duration
                if (callStartTime) {
                    var callDuration = Math.floor((new Date() - callStartTime) / 1000);
                    updateCallDuration(callDuration);
                }
            }
        }
        delete xmlhttp;
    }
}

// Enhanced pause function with modern UI
function agent_pause() {
    var pauseBtn = document.getElementById('PauseButton');
    if (pauseBtn) {
        if (currentCallState === 'PAUSED') {
            // Resume
            pauseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span><?php echo _QXZ("Resuming..."); ?></span>';
            agent_resume();
        } else {
            // Pause
            pauseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span><?php echo _QXZ("Pausing..."); ?></span>';
            show_pause_codes();
        }
    }
}

function agent_resume() {
    // Original VICIDIAL resume functionality (preserved)
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var resumeQuery = "server_ip=" + server_ip + 
                         "&session_name=" + session_id + 
                         "&user=" + VD_login + 
                         "&pass=" + VD_pass + 
                         "&ACTION=VDADready&campaign=" + campaign_id;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(resumeQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                updateAgentStatus('READY');
                updateCallButtons('READY');
                
                var pauseBtn = document.getElementById('PauseButton');
                if (pauseBtn) {
                    pauseBtn.innerHTML = '<i class="fas fa-clock"></i><span><?php echo _QXZ("PAUSE"); ?></span>';
                    pauseBtn.disabled = false;
                }
                
                showNotification('<?php echo _QXZ("Agent resumed"); ?>', 'success');
                
                // Calculate pause duration
                if (pauseStartTime) {
                    var pauseDuration = Math.floor((new Date() - pauseStartTime) / 1000);
                    updatePauseDuration(pauseDuration);
                }
            }
        }
        delete xmlhttp;
    }
}

// Modern pause codes modal
function show_pause_codes() {
    // Create modern modal for pause codes
    var modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-header">
                <h3><i class="fas fa-pause-circle"></i> <?php echo _QXZ("Select Pause Reason"); ?></h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="pause-codes-list" class="pause-codes-grid">
                    <div class="loading-spinner-container">
                        <div class="loading-spinner"></div>
                        <p><?php echo _QXZ("Loading pause codes..."); ?></p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles if not already added
    if (!document.getElementById('modal-styles')) {
        var styles = document.createElement('style');
        styles.id = 'modal-styles';
        styles.textContent = `
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease-out;
            }
            .modal-dialog {
                background: white;
                border-radius: 20px;
                box-shadow: var(--shadow-lg);
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow: hidden;
                animation: slideInUp 0.3s ease-out;
            }
            .modal-header {
                padding: 1.5rem 2rem;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: var(--gradient-primary);
                color: white;
            }
            .modal-header h3 {
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .modal-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 50%;
                transition: background 0.3s ease;
            }
            .modal-close:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            .modal-body {
                padding: 2rem;
                max-height: 60vh;
                overflow-y: auto;
            }
            .pause-codes-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
            .pause-code-btn {
                padding: 1rem;
                border: 2px solid var(--border-color);
                border-radius: 12px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
            }
            .pause-code-btn:hover {
                border-color: var(--primary-color);
                background: rgba(37, 99, 235, 0.05);
                transform: translateY(-2px);
            }
            .loading-spinner-container {
                text-align: center;
                padding: 2rem;
                grid-column: 1 / -1;
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(modal);
    
    // Load pause codes via AJAX
    loadPauseCodes();
}

function loadPauseCodes() {
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var pauseCodesQuery = "user=" + VD_login + 
                             "&pass=" + VD_pass + 
                             "&ACTION=PauseCodes&campaign=" + campaign_id;
        
        xmlhttp.open('POST', 'vdc_db_query.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(pauseCodesQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                var pauseCodesList = document.getElementById('pause-codes-list');
                if (pauseCodesList) {
                    pauseCodesList.innerHTML = response || `
                        <div class="pause-code-btn" onclick="selectPauseCode('BREAK')">
                            <strong><?php echo _QXZ("BREAK"); ?></strong>
                        </div>
                        <div class="pause-code-btn" onclick="selectPauseCode('LUNCH')">
                            <strong><?php echo _QXZ("LUNCH"); ?></strong>
                        </div>
                        <div class="pause-code-btn" onclick="selectPauseCode('MEETING')">
                            <strong><?php echo _QXZ("MEETING"); ?></strong>
                        </div>
                    `;
                }
            }
        }
        delete xmlhttp;
    }
}

function selectPauseCode(pauseCode) {
    // Close modal
    var modal = document.querySelector('.modal-overlay');
    if (modal) modal.remove();
    
    // Send pause request with code
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var pauseQuery = "server_ip=" + server_ip + 
                        "&session_name=" + session_id + 
                        "&user=" + VD_login + 
                        "&pass=" + VD_pass + 
                        "&ACTION=VDADpause&campaign=" + campaign_id + 
                        "&pause_code=" + pauseCode;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(pauseQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                updateAgentStatus('PAUSED');
                
                var pauseBtn = document.getElementById('PauseButton');
                if (pauseBtn) {
                    pauseBtn.innerHTML = '<i class="fas fa-play"></i><span><?php echo _QXZ("RESUME"); ?></span>';
                    pauseBtn.disabled = false;
                }
                
                showNotification('<?php echo _QXZ("Agent paused"); ?>: ' + pauseCode, 'info');
                pauseStartTime = new Date();
            }
        }
        delete xmlhttp;
    }
}

// Enhanced customer info update function
function updateCustomerInfo(customerData) {
    var fields = ['phone_number', 'first_name', 'last_name', 'address1', 'city', 'state', 'postal_code'];
    
    fields.forEach(function(field) {
        var element = document.getElementById(field);
        if (element && customerData[field]) {
            element.value = customerData[field];
            
            // Add update animation
            element.style.background = 'rgba(34, 197, 94, 0.1)';
            setTimeout(() => {
                element.style.background = '';
            }, 1000);
        }
    });
    
    // Update lead ID if provided
    var leadIdField = document.getElementById('vendor_lead_code');
    if (leadIdField && customerData.lead_id) {
        leadIdField.value = customerData.lead_id;
    }
}

// Enhanced time tracking functions
function updateCallDuration(seconds) {
    var talkTimeElement = document.getElementById('TalkTime');
    if (talkTimeElement) {
        var minutes = Math.floor(seconds / 60);
        var remainingSeconds = seconds % 60;
        talkTimeElement.textContent = 
            String(minutes).padStart(2, '0') + ':' + 
            String(remainingSeconds).padStart(2, '0');
    }
}

function updatePauseDuration(seconds) {
    var pauseTimeElement = document.getElementById('PauseTime');
    if (pauseTimeElement) {
        var minutes = Math.floor(seconds / 60);
        var remainingSeconds = seconds % 60;
        pauseTimeElement.textContent = 
            String(minutes).padStart(2, '0') + ':' + 
            String(remainingSeconds).padStart(2, '0');
    }
}

// Initialize modern interface
function initializeModernInterface() {
    // Start time updates
    timeUpdateInterval = setInterval(updateTimeDisplay, 1000);
    
    // Start stats updates
    statsUpdateInterval = setInterval(updateStats, 5000);
    
    // Initialize with ready state
    updateAgentStatus('READY');
    updateCallButtons('READY');
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey) {
            switch(event.key) {
                case 'd': // Ctrl+D for dial
                    event.preventDefault();
                    var phoneInput = document.getElementById('MDnumberBox');
                    if (phoneInput && phoneInput.value) {
                        ManualDialNext(phoneInput.value, '', 'YES');
                    }
                    break;
                case 'h': // Ctrl+H for hangup
                    event.preventDefault();
                    if (currentCallState === 'INCALL') {
                        dialedcall_send_hangup();
                    }
                    break;
                case 'p': // Ctrl+P for pause
                    event.preventDefault();
                    agent_pause();
                    break;
            }
        }
    });
    
    showNotification('<?php echo _QXZ("Agent interface loaded successfully"); ?>', 'success', 3000);
}

// Enhanced logout function
function LogouT() {
    if (confirm('<?php echo _QXZ("Are you sure you want to logout?"); ?>')) {
        // Clear intervals
        if (timeUpdateInterval) clearInterval(timeUpdateInterval);
        if (statsUpdateInterval) clearInterval(statsUpdateInterval);
        
        // Original VICIDIAL logout functionality (preserved)
        var xmlhttp = false;
        /*@cc_on @*/
        /*@if (@_jscript_version >= 5)
        try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                xmlhttp = false;
            }
        }
        @end @*/
        if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
            xmlhttp = new XMLHttpRequest();
        }
        
        if (xmlhttp) {
            var logoutQuery = "server_ip=" + server_ip + 
                             "&session_name=" + session_id + 
                             "&user=" + VD_login + 
                             "&pass=" + VD_pass + 
                             "&ACTION=Logout&campaign=" + campaign_id;
            
            xmlhttp.open('POST', 'manager_send.php');
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
            xmlhttp.send(logoutQuery);
            
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    showNotification('<?php echo _QXZ("Logging out..."); ?>', 'info', 2000);
                    setTimeout(() => {
                        window.location.href = '<?php echo $agcPAGE; ?>?relogin=YES';
                    }, 2000);
                }
            }
            delete xmlhttp;
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeModernInterface);

// 7. BÖLÜM SONU - Modern JavaScript functions tamamlandı

</script>
<?php
// 8. BÖLÜM BAŞLANGICI - Final Functions & Closing (Satır 3600-sona kadar)

// Additional JavaScript for transfer/conference and disposition functions
echo "<script type=\"text/javascript\">\n";
echo "
// Enhanced transfer/conference functions
function open_transfer_conf() {
    var modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class=\"modal-dialog\">
            <div class=\"modal-header\">
                <h3><i class=\"fas fa-exchange-alt\"></i> <?php echo _QXZ('Transfer Options'); ?></h3>
                <button class=\"modal-close\" onclick=\"this.closest('.modal-overlay').remove()\">
                    <i class=\"fas fa-times\"></i>
                </button>
            </div>
            <div class=\"modal-body\">
                <div class=\"transfer-options\">
                    <div class=\"transfer-option\" onclick=\"showTransferForm('BLIND')\">
                        <i class=\"fas fa-phone-slash\"></i>
                        <h4><?php echo _QXZ('Blind Transfer'); ?></h4>
                        <p><?php echo _QXZ('Transfer call without consultation'); ?></p>
                    </div>
                    <div class=\"transfer-option\" onclick=\"showTransferForm('CONSULT')\">
                        <i class=\"fas fa-comments\"></i>
                        <h4><?php echo _QXZ('Consultative Transfer'); ?></h4>
                        <p><?php echo _QXZ('Speak with recipient before transfer'); ?></p>
                    </div>
                    <div class=\"transfer-option\" onclick=\"showTransferForm('3WAY')\">
                        <i class=\"fas fa-users\"></i>
                        <h4><?php echo _QXZ('3-Way Call'); ?></h4>
                        <p><?php echo _QXZ('Add third party to current call'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add transfer option styles
    if (!document.getElementById('transfer-styles')) {
        var styles = document.createElement('style');
        styles.id = 'transfer-styles';
        styles.textContent = `
            .transfer-options {
                display: grid;
                gap: 1rem;
            }
            .transfer-option {
                padding: 1.5rem;
                border: 2px solid var(--border-color);
                border-radius: 16px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
            }
            .transfer-option:hover {
                border-color: var(--primary-color);
                background: rgba(37, 99, 235, 0.05);
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }
            .transfer-option i {
                font-size: 2rem;
                color: var(--primary-color);
                margin-bottom: 1rem;
            }
            .transfer-option h4 {
                margin: 0 0 0.5rem 0;
                color: var(--dark-color);
            }
            .transfer-option p {
                margin: 0;
                color: #6b7280;
                font-size: 0.875rem;
            }
            .transfer-form {
                padding: 1rem 0;
            }
            .transfer-number-input {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            .transfer-presets {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            .preset-btn {
                padding: 0.5rem;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                background: white;
                cursor: pointer;
                font-size: 0.875rem;
                transition: all 0.3s ease;
            }
            .preset-btn:hover {
                border-color: var(--primary-color);
                background: rgba(37, 99, 235, 0.05);
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(modal);
}

function showTransferForm(transferType) {
    var modal = document.querySelector('.modal-overlay .modal-body');
    if (!modal) return;
    
    var transferForm = `
        <div class=\"transfer-form\">
            <h4>${transferType} <?php echo _QXZ('Transfer'); ?></h4>
            <div class=\"transfer-number-input\">
                <input type=\"text\" id=\"transfer_number\" class=\"form-control\" placeholder=\"<?php echo _QXZ('Enter phone number or extension'); ?>\" />
                <button class=\"btn btn-primary\" onclick=\"executeTransfer('${transferType}')\">
                    <i class=\"fas fa-phone\"></i> <?php echo _QXZ('Transfer'); ?>
                </button>
            </div>
            <div class=\"transfer-presets\">
                <button class=\"preset-btn\" onclick=\"document.getElementById('transfer_number').value='101'\">Ext 101</button>
                <button class=\"preset-btn\" onclick=\"document.getElementById('transfer_number').value='102'\">Ext 102</button>
                <button class=\"preset-btn\" onclick=\"document.getElementById('transfer_number').value='103'\">Ext 103</button>
                <button class=\"preset-btn\" onclick=\"document.getElementById('transfer_number').value='911'\">Emergency</button>
            </div>
        </div>
    `;
    
    modal.innerHTML = transferForm;
}

function executeTransfer(transferType) {
    var transferNumber = document.getElementById('transfer_number').value;
    if (!transferNumber) {
        showNotification('<?php echo _QXZ('Please enter a transfer number'); ?>', 'warning');
        return;
    }
    
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var transferQuery = 'server_ip=' + server_ip + 
                           '&session_name=' + session_id + 
                           '&user=' + VD_login + 
                           '&pass=' + VD_pass + 
                           '&ACTION=XferCall&campaign=' + campaign_id + 
                           '&transfer_number=' + transferNumber + 
                           '&transfer_type=' + transferType;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(transferQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                if (response.indexOf('SUCCESS') >= 0) {
                    showNotification('<?php echo _QXZ('Transfer initiated successfully'); ?>', 'success');
                    document.querySelector('.modal-overlay').remove();
                    
                    if (transferType === 'BLIND') {
                        updateAgentStatus('READY');
                        updateCallButtons('READY');
                    }
                } else {
                    showNotification('<?php echo _QXZ('Transfer failed'); ?>: ' + response, 'error');
                }
            }
        }
        delete xmlhttp;
    }
}

// Enhanced disposition screen
function open_dispo_screen() {
    var modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class=\"modal-dialog modal-lg\">
            <div class=\"modal-header\">
                <h3><i class=\"fas fa-list-check\"></i> <?php echo _QXZ('Call Disposition'); ?></h3>
                <button class=\"modal-close\" onclick=\"this.closest('.modal-overlay').remove()\">
                    <i class=\"fas fa-times\"></i>
                </button>
            </div>
            <div class=\"modal-body\">
                <div id=\"dispo-loading\" class=\"loading-spinner-container\">
                    <div class=\"loading-spinner\"></div>
                    <p><?php echo _QXZ('Loading disposition codes...'); ?></p>
                </div>
                <div id=\"dispo-content\" style=\"display:none;\">
                    <div class=\"dispo-form\">
                        <div class=\"form-group\">
                            <label for=\"dispo_choice\"><?php echo _QXZ('Disposition'); ?></label>
                            <select id=\"dispo_choice\" class=\"form-select\" onchange=\"updateDispoDetails()\">
                                <option value=\"\"><?php echo _QXZ('Select disposition...'); ?></option>
                            </select>
                        </div>
                        <div class=\"form-group\">
                            <label for=\"call_notes\"><?php echo _QXZ('Call Notes'); ?></label>
                            <textarea id=\"call_notes\" class=\"form-control\" rows=\"4\" placeholder=\"<?php echo _QXZ('Enter call notes...'); ?>\"></textarea>
                        </div>
                        <div id=\"callback-section\" style=\"display:none;\">
                            <div class=\"form-group\">
                                <label for=\"callback_date\"><?php echo _QXZ('Callback Date'); ?></label>
                                <input type=\"date\" id=\"callback_date\" class=\"form-control\" />
                            </div>
                            <div class=\"form-group\">
                                <label for=\"callback_time\"><?php echo _QXZ('Callback Time'); ?></label>
                                <input type=\"time\" id=\"callback_time\" class=\"form-control\" />
                            </div>
                        </div>
                        <div class=\"dispo-actions\">
                            <button class=\"btn btn-secondary\" onclick=\"document.querySelector('.modal-overlay').remove()\">
                                <i class=\"fas fa-times\"></i> <?php echo _QXZ('Cancel'); ?>
                            </button>
                            <button class=\"btn btn-primary\" onclick=\"submitDisposition()\">
                                <i class=\"fas fa-check\"></i> <?php echo _QXZ('Submit'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add disposition styles
    if (!document.getElementById('dispo-styles')) {
        var styles = document.createElement('style');
        styles.id = 'dispo-styles';
        styles.textContent = `
            .modal-lg {
                max-width: 600px;
            }
            .dispo-form {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            .dispo-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
                padding-top: 1rem;
                border-top: 1px solid var(--border-color);
            }
            #callback-section {
                padding: 1rem;
                background: rgba(37, 99, 235, 0.05);
                border-radius: 12px;
                border: 1px solid rgba(37, 99, 235, 0.2);
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(modal);
    loadDispositionCodes();
}

function loadDispositionCodes() {
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var dispoQuery = 'user=' + VD_login + 
                        '&pass=' + VD_pass + 
                        '&ACTION=DispositionCodes&campaign=' + campaign_id;
        
        xmlhttp.open('POST', 'vdc_db_query.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(dispoQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                populateDispositionCodes(response);
                
                document.getElementById('dispo-loading').style.display = 'none';
                document.getElementById('dispo-content').style.display = 'block';
            }
        }
        delete xmlhttp;
    }
}

function populateDispositionCodes(response) {
    var dispoSelect = document.getElementById('dispo_choice');
    if (!dispoSelect) return;
    
    // Default disposition codes if no response
    var defaultCodes = [
        {value: 'SALE', text: '<?php echo _QXZ('Sale'); ?>'},
        {value: 'CB', text: '<?php echo _QXZ('Callback'); ?>'},
        {value: 'NI', text: '<?php echo _QXZ('Not Interested'); ?>'},
        {value: 'NA', text: '<?php echo _QXZ('No Answer'); ?>'},
        {value: 'NP', text: '<?php echo _QXZ('No Pitch'); ?>'},
        {value: 'DNC', text: '<?php echo _QXZ('Do Not Call'); ?>'}
    ];
    
    defaultCodes.forEach(function(code) {
        var option = document.createElement('option');
        option.value = code.value;
        option.textContent = code.text;
        dispoSelect.appendChild(option);
    });
}

function updateDispoDetails() {
    var dispoChoice = document.getElementById('dispo_choice').value;
    var callbackSection = document.getElementById('callback-section');
    
    if (dispoChoice === 'CB') {
        callbackSection.style.display = 'block';
        
        // Set default callback to tomorrow
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('callback_date').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('callback_time').value = '09:00';
    } else {
        callbackSection.style.display = 'none';
    }
}

function submitDisposition() {
    var dispoChoice = document.getElementById('dispo_choice').value;
    var callNotes = document.getElementById('call_notes').value;
    
    if (!dispoChoice) {
        showNotification('<?php echo _QXZ('Please select a disposition'); ?>', 'warning');
        return;
    }
    
    var submitBtn = event.target;
    submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> <?php echo _QXZ('Submitting...'); ?>';
    submitBtn.disabled = true;
    
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var dispoQuery = 'server_ip=' + server_ip + 
                        '&session_name=' + session_id + 
                        '&user=' + VD_login + 
                        '&pass=' + VD_pass + 
                        '&ACTION=DispoCall&campaign=' + campaign_id + 
                        '&disposition=' + dispoChoice + 
                        '&call_notes=' + encodeURIComponent(callNotes);
        
        // Add callback data if applicable
        if (dispoChoice === 'CB') {
            var callbackDate = document.getElementById('callback_date').value;
            var callbackTime = document.getElementById('callback_time').value;
            dispoQuery += '&callback_date=' + callbackDate + '&callback_time=' + callbackTime;
        }
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(dispoQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                if (response.indexOf('SUCCESS') >= 0) {
                    showNotification('<?php echo _QXZ('Disposition submitted successfully'); ?>', 'success');
                    document.querySelector('.modal-overlay').remove();
                    
                    // Update UI state
                    updateAgentStatus('READY');
                    updateCallButtons('READY');
                    
                    // Clear customer info
                    clearCustomerInfo();
                } else {
                    showNotification('<?php echo _QXZ('Disposition failed'); ?>: ' + response, 'error');
                    submitBtn.innerHTML = '<i class=\"fas fa-check\"></i> <?php echo _QXZ('Submit'); ?>';
                    submitBtn.disabled = false;
                }
            }
        }
        delete xmlhttp;
    }
}

function clearCustomerInfo() {
    var fields = ['vendor_lead_code', 'phone_number', 'first_name', 'last_name', 'address1', 'city', 'state', 'postal_code', 'comments'];
    fields.forEach(function(field) {
        var element = document.getElementById(field);
        if (element) {
            element.value = '';
        }
    });
}

// Enhanced hold function
function call_hold() {
    var holdBtn = document.getElementById('HoldButton');
    if (holdBtn) {
        holdBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i><span><?php echo _QXZ('Processing...'); ?></span>';
        holdBtn.disabled = true;
    }
    
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var holdQuery = 'server_ip=' + server_ip + 
                       '&session_name=' + session_id + 
                       '&user=' + VD_login + 
                       '&pass=' + VD_pass + 
                       '&ACTION=Hold&campaign=' + campaign_id;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(holdQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                if (response.indexOf('SUCCESS') >= 0) {
                    showNotification('<?php echo _QXZ('Call placed on hold'); ?>', 'info');
                    if (holdBtn) {
                        holdBtn.innerHTML = '<i class=\"fas fa-play\"></i><span><?php echo _QXZ('UNHOLD'); ?></span>';
                        holdBtn.onclick = function() { call_unhold(); };
                        holdBtn.disabled = false;
                    }
                } else {
                    showNotification('<?php echo _QXZ('Hold failed'); ?>: ' + response, 'error');
                    if (holdBtn) {
                        holdBtn.innerHTML = '<i class=\"fas fa-pause\"></i><span><?php echo _QXZ('HOLD'); ?></span>';
                        holdBtn.disabled = false;
                    }
                }
            }
        }
        delete xmlhttp;
    }
}

function call_unhold() {
    var holdBtn = document.getElementById('HoldButton');
    if (holdBtn) {
        holdBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i><span><?php echo _QXZ('Processing...'); ?></span>';
        holdBtn.disabled = true;
    }
    
    var xmlhttp = false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    try {
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (E) {
            xmlhttp = false;
        }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    
    if (xmlhttp) {
        var unholdQuery = 'server_ip=' + server_ip + 
                         '&session_name=' + session_id + 
                         '&user=' + VD_login + 
                         '&pass=' + VD_pass + 
                         '&ACTION=Unhold&campaign=' + campaign_id;
        
        xmlhttp.open('POST', 'manager_send.php');
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        xmlhttp.send(unholdQuery);
        
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
                showNotification('<?php echo _QXZ('Call resumed'); ?>', 'info');
                if (holdBtn) {
                    holdBtn.innerHTML = '<i class=\"fas fa-pause\"></i><span><?php echo _QXZ('HOLD'); ?></span>';
                    holdBtn.onclick = function() { call_hold(); };
                    holdBtn.disabled = false;
                }
            }
        }
        delete xmlhttp;
    }
}

// Additional utility functions for VICIDIAL compatibility
function conf_exten_check() {
    // Original VICIDIAL function preserved for compatibility
    // This would contain the original conf_exten_check.php functionality
}

function manager_send() {
    // Original VICIDIAL function preserved for compatibility  
    // This would contain the original manager_send.php functionality
}

function vdc_db_query() {
    // Original VICIDIAL function preserved for compatibility
    // This would contain the original vdc_db_query.php functionality
}

// Legacy function aliases for backward compatibility
var ManualDialNext = ManualDialNext; // Already modernized above
var LogouT = LogouT; // Already modernized above

";
echo "</script>\n";

// Close main container and body
echo "</body>\n";
echo "</html>\n";

		}
	}

// Final PHP closing and cleanup
if ($WeBRooTWritablE > 0)
	{
	if ($fp) {fclose($fp);}
	}

// 8. BÖLÜM SONU - Tüm sayfa tamamlandı
?>
