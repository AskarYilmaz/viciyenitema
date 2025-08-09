<?php
# vicidial.php - the web-based version of the astVICIDIAL client application
# 
# Copyright (C) 2018  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2

$version = '2.14-565c';
$build = '180512-2226';
$mel=1;					# Mysql Error Log enabled = 1
$mysql_log_count=87;
$one_mysql_log=0;
$DB=0;

require_once("dbconnect_mysqli.php");
require_once("functions.php");

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

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VICIdial Agent Interface - Modern Design</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Modern CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: var(--gradient-bg);
            padding: 2rem;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.1;
        }

        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 1;
        }

        .logo-container i {
            font-size: 2.5rem;
            color: white;
        }

        .login-body {
            padding: 2.5rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating > .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--light-color);
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
            background: white;
        }

        .form-floating > label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--light-color);
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
            background: white;
        }

        .btn-login {
            background: var(--gradient-bg);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: var(--transition);
            width: 100%;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-refresh {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            color: white;
            transition: var(--transition);
            width: 100%;
        }

        .btn-refresh:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .utility-links {
            text-align: center;
            padding: 1.5rem;
            background: var(--light-color);
            border-top: 1px solid var(--border-color);
        }

        .utility-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin: 0 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .utility-links a:hover {
            color: var(--secondary-color);
            transform: translateY(-1px);
        }

        .version-info {
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            background: var(--light-color);
        }

        .input-group-text {
            background: var(--light-color);
            border: 2px solid var(--border-color);
            border-right: none;
            color: var(--text-secondary);
        }

        .form-control.with-icon {
            border-left: none;
            padding-left: 0.5rem;
        }

        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert-modern {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                border-radius: 15px;
            }
            
            .login-header, .login-body {
                padding: 1.5rem;
            }
            
            .utility-links a {
                display: block;
                margin: 0.5rem 0;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-card {
                background: rgba(31, 41, 55, 0.95);
                color: white;
            }
            
            .form-floating > .form-control,
            .form-select {
                background: rgba(55, 65, 81, 0.8);
                border-color: rgba(75, 85, 99, 0.6);
                color: white;
            }
            
            .form-floating > label {
                color: rgba(209, 213, 219, 0.8);
            }
        }
    </style>
';

echo "<!-- VERSION: $version     BUILD: $build -->\n";
echo "<!-- BROWSER: $BROWSER_WIDTH x $BROWSER_HEIGHT     $JS_browser_width x $JS_browser_height -->\n";
?>
<?php
// Campaign login list logic - değişmeyecek ama modern form ile entegre edilecek
if ($campaign_login_list > 0)
	{
    $camp_form_code  = "<select class=\"form-select\" name=\"VD_campaign\" id=\"VD_campaign\" onfocus=\"login_allowable_campaigns()\">\n";
	$camp_form_code .= "<option value=\"\">"._QXZ("Select Campaign")."</option>\n";

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
    $camp_form_code = "<input type=\"text\" class=\"form-control\" name=\"vd_campaign\" maxlength=\"20\" value=\"$VD_campaign\" />\n";
	}

// Modern JavaScript functions
if ($LogiNAJAX > 0)
	{
	?>

    <script type="text/javascript">
	// Modern JavaScript with enhanced functionality
	let BrowseWidth = 0;
	let BrowseHeight = 0;
	let isLoading = false;

	// Create floating particles for visual appeal
	function createFloatingParticles() {
		const container = document.createElement('div');
		container.className = 'floating-particles';
		document.body.appendChild(container);

		for (let i = 0; i < 15; i++) {
			const particle = document.createElement('div');
			particle.className = 'particle';
			particle.style.left = Math.random() * 100 + '%';
			particle.style.top = Math.random() * 100 + '%';
			particle.style.width = Math.random() * 8 + 4 + 'px';
			particle.style.height = particle.style.width;
			particle.style.animationDelay = Math.random() * 6 + 's';
			particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
			container.appendChild(particle);
		}
	}

	// Enhanced browser dimension detection
	function browser_dimensions() {
		<?php 
		if (preg_match('/MSIE/',$browser)) 
			{
			echo "		if (document.documentElement && document.documentElement.clientHeight) {\n";
			echo "			BrowseWidth = document.documentElement.clientWidth;\n";
			echo "			BrowseHeight = document.documentElement.clientHeight;\n";
			echo "		} else if (document.body) {\n";
			echo "			BrowseWidth = document.body.clientWidth;\n";
			echo "			BrowseHeight = document.body.clientHeight;\n";
			echo "		}\n";
			}
		else 
			{
			echo "		BrowseWidth = window.innerWidth;\n";
			echo "		BrowseHeight = window.innerHeight;\n";
			}
		?>

		if (document.vicidial_form) {
			document.vicidial_form.JS_browser_width.value = BrowseWidth;
			document.vicidial_form.JS_browser_height.value = BrowseHeight;
		}
	}

	// Enhanced campaign loading with modern UI
	function login_allowable_campaigns() {
		if (isLoading) return;
		
		const loginBtn = document.querySelector('.btn-login');
		const refreshBtn = document.querySelector('.btn-refresh');
		const spinner = document.querySelector('.loading-spinner');
		
		isLoading = true;
		if (spinner) spinner.style.display = 'inline-block';
		if (refreshBtn) refreshBtn.disabled = true;

		// Modern fetch API with better error handling
		const formData = new FormData();
		formData.append('user', document.vicidial_form.VD_login.value);
		formData.append('pass', document.vicidial_form.VD_pass.value);
		formData.append('ACTION', 'LogiNCamPaigns');
		formData.append('format', 'html');

		fetch('vdc_db_query.php', {
			method: 'POST',
			body: formData,
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => {
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			return response.text();
		})
		.then(data => {
			const campaignContainer = document.getElementById("LogiNCamPaigns");
			const resetContainer = document.getElementById("LogiNReseT");
			
			if (campaignContainer) {
				campaignContainer.innerHTML = data;
				// Add modern styling to the new select element
				const newSelect = campaignContainer.querySelector('select');
				if (newSelect) {
					newSelect.className = 'form-select';
				}
			}
			
			if (resetContainer) {
				resetContainer.innerHTML = '<button type="button" class="btn btn-refresh" onclick="login_allowable_campaigns()"><i class="fas fa-sync-alt me-2"></i>Refresh Campaign List</button>';
			}
			
			// Focus on campaign select with smooth animation
			const campaignSelect = document.getElementById("VD_campaign");
			if (campaignSelect) {
				setTimeout(() => {
					campaignSelect.focus();
					campaignSelect.style.transform = 'scale(1.02)';
					setTimeout(() => {
						campaignSelect.style.transform = 'scale(1)';
					}, 200);
				}, 300);
			}
		})
		.catch(error => {
			console.error('Error:', error);
			showAlert('Error loading campaigns. Please try again.', 'danger');
		})
		.finally(() => {
			isLoading = false;
			if (spinner) spinner.style.display = 'none';
			if (refreshBtn) refreshBtn.disabled = false;
		});
	}

	// Show modern alert messages
	function showAlert(message, type = 'info') {
		const alertHtml = `
			<div class="alert alert-${type} alert-modern alert-dismissible fade show" role="alert">
				<i class="fas fa-info-circle me-2"></i>
				${message}
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		`;
		
		const alertContainer = document.querySelector('.login-body');
		if (alertContainer) {
			alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
			
			// Auto-dismiss after 5 seconds
			setTimeout(() => {
				const alert = alertContainer.querySelector('.alert');
				if (alert) {
					const bsAlert = new bootstrap.Alert(alert);
					bsAlert.close();
				}
			}, 5000);
		}
	}

	// Form validation with modern UI feedback
	function validateForm() {
		const form = document.vicidial_form;
		const requiredFields = [
			{field: form.phone_login, name: 'Phone Login'},
			{field: form.phone_pass, name: 'Phone Password'},
			{field: form.VD_login, name: 'User Login'},
			{field: form.VD_pass, name: 'User Password'},
			{field: form.VD_campaign, name: 'Campaign'}
		];

		let isValid = true;
		let firstErrorField = null;

		requiredFields.forEach(({field, name}) => {
			if (field && field.value.trim() === '') {
				field.classList.add('is-invalid');
				if (!firstErrorField) firstErrorField = field;
				isValid = false;
			} else if (field) {
				field.classList.remove('is-invalid');
				field.classList.add('is-valid');
			}
		});

		if (!isValid && firstErrorField) {
			firstErrorField.focus();
			showAlert('Please fill in all required fields.', 'warning');
		}

		return isValid;
	}

	// Enhanced form submission with loading states
	function submitForm() {
		if (!validateForm()) return false;

		const submitBtn = document.querySelector('.btn-login');
		const spinner = submitBtn.querySelector('.loading-spinner');
		
		submitBtn.disabled = true;
		if (spinner) spinner.style.display = 'inline-block';
		submitBtn.innerHTML = '<div class="loading-spinner" style="display: inline-block;"></div>Logging in...';

		return true;
	}

	// Initialize when DOM is loaded
	document.addEventListener('DOMContentLoaded', function() {
		browser_dimensions();
		createFloatingParticles();

		// Add event listeners for real-time validation
		const form = document.vicidial_form;
		if (form) {
			form.addEventListener('submit', function(e) {
				if (!submitForm()) {
					e.preventDefault();
				}
			});

			// Real-time field validation
			const fields = form.querySelectorAll('input, select');
			fields.forEach(field => {
				field.addEventListener('blur', function() {
					if (this.value.trim() !== '') {
						this.classList.remove('is-invalid');
						this.classList.add('is-valid');
					}
				});

				field.addEventListener('input', function() {
					this.classList.remove('is-invalid');
				});
			});
		}

		// Smooth animations for form elements
		const formElements = document.querySelectorAll('.form-floating, .form-select');
		formElements.forEach((element, index) => {
			element.style.opacity = '0';
			element.style.transform = 'translateY(20px)';
			
			setTimeout(() => {
				element.style.transition = 'all 0.4s ease-out';
				element.style.opacity = '1';
				element.style.transform = 'translateY(0)';
			}, index * 100);
		});
	});

	// Window resize handler
	window.addEventListener('resize', browser_dimensions);

	// Prevent form submission on Enter key in text fields (except submit button)
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type === 'text') {
			const form = e.target.form;
			const inputs = Array.from(form.querySelectorAll('input, select'));
			const index = inputs.indexOf(e.target);
			
			if (index > -1 && index < inputs.length - 1) {
				e.preventDefault();
				inputs[index + 1].focus();
			}
		}
	});

	</script>

	<?php
	}
else
	{
	?>

    <script type="text/javascript">
	// Simplified version without AJAX
	function browser_dimensions() {
		// Basic browser dimension detection
		<?php 
		if (preg_match('/MSIE/',$browser)) 
			{
			echo "		if (document.documentElement && document.documentElement.clientHeight) {\n";
			echo "			BrowseWidth = document.documentElement.clientWidth;\n";
			echo "			BrowseHeight = document.documentElement.clientHeight;\n";
			echo "		} else if (document.body) {\n";
			echo "			BrowseWidth = document.body.clientWidth;\n";
			echo "			BrowseHeight = document.body.clientHeight;\n";
			echo "		}\n";
			}
		else 
			{
			echo "		BrowseWidth = window.innerWidth;\n";
			echo "		BrowseHeight = window.innerHeight;\n";
			}
		?>
	}

	document.addEventListener('DOMContentLoaded', function() {
		browser_dimensions();
	});
	</script>

	<?php
	}

// Utility links with modern styling
$grey_link='';
if ($link_to_grey_version > 0)
	{$grey_link = "<a href=\"./vicidial-grey.php?pl=$phone_login&pp=$phone_pass&VD_login=$VD_login&VD_pass=$VD_pass\"><i class=\"fas fa-palette me-1\"></i>"._QXZ("Old Agent Screen")."</a>";}
?>
<?php
// Modern Re-Login Form
if ($relogin == 'YES')
	{
	echo "<title>"._QXZ("Agent web client: Re-Login")."</title>\n";
	echo "</head>\n";
    echo "<body>\n";
	
	echo '<div class="login-container">
		<div class="login-card">
			<div class="login-header">
				<div class="logo-container">
					<i class="fas fa-headset"></i>
				</div>
				<h1><i class="fas fa-sync-alt me-2"></i>Re-Login Required</h1>
				<p>Please re-enter your credentials to continue</p>
			</div>
			
			<div class="login-body">
				<form name="vicidial_form" id="vicidial_form" action="'.$agcPAGE.'" method="post" novalidate>
					<input type="hidden" name="DB" id="DB" value="'.$DB.'" />
					<input type="hidden" name="JS_browser_height" id="JS_browser_height" value="" />
					<input type="hidden" name="JS_browser_width" id="JS_browser_width" value="" />
					<input type="hidden" name="admin_test" id="admin_test" value="'.$admin_test.'" />
					<input type="hidden" name="LOGINvarONE" id="LOGINvarONE" value="'.$LOGINvarONE.'" />
					<input type="hidden" name="LOGINvarTWO" id="LOGINvarTWO" value="'.$LOGINvarTWO.'" />
					<input type="hidden" name="LOGINvarTHREE" id="LOGINvarTHREE" value="'.$LOGINvarTHREE.'" />
					<input type="hidden" name="LOGINvarFOUR" id="LOGINvarFOUR" value="'.$LOGINvarFOUR.'" />
					<input type="hidden" name="LOGINvarFIVE" id="LOGINvarFIVE" value="'.$LOGINvarFIVE.'" />
					
					<div class="form-floating">
						<input type="text" class="form-control" id="phone_login" name="phone_login" placeholder="Phone Login" value="'.$phone_login.'" maxlength="20" required>
						<label for="phone_login"><i class="fas fa-phone me-2"></i>'._QXZ("Phone Login").'</label>
					</div>
					
					<div class="form-floating">
						<input type="password" class="form-control" id="phone_pass" name="phone_pass" placeholder="Phone Password" value="'.$phone_pass.'" maxlength="20" required>
						<label for="phone_pass"><i class="fas fa-lock me-2"></i>'._QXZ("Phone Password").'</label>
					</div>
					
					<div class="form-floating">
						<input type="text" class="form-control" id="VD_login" name="VD_login" placeholder="User Login" value="'.$VD_login.'" maxlength="20" required>
						<label for="VD_login"><i class="fas fa-user me-2"></i>'._QXZ("User Login").'</label>
					</div>
					
					<div class="form-floating">
						<input type="password" class="form-control" id="VD_pass" name="VD_pass" placeholder="User Password" value="'.$VD_pass.'" maxlength="20" required>
						<label for="VD_pass"><i class="fas fa-key me-2"></i>'._QXZ("User Password").'</label>
					</div>
					
					<div class="mb-3">
						<label for="VD_campaign" class="form-label"><i class="fas fa-bullhorn me-2"></i>'._QXZ("Campaign").'</label>
						<span id="LogiNCamPaigns">'.$camp_form_code.'</span>
					</div>
					
					<button type="submit" class="btn btn-login" name="SUBMIT">
						<div class="loading-spinner"></div>
						<i class="fas fa-sign-in-alt me-2"></i>'._QXZ("LOGIN").'
					</button>
					
					<div id="LogiNReseT">
						<button type="button" class="btn btn-refresh" onclick="login_allowable_campaigns()">
							<i class="fas fa-sync-alt me-2"></i>'._QXZ("Refresh Campaign List").'
						</button>
					</div>
				</form>
			</div>
			
			<div class="utility-links">';
			
	if ($hide_timeclock_link < 1) {
		echo '<a href="./timeclock.php?referrer=agent&pl='.$phone_login.'&pp='.$phone_pass.'&VD_login='.$VD_login.'&VD_pass='.$VD_pass.'">
				<i class="fas fa-clock"></i>'._QXZ("Timeclock").'
			</a>';
	}
	echo $grey_link;
	
	echo '</div>
			
			<div class="version-info">
				<i class="fas fa-info-circle me-1"></i>
				'._QXZ("VERSION").': '.$version.' &nbsp;•&nbsp; '._QXZ("BUILD").': '.$build.'
			</div>
		</div>
	</div>
	
	<!-- Bootstrap 5 JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	</body>
	</html>';
	exit;
	}

// Modern User Login First Form
if ($user_login_first == 1)
	{
	if ( (strlen($VD_login)<1) or (strlen($VD_pass)<1) or (strlen($VD_campaign)<1) )
		{
		echo "<title>"._QXZ("Agent web client: Campaign Login")."</title>\n";
		echo "</head>\n";
        echo "<body>\n";
		
		echo '<div class="login-container">
			<div class="login-card">
				<div class="login-header">
					<div class="logo-container">
						<i class="fas fa-user-shield"></i>
					</div>
					<h1><i class="fas fa-sign-in-alt me-2"></i>User Authentication</h1>
					<p>Enter your credentials to access campaigns</p>
				</div>
				
				<div class="login-body">
					<form name="vicidial_form" id="vicidial_form" action="'.$agcPAGE.'" method="post" novalidate>
						<input type="hidden" name="DB" value="'.$DB.'" />
						<input type="hidden" name="JS_browser_height" id="JS_browser_height" value="" />
						<input type="hidden" name="JS_browser_width" id="JS_browser_width" value="" />
						<input type="hidden" name="LOGINvarONE" id="LOGINvarONE" value="'.$LOGINvarONE.'" />
						<input type="hidden" name="LOGINvarTWO" id="LOGINvarTWO" value="'.$LOGINvarTWO.'" />
						<input type="hidden" name="LOGINvarTHREE" id="LOGINvarTHREE" value="'.$LOGINvarTHREE.'" />
						<input type="hidden" name="LOGINvarFOUR" id="LOGINvarFOUR" value="'.$LOGINvarFOUR.'" />
						<input type="hidden" name="LOGINvarFIVE" id="LOGINvarFIVE" value="'.$LOGINvarFIVE.'" />
						
						<div class="form-floating">
							<input type="text" class="form-control" id="VD_login" name="VD_login" placeholder="User Login" value="'.$VD_login.'" maxlength="20" required>
							<label for="VD_login"><i class="fas fa-user me-2"></i>'._QXZ("User Login").'</label>
						</div>
						
						<div class="form-floating">
							<input type="password" class="form-control" id="VD_pass" name="VD_pass" placeholder="User Password" value="'.$VD_pass.'" maxlength="20" required>
							<label for="VD_pass"><i class="fas fa-key me-2"></i>'._QXZ("User Password").'</label>
						</div>
						
						<div class="mb-3">
							<label for="VD_campaign" class="form-label"><i class="fas fa-bullhorn me-2"></i>'._QXZ("Campaign").'</label>
							<span id="LogiNCamPaigns">'.$camp_form_code.'</span>
						</div>
						
						<button type="submit" class="btn btn-login" name="SUBMIT">
							<div class="loading-spinner"></div>
							<i class="fas fa-arrow-right me-2"></i>'._QXZ("CONTINUE").'
						</button>
						
						<div id="LogiNReseT"></div>
					</form>
				</div>
				
				<div class="utility-links">';
				
		if ($hide_timeclock_link < 1) {
			echo '<a href="./timeclock.php?referrer=agent&pl='.$phone_login.'&pp='.$phone_pass.'&VD_login='.$VD_login.'&VD_pass='.$VD_pass.'">
					<i class="fas fa-clock"></i>'._QXZ("Timeclock").'
				</a>';
		}
		echo $grey_link;
		
		echo '</div>
				
				<div class="version-info">
					<i class="fas fa-info-circle me-1"></i>
					'._QXZ("VERSION").': '.$version.' &nbsp;•&nbsp; '._QXZ("BUILD").': '.$build.'
				</div>
			</div>
		</div>
		
		<!-- Bootstrap 5 JS -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		</body>
		</html>';
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
				echo "<title>"._QXZ("Agent web client: Login")."</title>\n";
				echo "</head>\n";
                echo "<body>\n";
				
				echo '<div class="login-container">
					<div class="login-card">
						<div class="login-header">
							<div class="logo-container">
								<i class="fas fa-phone"></i>
							</div>
							<h1><i class="fas fa-headset me-2"></i>Phone Setup Required</h1>
							<p>Complete your phone configuration</p>
						</div>
						
						<div class="login-body">
							<div class="alert alert-info alert-modern">
								<i class="fas fa-info-circle me-2"></i>
								Please configure your phone settings to continue.
							</div>
							
							<form name="vicidial_form" id="vicidial_form" action="'.$agcPAGE.'" method="post" novalidate>
								<input type="hidden" name="DB" value="'.$DB.'" />
								<input type="hidden" name="JS_browser_height" id="JS_browser_height" value="" />
								<input type="hidden" name="JS_browser_width" id="JS_browser_width" value="" />
								<input type="hidden" name="LOGINvarONE" id="LOGINvarONE" value="'.$LOGINvarONE.'" />
								<input type="hidden" name="LOGINvarTWO" id="LOGINvarTWO" value="'.$LOGINvarTWO.'" />
								<input type="hidden" name="LOGINvarTHREE" id="LOGINvarTHREE" value="'.$LOGINvarTHREE.'" />
								<input type="hidden" name="LOGINvarFOUR" id="LOGINvarFOUR" value="'.$LOGINvarFOUR.'" />
								<input type="hidden" name="LOGINvarFIVE" id="LOGINvarFIVE" value="'.$LOGINvarFIVE.'" />
								
								<div class="form-floating">
									<input type="text" class="form-control" id="phone_login" name="phone_login" placeholder="Phone Login" value="'.$phone_login.'" maxlength="20" required>
									<label for="phone_login"><i class="fas fa-phone me-2"></i>'._QXZ("Phone Login").'</label>
								</div>
								
								<div class="form-floating">
									<input type="password" class="form-control" id="phone_pass" name="phone_pass" placeholder="Phone Password" value="'.$phone_pass.'" maxlength="20" required>
									<label for="phone_pass"><i class="fas fa-lock me-2"></i>'._QXZ("Phone Password").'</label>
								</div>
								
								<div class="form-floating">
									<input type="text" class="form-control" id="VD_login" name="VD_login" placeholder="User Login" value="'.$VD_login.'" maxlength="20" readonly>
									<label for="VD_login"><i class="fas fa-user me-2"></i>'._QXZ("User Login").'</label>
								</div>
								
								<div class="form-floating">
									<input type="password" class="form-control" id="VD_pass" name="VD_pass" placeholder="User Password" value="'.$VD_pass.'" maxlength="20" readonly>
									<label for="VD_pass"><i class="fas fa-key me-2"></i>'._QXZ("User Password").'</label>
								</div>
								
								<div class="mb-3">
									<label for="VD_campaign" class="form-label"><i class="fas fa-bullhorn me-2"></i>'._QXZ("Campaign").'</label>
									<span id="LogiNCamPaigns">'.$camp_form_code.'</span>
								</div>
								
								<button type="submit" class="btn btn-login" name="SUBMIT">
									<div class="loading-spinner"></div>
									<i class="fas fa-play me-2"></i>'._QXZ("START SESSION").'
								</button>
								
								<div id="LogiNReseT"></div>
							</form>
						</div>
						
						<div class="utility-links">';
						
				if ($hide_timeclock_link < 1) {
					echo '<a href="./timeclock.php?referrer=agent&pl='.$phone_login.'&pp='.$phone_pass.'&VD_login='.$VD_login.'&VD_pass='.$VD_pass.'">
							<i class="fas fa-clock"></i>'._QXZ("Timeclock").'
						</a>';
				}
				echo $grey_link;
				
				echo '</div>
						
						<div class="version-info">
							<i class="fas fa-info-circle me-1"></i>
							'._QXZ("VERSION").': '.$version.' &nbsp;•&nbsp; '._QXZ("BUILD").': '.$build.'
						</div>
					</div>
				</div>
				
				<!-- Bootstrap 5 JS -->
				<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
				</body>
				</html>';
				exit;
				}
			}
		}
	}

// Main Phone Login Form
if ( (strlen($phone_login)<2) or (strlen($phone_pass)<2) )
	{
	echo "<title>"._QXZ("Agent web client: Phone Login")."</title>\n";
	echo "</head>\n";
    echo "<body>\n";
	
	echo '<div class="login-container">
		<div class="login-card">
			<div class="login-header">
				<div class="logo-container">
					<i class="fas fa-phone-alt"></i>
				</div>
				<h1><i class="fas fa-headset me-2"></i>VICIdial Agent</h1>
				<p>Enter your phone credentials to begin</p>
			</div>
			
			<div class="login-body">
				<form name="vicidial_form" id="vicidial_form" action="'.$agcPAGE.'" method="post" novalidate>
					<input type="hidden" name="DB" value="'.$DB.'" />
					<input type="hidden" name="JS_browser_height" id="JS_browser_height" value="" />
					<input type="hidden" name="JS_browser_width" id="JS_browser_width" value="" />
					<input type="hidden" name="LOGINvarONE" id="LOGINvarONE" value="'.$LOGINvarONE.'" />
					<input type="hidden" name="LOGINvarTWO" id="LOGINvarTWO" value="'.$LOGINvarTWO.'" />
					<input type="hidden" name="LOGINvarTHREE" id="LOGINvarTHREE" value="'.$LOGINvarTHREE.'" />
					<input type="hidden" name="LOGINvarFOUR" id="LOGINvarFOUR" value="'.$LOGINvarFOUR.'" />
					<input type="hidden" name="LOGINvarFIVE" id="LOGINvarFIVE" value="'.$LOGINvarFIVE.'" />
					
					<div class="form-floating">
						<input type="text" class="form-control" id="phone_login" name="phone_login" placeholder="Phone Login" value="" maxlength="20" required autofocus>
						<label for="phone_login"><i class="fas fa-phone me-2"></i>'._QXZ("Phone Login").'</label>
					</div>
					
					<div class="form-floating">
						<input type="password" class="form-control" id="phone_pass" name="phone_pass" placeholder="Phone Password" value="" maxlength="20" required>
						<label for="phone_pass"><i class="fas fa-lock me-2"></i>'._QXZ("Phone Password").'</label>
					</div>
					
					<button type="submit" class="btn btn-login" name="SUBMIT">
						<div class="loading-spinner"></div>
						<i class="fas fa-arrow-right me-2"></i>'._QXZ("CONTINUE").'
					</button>
					
					<div id="LogiNReseT"></div>
				</form>
			</div>
			
			<div class="utility-links">';
			
	if ($hide_timeclock_link < 1) {
		echo '<a href="./timeclock.php?referrer=agent&pl='.$phone_login.'&pp='.$phone_pass.'&VD_login='.$VD_login.'&VD_pass='.$VD_pass.'">
				<i class="fas fa-clock"></i>'._QXZ("Timeclock").'
			</a>';
	}
	echo $grey_link;
	
	echo '</div>
			
			<div class="version-info">
				<i class="fas fa-info-circle me-1"></i>
				'._QXZ("VERSION").': '.$version.' &nbsp;•&nbsp; '._QXZ("BUILD").': '.$build.'
			</div>
		</div>
	</div>
	
	<!-- Bootstrap 5 JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	</body>
	</html>';
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
// Bu kısımdan itibaren authentication sonrası işlemlere devam ediliyor
			// Burada VDloginDISPLAY=1 durumu için modern login formu gösterilecek
			<?php
// Bu kodu ice_agent.php dosyanızda şu satırdan sonra ekleyin:
// $row=mysqli_fetch_row($rslt);
// // Bu kısımdan itibaren authentication sonrası işlemlere devam ediliyor

			// KULLANICI BİLGİLERİNİ TAMAMLAYALIM
			$VU_full_name =					$row[0];
			$VU_user_level =				$row[1];
			$VU_hotkeys_active =			$row[2];
			$VU_agent_choose_ingroups =		$row[3];
			$VU_scheduled_callbacks =		$row[4];
			$VU_agentonly_callbacks =		$row[5];
			$VU_agentcall_manual =			$row[6];
			$VU_vicidial_recording =		$row[7];
			$VU_vicidial_transfers =		$row[8];
			$VU_closer_default_blended =	$row[9];
			$VU_user_group =				$row[10];
			$VU_vicidial_recording_override = $row[11];
			$VU_alter_custphone_override =	$row[12];
			$VU_alert_enabled =				$row[13];
			$VU_agent_shift_enforcement_override = $row[14];
			$VU_shift_override_flag =		$row[15];
			$VU_allow_alerts =				$row[16];
			$VU_closer_campaigns =			$row[17];
			$VU_agent_choose_territories =	$row[18];
			$VU_custom_one =				$row[19];
			$VU_custom_two =				$row[20];
			$VU_custom_three =				$row[21];
			$VU_custom_four =				$row[22];
			$VU_custom_five =				$row[23];
			$VU_agent_call_log_view_override = $row[24];
			$VU_agent_choose_blended =		$row[25];
			$VU_agent_lead_search_override = $row[26];
			$VU_preset_contact_search =		$row[27];
			$VU_max_inbound_calls =			$row[28];
			$VU_wrapup_seconds_override =	$row[29];
			$VU_email =						$row[30];
			$VU_user_choose_language =		$row[31];
			$VU_ready_max_logout =			$row[32];

			if ($VU_user_choose_language == 'Y')
				{
				if (strlen($VD_language) > 0)
					{
					$VUselected_language = $VD_language;
					}
				}

			if ( ($VU_user_level < 1) or ($VU_user_level > 9) )
				{
				$VDloginDISPLAY=1;
				echo "<!-- invalid user level: $VU_user_level -->\n";
				}
			else
				{
				$VDloginDISPLAY=0;

				### GET LANGUAGE TEXT ###
				if (strlen($VUselected_language) > 0)
					{
					if (file_exists("lang/languages.php"))
						{
						require_once("lang/languages.php");
						}
					}

				##### BEGIN AGENT SESSION SETUP #####
				
				### Phone and session setup ###
				if(strlen($phone_login) > 0)
					{
					$phone_login = preg_replace("/\'|\"|\\\\|;/","",$phone_login);
					$phone_pass = preg_replace("/\'|\"|\\\\|;/","",$phone_pass);
					}

				$session_name = "$VD_login$random";
				$web_vars='';
				$conf_exten = '';
				$extension = '';
				$voicemail_dump_exten = '85026666666666';
				
				### Server and timing variables ###
				$server_ip = $_SERVER['SERVER_ADDR'];
				if (strlen($server_ip) < 1)
					{$server_ip = '127.0.0.1';}
				
				### Set campaign variables ###
				$stmt = "SELECT closer_campaigns,campaign_cid,campaign_vdad_exten,campaign_rec_exten,campaign_recording,campaign_rec_filename,campaign_script,get_call_launch,xferconf_a_dtmf,xferconf_a_number,xferconf_b_dtmf,xferconf_b_number,default_xfer_group,voicemail_ext,agent_pause_codes_active,manual_dial_prefix,campaign_login_date,web_form_address,web_form_address_two,agent_lead_search,external_igb_set_user,web_form_target,vtiger_screen_login,campaign_allow_inbound,manual_dial_search_filter,default_ingroup_cid,web_form_address_three,timer_action,timer_action_message,timer_action_seconds,start_call_url,dispo_call_url,xferconf_c_number,xferconf_d_number,xferconf_e_number,use_custom_cid,scheduled_callbacks_alert,scheduled_callbacks_count,callmenu_qualify_enabled,campaign_script_two,browser_alert_sound,browser_alert_volume,user_group_two,user_group_three,xferconf_f_number,xferconf_g_number,xferconf_h_number,xferconf_i_number,xferconf_j_number from vicidial_campaigns where campaign_id='$VD_campaign'";
				$rslt=mysql_to_mysqli($stmt, $link);
					if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01011',$VD_login,$server_ip,$session_name,$one_mysql_log);}
				$row=mysqli_fetch_row($rslt);
				
				$campaign_cid =							$row[1];
				$campaign_vdad_exten =					$row[2];
				$campaign_rec_exten =					$row[3];
				$campaign_recording =					$row[4];
				$campaign_rec_filename =				$row[5];
				$campaign_script =						$row[6];
				$get_call_launch =						$row[7];
				$xferconf_a_dtmf =						$row[8];
				$xferconf_a_number =					$row[9];
				$xferconf_b_dtmf =						$row[10];
				$xferconf_b_number =					$row[11];
				$default_xfer_group =					$row[12];
				$voicemail_ext =						$row[13];
				$agent_pause_codes_active =				$row[14];
				$manual_dial_prefix =					$row[15];
				$campaign_login_date =					$row[16];
				$web_form_address =						$row[17];
				$web_form_address_two =					$row[18];
				$agent_lead_search =					$row[19];
				$external_igb_set_user =				$row[20];
				$web_form_target =						$row[21];
				$vtiger_screen_login =					$row[22];
				$campaign_allow_inbound =				$row[23];
				$manual_dial_search_filter =			$row[24];
				$default_ingroup_cid =					$row[25];
				$web_form_address_three =				$row[26];
				$timer_action =							$row[27];
				$timer_action_message =					$row[28];
				$timer_action_seconds =					$row[29];
				$start_call_url =						$row[30];
				$dispo_call_url =						$row[31];
				$xferconf_c_number =					$row[32];
				$xferconf_d_number =					$row[33];
				$xferconf_e_number =					$row[34];
				$use_custom_cid =						$row[35];
				$scheduled_callbacks_alert =			$row[36];
				$scheduled_callbacks_count =			$row[37];
				$callmenu_qualify_enabled =				$row[38];
				$campaign_script_two =					$row[39];
				$browser_alert_sound =					$row[40];
				$browser_alert_volume =					$row[41];
				$user_group_two =						$row[42];
				$user_group_three =						$row[43];
				$xferconf_f_number =					$row[44];
				$xferconf_g_number =					$row[45];
				$xferconf_h_number =					$row[46];
				$xferconf_i_number =					$row[47];
				$xferconf_j_number =					$row[48];

				##### AUTHENTICATION COMPLETE - START MAIN INTERFACE #####
				
				echo "<title>"._QXZ("Agent web client")."</title>\n";
				
				### INSERT HEAD SCRIPTS ###
				echo $INSERT_head_script;
				
				echo "
				<script language=\"Javascript\">
				
				// VICIdial Agent Variables
				var active = 'N';
				var fronter = 0;
				var VD_live_customer_call = 0;
				var CalL_ScripT_id = '';
				var CalL_ScripT_color = '';
				var VDCL_group_id = '';
				var campaign = '$VD_campaign';
				var phone_login = '$phone_login';
				var phone_pass = '$phone_pass';
				var original_phone_login = '$phone_login';
				var conf_exten = '$conf_exten';
				var user = '$VD_login';
				var pass = '$VD_pass';
				var full_name = '$VU_full_name';
				var hotkeys_active = '$VU_hotkeys_active';
				var voicemail_dump_exten = '$voicemail_dump_exten';
				var ext_context = '$ext_context';
				var web_vars = '$web_vars';
				var session_id = '$session_name';
				var agcDIR = '$agcDIR';
				var agentchannel = '';
				var lastcustchannel = '';
				var lastcustserverip = '';
				var recording_filename = '';
				var recording_id = '';
				var customer_sec = 0;
				var agent_log_id = '';
				var MDnextCID = '';
				var LaSTCID = '';
				var phone_ip = '';
				var original_phone_ip = '';
				var phone_type = 'SIP';
				var webphone_location = '$webphone_location';
				var agentwebsite = '';
				
				$INSERT_head_js
				
				// Modern Agent Interface Functions
				function agent_main_login_screen() {
					// Initialize modern agent interface
					console.log('Agent interface initializing...');
					setTimeout(() => {
						document.getElementById('initial-loading').style.display = 'none';
						document.getElementById('main-interface').style.display = 'block';
					}, 3000);
				}
				
				function fast_logout() {
					// Logout functionality
					console.log('Logging out...');
				}
				
				function browser_dimensions() {
					// Handle browser resize
				}
				
				</script>
				";
				
				echo "</head>\n";
				
				### MAIN BODY STARTS ###
				echo "<body onload=\"agent_main_login_screen();\" onunload=\"fast_logout();\" onresize=\"browser_dimensions();\">\n";
				
				// Modern loading screen
				echo '<div id="initial-loading" class="login-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: var(--gradient-bg);">
					<div class="login-card" style="max-width: 400px;">
						<div class="login-header">
							<div class="logo-container">
								<i class="fas fa-headset"></i>
							</div>
							<h1><i class="fas fa-rocket me-2"></i>Loading Interface</h1>
							<p>Initializing your agent workspace...</p>
						</div>
						<div class="login-body text-center">
							<div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
								<span class="visually-hidden">Loading...</span>
							</div>
							<div class="progress mb-3" style="height: 8px;">
								<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="loading-progress"></div>
							</div>
							<p class="text-muted" id="loading-status">Authenticating user...</p>
						</div>
					</div>
				</div>';
				
				### MAIN AGENT INTERFACE WITH MODERN BOOTSTRAP DESIGN ###
				echo '<div id="main-interface" style="display: none;" class="container-fluid p-0">
					<!-- Top Navigation Bar -->
					<nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--gradient-bg);">
						<div class="container-fluid">
							<a class="navbar-brand" href="#">
								<i class="fas fa-headset me-2"></i>VICIdial Agent
							</a>
							
							<div class="navbar-nav ms-auto">
								<div class="nav-item dropdown">
									<a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
										<i class="fas fa-user-circle me-2"></i>'.$VU_full_name.'
									</a>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
										<li><a class="dropdown-item" href="#"><i class="fas fa-clock me-2"></i>Timeclock</a></li>
										<li><hr class="dropdown-divider"></li>
										<li><a class="dropdown-item" href="#" onclick="fast_logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
									</ul>
								</div>
							</div>
						</div>
					</nav>
					
					<!-- Main Content Area -->
					<div class="row g-0">
						<!-- Left Sidebar - Agent Controls -->
						<div class="col-md-3 bg-light border-end">
							<div class="p-3">
								<h5 class="mb-3"><i class="fas fa-tachometer-alt me-2"></i>Agent Dashboard</h5>
								
								<!-- Agent Status -->
								<div class="card mb-3">
									<div class="card-header bg-primary text-white">
										<i class="fas fa-circle me-2"></i>Status
									</div>
									<div class="card-body">
										<div class="d-flex align-items-center mb-2">
											<span class="badge bg-success me-2">READY</span>
											<small class="text-muted">Agent: '.$VD_login.'</small>
										</div>
										<div class="d-flex align-items-center mb-2">
											<i class="fas fa-bullhorn me-2 text-info"></i>
											<small>Campaign: '.$VD_campaign.'</small>
										</div>
										<div class="d-flex align-items-center">
											<i class="fas fa-phone me-2 text-secondary"></i>
											<small>Extension: '.$phone_login.'</small>
										</div>
									</div>
								</div>
								
								<!-- Call Controls -->
								<div class="card mb-3">
									<div class="card-header">
										<i class="fas fa-phone-alt me-2"></i>Call Controls
									</div>
									<div class="card-body">
										<div class="d-grid gap-2">
											<button class="btn btn-success" id="dial-btn">
												<i class="fas fa-phone me-2"></i>Dial
											</button>
											<button class="btn btn-danger" id="hangup-btn">
												<i class="fas fa-phone-slash me-2"></i>Hangup
											</button>
											<button class="btn btn-warning" id="pause-btn">
												<i class="fas fa-pause me-2"></i>Pause
											</button>
											<button class="btn btn-info" id="resume-btn">
												<i class="fas fa-play me-2"></i>Resume
											</button>
										</div>
									</div>
								</div>
								
								<!-- Quick Actions -->
								<div class="card mb-3">
									<div class="card-header">
										<i class="fas fa-bolt me-2"></i>Quick Actions
									</div>
									<div class="card-body">
										<div class="d-grid gap-2">
											<button class="btn btn-outline-primary btn-sm">
												<i class="fas fa-search me-2"></i>Lead Search
											</button>
											<button class="btn btn-outline-secondary btn-sm">
												<i class="fas fa-calendar me-2"></i>Callbacks
											</button>
											<button class="btn btn-outline-info btn-sm">
												<i class="fas fa-file-alt me-2"></i>Manual Dial
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<!-- Main Work Area -->
						<div class="col-md-9">
							<div class="p-3">
								<!-- Customer Information -->
								<div class="card mb-3">
									<div class="card-header d-flex justify-content-between align-items-center">
										<span><i class="fas fa-user me-2"></i>Customer Information</span>
										<span class="badge bg-primary" id="call-timer">00:00</span>
									</div>
									<div class="card-body">
										<div class="row">
											<div class="col-md-6">
												<div class="mb-3">
													<label class="form-label">Customer Name</label>
													<input type="text" class="form-control" id="customer-name" value="No Customer">
												</div>
												<div class="mb-3">
													<label class="form-label">Phone Number</label>
													<input type="text" class="form-control" id="customer-phone" value="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="mb-3">
													<label class="form-label">Lead ID</label>
													<input type="text" class="form-control" id="lead-id" value="">
												</div>
												<div class="mb-3">
													<label class="form-label">Call Status</label>
													<input type="text" class="form-control" id="call-status" value="Ready">
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Tabbed Content Area -->
								<div class="card">
									<div class="card-header">
										<ul class="nav nav-tabs card-header-tabs" id="mainTabs" role="tablist">
											<li class="nav-item" role="presentation">
												<button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-content" type="button" role="tab">
													<i class="fas fa-home me-2"></i>Main
												</button>
											</li>
											<li class="nav-item" role="presentation">
												<button class="nav-link" id="script-tab" data-bs-toggle="tab" data-bs-target="#script-content" type="button" role="tab">
													<i class="fas fa-file-text me-2"></i>Script
												</button>
											</li>
											<li class="nav-item" role="presentation">
												<button class="nav-link" id="form-tab" data-bs-toggle="tab" data-bs-target="#form-content" type="button" role="tab">
													<i class="fas fa-wpforms me-2"></i>Form
												</button>
											</li>
											<li class="nav-item" role="presentation">
												<button class="nav-link" id="disposition-tab" data-bs-toggle="tab" data-bs-target="#disposition-content" type="button" role="tab">
													<i class="fas fa-check-circle me-2"></i>Disposition
												</button>
											</li>
										</ul>
									</div>
									<div class="card-body">
										<div class="tab-content" id="mainTabContent">
											<!-- Main Tab -->
											<div class="tab-pane fade show active" id="main-content" role="tabpanel">
												<div class="row">
													<div class="col-12">
														<div class="alert alert-success">
															<h5><i class="fas fa-rocket me-2"></i>Welcome to VICIdial Modern Agent Interface!</h5>
															<p class="mb-0">Your workspace is ready. Use the controls on the left to manage calls and the tabs above to access scripts, forms, and dispositions.</p>
														</div>
													</div>
												</div>
												
												<!-- Real-time Stats -->
												<div class="row">
													<div class="col-md-3">
														<div class="card bg-primary text-white">
															<div class="card-body text-center">
																<i class="fas fa-phone fa-2x mb-2"></i>
																<h5>0</h5>
																<small>Calls Today</small>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="card bg-success text-white">
															<div class="card-body text-center">
																<i class="fas fa-clock fa-2x mb-2"></i>
																<h5>00:00:00</h5>
																<small>Talk Time</small>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="card bg-warning text-white">
															<div class="card-body text-center">
																<i class="fas fa-pause fa-2x mb-2"></i>
																<h5>00:00:00</h5>
																<small>Pause Time</small>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="card bg-info text-white">
															<div class="card-body text-center">
																<i class="fas fa-chart-line fa-2x mb-2"></i>
																<h5>0%</h5>
																<small>Efficiency</small>
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<!-- Script Tab -->
											<div class="tab-pane fade" id="script-content" role="tabpanel">
												<div class="alert alert-info">
													<i class="fas fa-file-text me-2"></i>Campaign script will be loaded here when a call is active.
												</div>
												<div id="script-display" class="border p-3 bg-light" style="min-height: 300px;">
													<p class="text-muted text-center">No script loaded</p>
												</div>
											</div>
											
											<!-- Form Tab -->
											<div class="tab-pane fade" id="form-content" role="tabpanel">
												<div class="alert alert-info">
													<i class="fas fa-wpforms me-2"></i>Lead information form will appear here.
												</div>
												<div id="form-display" style="min-height: 300px;">
													<p class="text-muted text-center">No form loaded</p>
												</div>
											</div>
											
											<!-- Disposition Tab -->
											<div class="tab-pane fade" id="disposition-content" role="tabpanel">
												<div class="alert alert-warning">
													<i class="fas fa-check-circle me-2"></i>Call disposition options will appear here after a call.
												</div>
												<div id="disposition-display" style="min-height: 300px;">
													<p class="text-muted text-center">No dispositions available</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>';
				
				### JAVASCRIPT FOR MAIN INTERFACE ###
				echo '
				<script>
				// Modern loading animation
				let progress = 0;
				const progressBar = document.getElementById("loading-progress");
				const statusText = document.getElementById("loading-status");
				const loadingSteps = [
					"Authenticating user...",
					"Loading campaign settings...", 
					"Initializing phone connection...",
					"Setting up interface...",
					"Almost ready..."
				];
				
				const loadingInterval = setInterval(() => {
					progress += Math.random() * 15 + 5;
					if (progress > 100) progress = 100;
					
					progressBar.style.width = progress + "%";
					
					const stepIndex = Math.floor((progress / 100) * (loadingSteps.length - 1));
					statusText.textContent = loadingSteps[stepIndex];
					
					if (progress >= 100) {
						clearInterval(loadingInterval);
						setTimeout(() => {
							document.getElementById("initial-loading").style.display = "none";
							document.getElementById("main-interface").style.display = "block";
						}, 1000);
					}
				}, 300);
				
				// Initialize call timer
				let callSeconds = 0;
				function updateCallTimer() {
					const timer = document.getElementById("call-timer");
					if (timer) {
						const minutes = Math.floor(callSeconds / 60);
						const seconds = callSeconds % 60;
						timer.textContent = String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0");
					}
				}
				
				// Add event listeners for modern controls
				document.addEventListener("DOMContentLoaded", function() {
					// Call control buttons
					const dialBtn = document.getElementById("dial-btn");
					const hangupBtn = document.getElementById("hangup-btn");
					const pauseBtn = document.getElementById("pause-btn");
					const resumeBtn = document.getElementById("resume-btn");
					
					if (dialBtn) {
						dialBtn.addEventListener("click", function() {
							alert("Dial function will be implemented with VICIdial API");
						});
					}
					
					if (hangupBtn) {
						hangupBtn.addEventListener("click", function() {
							alert("Hangup function will be implemented with VICIdial API");
						});
					}
					
					if (pauseBtn) {
						pauseBtn.addEventListener("click", function() {
							alert("Pause function will be implemented with VICIdial API");
						});
					}
					
					if (resumeBtn) {
						resumeBtn.addEventListener("click", function() {
							alert("Resume function will be implemented with VICIdial API");
						});
					}
					
					// Update timer every second
					setInterval(updateCallTimer, 1000);
				});
				
				</script>';
				
				### BOOTSTRAP AND CLOSING TAGS ###
				echo '
				<!-- Bootstrap 5 JS -->
				<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
				
				</body>
				</html>';
				
				##### END MAIN INTERFACE #####
				}
?>
