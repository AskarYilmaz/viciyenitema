<?php
# Enhanced QC.php - VICIdial QC Panel with ice_agent.php referanslı WebPhone
# Session Yönetimi ve ice_agent.php'deki gibi WebPhone entegrasyonu - KISIM 1

$version = '2.0-004';
$build = '241221-0100';

require_once("../agc/dbconnect_mysqli.php");
require_once("../agc/functions.php");

// SESSION YÖNETİMİ
session_start();

// Değişkenleri tanımla
$VD_login = '';
$VD_pass = '';
$phone_login = '';
$phone_pass = '';
$webphone = '';
$action = '';
$lead_id = '';
$phone_number = '';
$session_name = '';
$qc_result = '';
$campaign_filter = '';
$date_start = '';
$date_end = '';

// POST verilerini al ve session'a kaydet
if (isset($_POST["VD_login"])) {
    $VD_login = $_POST["VD_login"];
    $_SESSION['VD_login'] = $VD_login;
}
if (isset($_POST["VD_pass"])) {
    $VD_pass = $_POST["VD_pass"];
    $_SESSION['VD_pass'] = $VD_pass;
}
if (isset($_POST["phone_login"])) {
    $phone_login = $_POST["phone_login"];
    $_SESSION['phone_login'] = $phone_login;
}
if (isset($_POST["phone_pass"])) {
    $phone_pass = $_POST["phone_pass"];
    $_SESSION['phone_pass'] = $phone_pass;
}
if (isset($_POST["webphone"])) {
    $webphone = $_POST["webphone"];
    $_SESSION['webphone'] = $webphone;
}

// Session'dan bilgileri al (GET isteklerinde)
if (empty($VD_login) && isset($_SESSION['VD_login'])) {
    $VD_login = $_SESSION['VD_login'];
}
if (empty($VD_pass) && isset($_SESSION['VD_pass'])) {
    $VD_pass = $_SESSION['VD_pass'];
}
if (empty($phone_login) && isset($_SESSION['phone_login'])) {
    $phone_login = $_SESSION['phone_login'];
}
if (empty($phone_pass) && isset($_SESSION['phone_pass'])) {
    $phone_pass = $_SESSION['phone_pass'];
}
if (empty($webphone) && isset($_SESSION['webphone'])) {
    $webphone = $_SESSION['webphone'];
}

// GET verilerini al
if (isset($_GET["action"]))         {$action=$_GET["action"];}
if (isset($_GET["lead_id"]))        {$lead_id=$_GET["lead_id"];}
if (isset($_GET["phone_number"]))   {$phone_number=$_GET["phone_number"];}
if (isset($_GET["qc_result"]))      {$qc_result=$_GET["qc_result"];}
if (isset($_GET["campaign_filter"])){$campaign_filter=$_GET["campaign_filter"];}
if (isset($_GET["date_start"]))     {$date_start=$_GET["date_start"];}
if (isset($_GET["date_end"]))       {$date_end=$_GET["date_end"];}

// Güvenlik kontrolü
$VD_login = preg_replace("/\'|\"|\\\\|;| /", "", $VD_login);
$VD_pass = preg_replace("/\'|\"|\\\\|;| /", "", $VD_pass);
$phone_login = preg_replace("/[^\,0-9a-zA-Z]/", "", $phone_login);
$phone_pass = preg_replace("/[^-_0-9a-zA-Z]/", "", $phone_pass);
$lead_id = preg_replace("/[^0-9]/", "", $lead_id);
$phone_number = preg_replace("/[^0-9]/", "", $phone_number);
$qc_result = preg_replace("/[^A-Z]/", "", $qc_result);
$campaign_filter = preg_replace("/[^-_0-9a-zA-Z]/", "", $campaign_filter);

$NOW_TIME = date("Y-m-d H:i:s");
$StarTtimE = date("U");
$CIDdate = date("ymdHis");
$session_name = $VD_login . '_' . $CIDdate;

// Login kontrolü
if (strlen($VD_login) < 1 || strlen($VD_pass) < 1) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Kullanıcı doğrulaması
$auth = 0;
$auth_message = user_authorization($VD_login,$VD_pass,'',1,0,1,0);
if (preg_match("/^GOOD/",$auth_message)) {
    $auth=1;
    $_SESSION['authenticated'] = true;
    $_SESSION['auth_time'] = time();
}

if ($auth == 0) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Session timeout kontrolü (30 dakika)
if (isset($_SESSION['auth_time']) && (time() - $_SESSION['auth_time'] > 1800)) {
    session_destroy();
    header("Location: ../index.php?timeout=1");
    exit;
}

$_SESSION['auth_time'] = time();

// Kullanıcı bilgilerini al
$stmt="SELECT full_name,user_level,user_group from vicidial_users where user='$VD_login' and active='Y';";
$rslt=mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row=mysqli_fetch_row($rslt);
    $full_name = $row[0];
    $user_level = $row[1];
    $user_group = $row[2];
    $allowed_campaigns = $row[3];
    
    if ($user_level < 7) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
} else {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$stmt="SELECT allowed_campaigns from vicidial_user_groups where user_group='$user_group' limit 1;";
$rslt=mysql_to_mysqli($stmt, $link);
 $row=mysqli_fetch_row($rslt);
 $allowed_campaigns = $row[0];

// User Group ayarlarını al - ice_agent.php'deki gibi
$webphone_url_override = '';
$webphone_dialpad_override = '';
$system_key = '';
$webphone_layout_override = '';

$stmt="SELECT forced_timeclock_login,shift_enforcement,group_shifts,agent_status_viewable_groups,agent_status_view_time,agent_call_log_view,agent_xfer_consultative,agent_xfer_dial_override,agent_xfer_vm_transfer,agent_xfer_blind_transfer,agent_xfer_dial_with_customer,agent_xfer_park_customer_dial,agent_fullscreen,webphone_url_override,webphone_dialpad_override,webphone_systemkey_override,admin_viewable_groups,agent_xfer_park_3way,webphone_layout from vicidial_user_groups where user_group='$user_group';";
$rslt=mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row=mysqli_fetch_row($rslt);
    $forced_timeclock_login = $row[0];
    $shift_enforcement = $row[1];
    $agent_fullscreen = $row[12];
    $webphone_url_override = $row[13];
    $webphone_dialpad_override = $row[14];
    $system_key = $row[15];
    $admin_viewable_groups = $row[16];
    $webphone_layout_override = $row[18];
}

// Phone tablosu ayarlarını al - ice_agent.php'deki gibi tam
$extension = '';
$conf_secret = '';
$is_webphone = 'N';
$use_external_server_ip = 'N';
$codecs_list = '';
$webphone_dialpad = 'Y';
$webphone_auto_answer = 'N';
$webphone_dialbox = 'Y';
$webphone_mute = 'Y';
$webphone_volume = 'Y';
$webphone_debug = 'N';
$webphone_layout = 'default';
$outbound_cid = '';
$protocol = 'SIP';

$stmt="SELECT extension,dialplan_number,voicemail_id,phone_ip,computer_ip,server_ip,login,pass,status,active,phone_type,fullname,company,picture,messages,old_messages,protocol,local_gmt,ASTmgrUSERNAME,ASTmgrSECRET,login_user,login_pass,login_campaign,park_on_extension,conf_on_extension,VICIDIAL_park_on_extension,VICIDIAL_park_on_filename,monitor_prefix,recording_exten,voicemail_exten,voicemail_dump_exten,ext_context,dtmf_send_extension,call_out_number_group,client_browser,install_directory,local_web_callerID_URL,VICIDIAL_web_URL,AGI_call_logging_enabled,user_switching_enabled,conferencing_enabled,admin_hangup_enabled,admin_hijack_enabled,admin_monitor_enabled,call_parking_enabled,updater_check_enabled,AFLogging_enabled,QUEUE_ACTION_enabled,CallerID_popup_enabled,voicemail_button_enabled,enable_fast_refresh,fast_refresh_rate,enable_persistant_mysql,auto_dial_next_number,VDstop_rec_after_each_call,DBX_server,DBX_database,DBX_user,DBX_pass,DBX_port,DBY_server,DBY_database,DBY_user,DBY_pass,DBY_port,outbound_cid,enable_sipsak_messages,email,template_id,conf_override,phone_context,phone_ring_timeout,conf_secret,is_webphone,use_external_server_ip,codecs_list,webphone_dialpad,phone_ring_timeout,on_hook_agent,webphone_auto_answer,webphone_dialbox,webphone_mute,webphone_volume,webphone_debug,webphone_layout from phones where login='$phone_login' and pass='$phone_pass' and active = 'Y';";
$rslt=mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row=mysqli_fetch_row($rslt);
    $extension = $row[0];
    $dialplan_number = $row[1];
    $voicemail_id = $row[2];
    $phone_ip = $row[3];
    $computer_ip = $row[4];
    $server_ip = $row[5];
    $login = $row[6];
    $pass = $row[7];
    $status = $row[8];
    $active = $row[9];
    $phone_type = $row[10];
    $fullname = $row[11];
    $company = $row[12];
    $picture = $row[13];
    $messages = $row[14];
    $old_messages = $row[15];
    $protocol = $row[16];
    $local_gmt = $row[17];
    $ASTmgrUSERNAME = $row[18];
    $ASTmgrSECRET = $row[19];
    $login_user = $row[20];
    $login_pass = $row[21];
    $login_campaign = $row[22];
    $park_on_extension = $row[23];
    $conf_on_extension = $row[24];
    $VICIDiaL_park_on_extension = $row[25];
    $VICIDiaL_park_on_filename = $row[26];
    $monitor_prefix = $row[27];
    $recording_exten = $row[28];
    $voicemail_exten = $row[29];
    $voicemail_dump_exten = $row[30];
    $ext_context = $row[31];
    $dtmf_send_extension = $row[32];
    $call_out_number_group = $row[33];
    $client_browser = $row[34];
    $install_directory = $row[35];
    $local_web_callerID_URL = $row[36];
    $VICIDiaL_web_URL = $row[37];
    $AGI_call_logging_enabled = $row[38];
    $user_switching_enabled = $row[39];
    $conferencing_enabled = $row[40];
    $admin_hangup_enabled = $row[41];
    $admin_hijack_enabled = $row[42];
    $admin_monitor_enabled = $row[43];
    $call_parking_enabled = $row[44];
    $updater_check_enabled = $row[45];
    $AFLogging_enabled = $row[46];
    $QUEUE_ACTION_enabled = $row[47];
    $CallerID_popup_enabled = $row[48];
    $voicemail_button_enabled = $row[49];
    $enable_fast_refresh = $row[50];
    $fast_refresh_rate = $row[51];
    $enable_persistant_mysql = $row[52];
    $auto_dial_next_number = $row[53];
    $VDstop_rec_after_each_call = $row[54];
    $DBX_server = $row[55];
    $DBX_database = $row[56];
    $DBX_user = $row[57];
    $DBX_pass = $row[58];
    $DBX_port = $row[59];
    $outbound_cid = $row[65];
    $enable_sipsak_messages = $row[66];
    $conf_secret = $row[72];
    $is_webphone = $row[73];
    $use_external_server_ip = $row[74];
    $codecs_list = $row[75];
    $webphone_dialpad = $row[76];
    $phone_ring_timeout = $row[77];
    $on_hook_agent = $row[78];
    $webphone_auto_answer = $row[79];
    $webphone_dialbox = $row[80];
    $webphone_mute = $row[81];
    $webphone_volume = $row[82];
    $webphone_debug = $row[83];
    $webphone_layout = $row[84];
}

// webphone_layout override kontrolü
if (strlen($webphone_layout_override) > 0) {
    $webphone_layout = $webphone_layout_override;
}

// webphone_dialpad override kontrolü
if (($webphone_dialpad_override != 'DISABLED') && (strlen($webphone_dialpad_override) > 0)) {
    $webphone_dialpad = $webphone_dialpad_override;
}

// System settings'ten webphone bilgilerini al
$webphone_url = '';
$web_socket_url = '';
$webphone_width = 460;
$webphone_height = 500;
$webphone_location = 'right';

$stmt = "SELECT webphone_url FROM system_settings LIMIT 1;";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    if (strlen($webphone_url_override) < 6) {
        $webphone_url = $row[0];
    } else {
        $webphone_url = $webphone_url_override;
    }
    
}
$stmt = "SELECT server_ip,web_socket_url FROM servers LIMIT 1;";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    
        $server_ip = $row[0];
        $web_socket_url= $row[1];
}

// Server bilgilerini al - external IP için
$webphone_server_ip = $server_ip;
if ($use_external_server_ip == 'Y') {
    $stmt = "SELECT external_server_ip,  FROM servers where server_ip='$server_ip' LIMIT 1;";
    $rslt = mysql_to_mysqli($stmt, $link);
    if (mysqli_num_rows($rslt) > 0) {
        $row = mysqli_fetch_row($rslt);
        $webphone_server_ip = $row[0];
   
    }
}

// System key kontrolü
if (strlen($system_key) < 1) {
    $stmt = "SELECT webphone_systemkey FROM system_settings LIMIT 1;";
    $rslt = mysql_to_mysqli($stmt, $link);
    if (mysqli_num_rows($rslt) > 0) {
        $row = mysqli_fetch_row($rslt);
        $system_key = $row[0];
    }
}

// WebPhone iframe URL'sini oluştur - ice_agent.php'deki gibi
$WebPhonEurl = '';
$webphone_content = '';

if ($is_webphone == 'Y') {
    // Codecs temizle
    $codecs_list = preg_replace("/ /", '', $codecs_list);
    $codecs_list = preg_replace("/-/", '', $codecs_list);
    $codecs_list = preg_replace("/&/", '', $codecs_list);
    
    // WebPhone options oluştur
    $webphone_options = 'INITIAL_LOAD';
    if ($webphone_dialpad == 'Y') { $webphone_options .= "--DIALPAD_Y"; }
    if ($webphone_dialpad == 'N') { $webphone_options .= "--DIALPAD_N"; }
    if ($webphone_dialpad == 'TOGGLE') { $webphone_options .= "--DIALPAD_TOGGLE"; }
    if ($webphone_dialpad == 'TOGGLE_OFF') { $webphone_options .= "--DIALPAD_OFF_TOGGLE"; }
    if ($webphone_auto_answer == 'Y') { $webphone_options .= "--AUTOANSWER_Y"; }
    if ($webphone_auto_answer == 'N') { $webphone_options .= "--AUTOANSWER_N"; }
    if ($webphone_dialbox == 'Y') { $webphone_options .= "--DIALBOX_Y"; }
    if ($webphone_dialbox == 'N') { $webphone_options .= "--DIALBOX_N"; }
    if ($webphone_mute == 'Y') { $webphone_options .= "--MUTE_Y"; }
    if ($webphone_mute == 'N') { $webphone_options .= "--MUTE_N"; }
    if ($webphone_volume == 'Y') { $webphone_options .= "--VOLUME_Y"; }
    if ($webphone_volume == 'N') { $webphone_options .= "--VOLUME_N"; }
    if ($webphone_debug == 'Y') { $webphone_options .= "--DEBUG"; }
    if (strlen($web_socket_url) > 5) { $webphone_options .= "--WEBSOCKETURL$web_socket_url"; }
    if (strlen($webphone_layout) > 0) { $webphone_options .= "--WEBPHONELAYOUT$webphone_layout"; }
    
    // FQDN oluştur
    $server_name = $_SERVER['SERVER_NAME'];
    $server_port = $_SERVER['SERVER_PORT'];
    if ($server_port == '80' || $server_port == '443') {
        $server_port = '';
    } else {
        $server_port = ':' . $server_port;
    }
    $FQDN = $server_name . $server_port;
    
    $webphone_url = preg_replace("/LOCALFQDN/", $FQDN, $webphone_url);
    
    // Base64 encode variables
    $b64_phone_login = base64_encode($extension);
    $b64_phone_pass = base64_encode($conf_secret);
    $b64_session_name = base64_encode($session_name);
    $b64_server_ip = base64_encode($webphone_server_ip);
    $b64_callerid = base64_encode($outbound_cid);
    $b64_protocol = base64_encode($protocol);
    $b64_codecs = base64_encode($codecs_list);
    $b64_options = base64_encode($webphone_options);
    $b64_system_key = base64_encode($system_key);
    
    $WebPhonEurl = "$webphone_url?phone_login=$b64_phone_login&phone_pass=$b64_phone_pass&server_ip=$b64_server_ip&callerid=$b64_callerid&protocol=$b64_protocol&codecs=$b64_codecs&options=$b64_options&system_key=$b64_system_key";
  
    // Iframe content oluştur
    if ($webphone_location == 'bar') {
        $webphone_content = "<iframe src=\"$WebPhonEurl\" style=\"width:" . $webphone_width . "px;height:" . $webphone_height . "px;background-color:transparent;z-index:17;\" scrolling=\"no\" frameborder=\"0\" allowtransparency=\"true\" id=\"webphone\" name=\"webphone\" width=\"" . $webphone_width . "px\" height=\"" . $webphone_height . "px\" allow=\"microphone\"> </iframe>";
    } else {
        $webphone_content = "<iframe src=\"$WebPhonEurl\" style=\"width:" . $webphone_width . "px;height:" . $webphone_height . "px;background-color:transparent;z-index:17;\" scrolling=\"auto\" frameborder=\"0\" allowtransparency=\"true\" id=\"webphone\" name=\"webphone\" width=\"" . $webphone_width . "px\" height=\"" . $webphone_height . "px\" allow=\"microphone\"> </iframe>";
    }
}

// KISIM 1 SONU - Kısım 2'ye devam edecek...
?>
<?php
// KISIM 2 BAŞLANGIÇ - Kısım 1'den sonra gelecek

// Grup bazlı kampanya kontrolü
$campaign_sql = "";
if (!preg_match("/ALL-CAMPAIGNS/i", $allowed_campaigns)) {
    $allowed_campaigns = preg_replace('/\s-/i','',$allowed_campaigns);
    $allowed_campaigns = preg_replace('/\s/i',"','",$allowed_campaigns);
    $campaign_sql = "and campaign_id IN('$allowed_campaigns')";
}

// VICIdial session kayıtları oluştur
$stmt = "INSERT INTO vicidial_live_agents (user,server_ip,conf_exten,extension,status,lead_id,campaign_id,uniqueid,callerid,channel,random_id,last_call_time,last_update_time,last_call_finish,closer_campaigns,call_server_ip,user_level,comments,calls_today,pause_code) VALUES('$VD_login','$server_ip','','$phone_login','READY','','QC','','','','$StarTtimE','$NOW_TIME','$NOW_TIME','','','$server_ip','$user_level','QC_Panel','0','') ON DUPLICATE KEY UPDATE status='READY',last_update_time='$NOW_TIME';";
$rslt=mysql_to_mysqli($stmt, $link);

$stmt = "INSERT INTO vicidial_user_log (user,event,campaign_id,event_date,event_epoch,user_group,session_id) values('$VD_login','LOGIN','QC','$NOW_TIME','$StarTtimE','$user_group','$session_name');";
$rslt=mysql_to_mysqli($stmt, $link);

// Aktif session kontrol et
$qc_session_active = false;
$recording_status = 'OFF';
$agent_status = 'READY';
$calls_today = 0;

$stmt="SELECT status,pause_code,calls_today,comments FROM vicidial_live_agents where user='$VD_login';";
$rslt=mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row=mysqli_fetch_row($rslt);
    $agent_status = $row[0];
    $pause_code = $row[1];
    $calls_today = $row[2];
    $comments = $row[3];
    $qc_session_active = true;
    
    if (preg_match("/REC_ON/", $comments)) {
        $recording_status = 'ON';
    }
}

// ACTION İŞLEMLERİ
if ($action == 'manual_dial' && $phone_number) {
    $lead_found = false;
    $stmt = "SELECT lead_id,first_name,last_name,campaign_id FROM vicidial_list WHERE phone_number='$phone_number' LIMIT 1;";
    $rslt = mysql_to_mysqli($stmt, $link);
    if (mysqli_num_rows($rslt) > 0) {
        $row = mysqli_fetch_row($rslt);
        $found_lead_id = $row[0];
        $customer_name = $row[1] . ' ' . $row[2];
        $lead_campaign = $row[3];
        $lead_found = true;
    } else {
        $found_lead_id = $lead_id;
        $customer_name = 'Unknown';
        $lead_campaign = 'QC';
    }
    
    $stmt = "UPDATE vicidial_live_agents SET status='INCALL',lead_id='$found_lead_id',campaign_id='$lead_campaign',last_call_time='$NOW_TIME',calls_today=calls_today+1 WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    $uniqueid = $CIDdate . '_' . $VD_login;
    $stmt = "INSERT INTO call_log (uniqueid,channel,channel_group,type,server_ip,extension,number_dialed,caller_code,start_time,start_epoch,end_time,end_epoch,length_in_sec,length_in_min,adapter,lead_id,phone_code,phone_number,user,comments) VALUES('$uniqueid','Local/$phone_login','QC_MANUAL','OUT','$server_ip','$phone_login','$phone_number','QC','$NOW_TIME','$StarTtimE','$NOW_TIME','$StarTtimE','0','0','QC_Panel','$found_lead_id','1','$phone_number','$VD_login','QC_Manual_Dial');";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Arama başlatıldı","phone_number":"'.$phone_number.'","lead_id":"'.$found_lead_id.'","customer":"'.$customer_name.'","uniqueid":"'.$uniqueid.'"}';
        exit;
    }
}

if ($action == 'hangup_call') {
    $stmt = "UPDATE vicidial_live_agents SET status='PAUSED',lead_id='',last_call_finish='$NOW_TIME' WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Çağrı sonlandırıldı","show_qc_modal":true}';
        exit;
    }
}

if ($action == 'save_qc_result' && $qc_result && $lead_id) {
    $qc_codes = array(
        'OK' => 'Başarılı',
        'RED' => 'Red',
        'NOANS' => 'Ulaşılamadı', 
        'BUSY' => 'Meşgul',
        'HOLD' => 'Beklemeye Alındı',
        'CALLBACK' => 'Geri Arama',
        'FOLLOWUP' => 'Takip'
    );
    
    if (array_key_exists($qc_result, $qc_codes)) {
        $qc_description = $qc_codes[$qc_result];
        $stmt = "UPDATE A_ozel_tablo SET qc_result='$qc_result', qc_description='$qc_description', qc_agent='$VD_login', qc_date='$NOW_TIME' WHERE lead_id='$lead_id';";
        $rslt = mysql_to_mysqli($stmt, $link);
        
        $stmt = "INSERT INTO vicidial_agent_log (user,event,campaign_id,event_date,event_epoch,lead_id,phone_number,user_group) VALUES('$VD_login','QC_RESULT','QC','$NOW_TIME','$StarTtimE','$lead_id','$phone_number','$user_group');";
        $rslt = mysql_to_mysqli($stmt, $link);
        
        $stmt = "UPDATE vicidial_live_agents SET status='READY' WHERE user='$VD_login';";
        $rslt = mysql_to_mysqli($stmt, $link);
    }
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"QC sonucu kaydedildi: '.$qc_description.'","qc_result":"'.$qc_result.'"}';
        exit;
    }
}

if ($action == 'recording_start') {
    $stmt = "UPDATE vicidial_live_agents SET comments='QC_Panel_REC_ON' WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    $recording_status = 'ON';
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Kayıt başlatıldı","recording":"ON"}';
        exit;
    }
}

if ($action == 'recording_stop') {
    $stmt = "UPDATE vicidial_live_agents SET comments='QC_Panel_REC_OFF' WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    $recording_status = 'OFF';
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Kayıt durduruldu","recording":"OFF"}';
        exit;
    }
}

if ($action == 'change_status') {
    $new_status = isset($_GET['status']) ? $_GET['status'] : 'PAUSED';
    $new_status = preg_replace("/[^A-Z]/", "", $new_status);
    
    $stmt = "UPDATE vicidial_live_agents SET status='$new_status',last_update_time='$NOW_TIME' WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Durum güncellendi","agent_status":"'.$new_status.'"}';
        exit;
    }
}

if ($action == 'status_check') {
    $stmt="SELECT status,calls_today,comments FROM vicidial_live_agents where user='$VD_login';";
    $rslt=mysql_to_mysqli($stmt, $link);
    if (mysqli_num_rows($rslt) > 0) {
        $row=mysqli_fetch_row($rslt);
        $current_status = $row[0];
        $current_calls = $row[1];
        $current_comments = $row[2];
        $current_recording = preg_match("/REC_ON/", $current_comments) ? 'ON' : 'OFF';
        
        header('Content-Type: application/json');
        echo '{"status":"success","agent_status":"'.$current_status.'","calls_today":'.$current_calls.',"recording":"'.$current_recording.'"}';
        exit;
    }
}

if ($action == 'logout') {
    $stmt = "DELETE FROM vicidial_live_agents WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    $stmt = "INSERT INTO vicidial_user_log (user,event,campaign_id,event_date,event_epoch,user_group,session_id) values('$VD_login','LOGOUT','QC','$NOW_TIME','$StarTtimE','$user_group','$session_name');";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Filtre oluşturma
$search_filter = "WHERE 1=1 ";
if (isset($_GET['search']) && strlen($_GET['search']) > 0) {
    $search = mysqli_real_escape_string($link, $_GET['search']);
    $search_filter .= "AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR phone_number LIKE '%$search%' OR user LIKE '%$search%') ";
}

if ($campaign_filter) {
    $search_filter .= "AND campaign_id='$campaign_filter' ";
}

if ($date_start) {
    $search_filter .= "AND DATE(entry_date) >= '$date_start' ";
}

if ($date_end) {
    $search_filter .= "AND DATE(entry_date) <= '$date_end' ";
}

if (isset($_GET['qc_filter']) && $_GET['qc_filter'] != '') {
    $qc_filter = mysqli_real_escape_string($link, $_GET['qc_filter']);
    if ($qc_filter == 'PENDING') {
        $search_filter .= "AND (qc_result IS NULL OR qc_result = '') ";
    } else {
        $search_filter .= "AND qc_result='$qc_filter' ";
    }
}

// Sayfalama
$page = 1;
if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
}
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Toplam kayıt sayısı
$count_stmt = "SELECT COUNT(*) FROM A_ozel_tablo $search_filter $campaign_sql";
$count_rslt = mysql_to_mysqli($count_stmt, $link);
$count_row = mysqli_fetch_row($count_rslt);
$total_records = $count_row[0];
$total_pages = ceil($total_records / $records_per_page);

// Satış kayıtları
$stmt = "SELECT * FROM A_ozel_tablo $search_filter $campaign_sql ORDER BY entry_date DESC LIMIT $offset, $records_per_page";
$rslt = mysql_to_mysqli($stmt, $link);

// Kampanya listesi çek
$campaign_list_stmt = "SELECT DISTINCT campaign_id FROM A_ozel_tablo $campaign_sql ORDER BY campaign_id";
$campaign_list_rslt = mysql_to_mysqli($campaign_list_stmt, $link);

// URL oluştur fonksiyonu
function build_url($page = null) {
    $params = $_GET;
    unset($params['action']);
    
    if ($page !== null) {
        $params['page'] = $page;
    }
    
    $query_string = http_build_query($params);
    return $_SERVER['PHP_SELF'] . ($query_string ? '?' . $query_string : '');
}

header ("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VICIdial QC Panel - Enhanced with WebPhone</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }

        .card-header {
            background: var(--gradient-bg);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            font-weight: 600;
        }

        .btn-call {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            border: none;
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-hangup {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            border: none;
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-hangup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .stats-card {
            background: var(--gradient-bg);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }

        .qc-result-badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .qc-result-OK { background: var(--success-color); color: white; }
        .qc-result-RED { background: var(--danger-color); color: white; }
        .qc-result-NOANS { background: var(--warning-color); color: white; }
        .qc-result-BUSY { background: var(--info-color); color: white; }
        .qc-result-HOLD { background: #8b5cf6; color: white; }
        .qc-result-CALLBACK { background: #f97316; color: white; }
        .qc-result-FOLLOWUP { background: #06b6d4; color: white; }
        .qc-result-PENDING { background: #6b7280; color: white; }

        .agent-status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .status-READY { background: var(--success-color); color: white; }
        .status-INCALL { background: var(--info-color); color: white; }
        .status-PAUSED { background: var(--warning-color); color: white; }

        .webphone-container {
            position: fixed;
            top: 80px;
            <?php echo ($webphone_location == 'bar') ? 'bottom: 0; left: 0; right: 0; height: ' . $webphone_height . 'px;' : 'right: 20px; width: ' . $webphone_width . 'px; max-height: ' . $webphone_height . 'px;'; ?>
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: <?php echo ($webphone_location == 'bar') ? '0' : '15px'; ?>;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            transition: all 0.3s ease;
            transform: <?php echo ($webphone_location == 'bar') ? 'translateY(100%)' : 'translateX(320px)'; ?>;
            overflow: hidden;
        }

        .webphone-container.active {
            transform: translate(0, 0);
        }

        .webphone-header {
            background: var(--gradient-bg);
            color: white;
            padding: 1rem;
            border-radius: <?php echo ($webphone_location == 'bar') ? '0' : '15px 15px 0 0'; ?>;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .webphone-iframe-container {
            width: 100%;
            height: calc(100% - 60px);
            overflow: hidden;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
        }

        .session-warning {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f59e0b;
            color: white;
            text-align: center;
            padding: 0.5rem;
            z-index: 9998;
            font-weight: 600;
            display: none;
        }

        @media (max-width: 768px) {
            .webphone-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                border-radius: 0;
                transform: translateY(100%);
            }
            
            .webphone-container.active {
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .status-INCALL {
            animation: pulse 2s infinite;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(37, 99, 235, 0.05) !important;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .webphone-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Session timeout warning -->
    <div class="session-warning" id="sessionWarning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Session will expire in <span id="sessionTimer">5</span> minutes. Please save your work.
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-clipboard-check me-2"></i>VICIdial QC Panel
                <?php if ($is_webphone == 'Y'): ?>
                <span class="badge bg-success ms-2">WebPhone Enabled</span>
                <?php endif; ?>
            </a>
            
            <!-- Agent Status Panel -->
            <div class="navbar-nav mx-auto">
                <div class="nav-item d-flex align-items-center">
                    <span id="agent-status" class="agent-status-badge status-<?php echo $agent_status; ?>"><?php echo $agent_status; ?></span>
                    <span class="ms-3 text-muted">Calls: <span id="calls-count" class="fw-bold text-primary"><?php echo $calls_today; ?></span></span>
                    <span class="ms-3 text-muted">Recording: <span id="recording-status" class="fw-bold <?php echo $recording_status == 'ON' ? 'text-danger' : 'text-secondary'; ?>"><?php echo $recording_status; ?></span></span>
                </div>
            </div>
            
            <!-- Control Buttons -->
            <div class="navbar-nav">
                <div class="nav-item d-flex align-items-center gap-2">
                    <button class="btn btn-success btn-sm" onclick="changeStatus('READY')" title="Ready">
                        <i class="fas fa-play"></i>
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="changeStatus('PAUSED')" title="Pause">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button class="btn btn-info btn-sm" id="record-btn" onclick="toggleRecording()" title="Toggle Recording">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <?php if ($is_webphone == 'Y'): ?>
                    <button class="btn btn-primary btn-sm" onclick="toggleWebphone()" title="WebPhone">
                        <i class="fas fa-phone"></i> WebPhone
                    </button>
                    <?php endif; ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-primary" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($full_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">Level: <?php echo $user_level; ?></span></li>
                            <li><span class="dropdown-item-text">Phone: <?php echo $phone_login; ?></span></li>
                            <li><span class="dropdown-item-text">Extension: <?php echo $extension; ?></span></li>
                            <li><span class="dropdown-item-text">Session: <?php echo substr($session_name, -8); ?></span></li>
                            <?php if ($is_webphone == 'Y'): ?>
                            <li><span class="dropdown-item-text">WebPhone: Active</span></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Çıkış
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- WebPhone Container -->
    <?php if ($is_webphone == 'Y' && !empty($webphone_content)): ?>
    <div class="webphone-container" id="webphoneContainer">
        <div class="webphone-header">
            <div>
                <i class="fas fa-phone me-2"></i>WebPhone - <?php echo $extension; ?>
                <div style="font-size: 0.8rem; opacity: 0.8;">
                    Server: <?php echo $webphone_server_ip; ?>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-light" onclick="toggleWebphone()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="webphone-iframe-container">
            <?php echo $webphone_content; ?>
        </div>
    </div>
    <?php endif; ?>

<!-- KISIM 2 SONU - Kısım 3'e devam edecek... --> 
<div class="container-fluid mt-4 fade-in">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h3><?php echo $total_records; ?></h3>
                    <p class="mb-0">Toplam Satış</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h3><?php echo date('d.m.Y'); ?></h3>
                    <p class="mb-0">Bugün</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3>QC</h3>
                    <p class="mb-0">Panel</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3><?php echo $total_pages; ?></h3>
                    <p class="mb-0">Sayfa</p>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Gelişmiş Filtreler
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3" id="filterForm">
                    <div class="col-md-3">
                        <label class="form-label">Arama</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="İsim, telefon, agent..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Kampanya</label>
                        <select class="form-select" name="campaign_filter">
                            <option value="">Tümü</option>
                            <?php
                            mysqli_data_seek($campaign_list_rslt, 0); // Reset pointer
                            while ($camp_row = mysqli_fetch_row($campaign_list_rslt)) {
                                $selected = ($campaign_filter == $camp_row[0]) ? 'selected' : '';
                                echo "<option value='{$camp_row[0]}' $selected>{$camp_row[0]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">QC Sonuç</label>
                        <select class="form-select" name="qc_filter">
                            <option value="">Tümü</option>
                            <option value="PENDING" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'PENDING') ? 'selected' : ''; ?>>Bekliyor</option>
                            <option value="OK" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'OK') ? 'selected' : ''; ?>>Başarılı</option>
                            <option value="RED" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'RED') ? 'selected' : ''; ?>>Red</option>
                            <option value="NOANS" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'NOANS') ? 'selected' : ''; ?>>Ulaşılamadı</option>
                            <option value="BUSY" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'BUSY') ? 'selected' : ''; ?>>Meşgul</option>
                            <option value="HOLD" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'HOLD') ? 'selected' : ''; ?>>Beklemeye Alındı</option>
                            <option value="CALLBACK" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'CALLBACK') ? 'selected' : ''; ?>>Geri Arama</option>
                            <option value="FOLLOWUP" <?php echo (isset($_GET['qc_filter']) && $_GET['qc_filter'] == 'FOLLOWUP') ? 'selected' : ''; ?>>Takip</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" name="date_start" 
                               value="<?php echo isset($_GET['date_start']) ? $_GET['date_start'] : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="date_end" 
                               value="<?php echo isset($_GET['date_end']) ? $_GET['date_end'] : ''; ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div class="mt-2">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-refresh me-1"></i>Temizle
                    </a>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>QC Kayıtları
                    <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> kayıt</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Agent</th>
                                <th>Müşteri</th>
                                <th>Telefon</th>
                                <th>Kampanya</th>
                                <th>Tarih</th>
                                <th>QC Sonuç</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($rslt) > 0) {
                                while ($row = mysqli_fetch_assoc($rslt)) {
                                    $qc_result_display = '';
                                    $qc_class = 'qc-result-PENDING';
                                    
                                    if ($row['qc_result']) {
                                        $qc_result_display = $row['qc_description'] ?: $row['qc_result'];
                                        $qc_class = 'qc-result-' . $row['qc_result'];
                                    } else {
                                        $qc_result_display = 'Bekliyor';
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td><span class='badge bg-primary'>" . $row['id'] . "</span></td>";
                                    echo "<td><strong>" . htmlspecialchars($row['user']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                    echo "<td><code>" . htmlspecialchars($row['phone_number']) . "</code></td>";
                                    echo "<td><span class='badge bg-info'>" . htmlspecialchars($row['campaign_id']) . "</span></td>";
                                    echo "<td>" . date('d.m.Y H:i', strtotime($row['entry_date'])) . "</td>";
                                    echo "<td><span class='qc-result-badge $qc_class'>$qc_result_display</span></td>";
                                    echo "<td>";
                                    echo "<button class='btn btn-call btn-sm me-2' onclick='makeCall(\"" . $row['phone_number'] . "\", \"" . $row['lead_id'] . "\")'>";
                                    echo "<i class='fas fa-phone me-1'></i>Ara";
                                    echo "</button>";
                                    echo "<button class='btn btn-outline-primary btn-sm' onclick='showDetails(" . $row['id'] . ")'>";
                                    echo "<i class='fas fa-eye me-1'></i>Detay";
                                    echo "</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center py-4'>";
                                echo "<i class='fas fa-inbox fa-2x text-muted mb-2'></i><br>";
                                echo "<span class='text-muted'>Kayıt bulunamadı</span>";
                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fixed Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Sayfa navigasyonu" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                // Previous button
                if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo build_url($page - 1); ?>">
                        <i class="fas fa-chevron-left"></i> Önceki
                    </a>
                </li>
                <?php endif; ?>
                
                <?php
                // Page numbers with smart range
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo build_url(1); ?>">1</a>
                </li>
                <?php if ($start_page > 2): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo build_url($i); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo build_url($total_pages); ?>"><?php echo $total_pages; ?></a>
                </li>
                <?php endif; ?>
                
                <?php
                // Next button
                if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo build_url($page + 1); ?>">
                        Sonraki <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Page info -->
            <div class="text-center mt-2">
                <small class="text-muted">
                    Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?> 
                    (<?php echo $total_records; ?> kayıt)
                </small>
            </div>
        </nav>
        <?php endif; ?>
    </div>

    <!-- QC Result Modal -->
    <div class="modal fade" id="qcResultModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-check me-2"></i>QC Sonuç Seçimi
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Çağrı için uygun QC sonucunu seçiniz:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" onclick="saveQCResult('OK')">
                            <i class="fas fa-check-circle me-2"></i>Başarılı (OK)
                        </button>
                        <button class="btn btn-danger btn-lg" onclick="saveQCResult('RED')">
                            <i class="fas fa-times-circle me-2"></i>Red (RED)
                        </button>
                        <button class="btn btn-warning btn-lg" onclick="saveQCResult('NOANS')">
                            <i class="fas fa-phone-slash me-2"></i>Ulaşılamadı (NOANS)
                        </button>
                        <button class="btn btn-info btn-lg" onclick="saveQCResult('BUSY')">
                            <i class="fas fa-phone-volume me-2"></i>Meşgul (BUSY)
                        </button>
                        <button class="btn btn-secondary btn-lg" onclick="saveQCResult('HOLD')">
                            <i class="fas fa-pause-circle me-2"></i>Beklemeye Alındı (HOLD)
                        </button>
                        <button class="btn" style="background: #f97316; color: white;" onclick="saveQCResult('CALLBACK')">
                            <i class="fas fa-phone-alt me-2"></i>Geri Arama (CALLBACK)
                        </button>
                        <button class="btn" style="background: #06b6d4; color: white;" onclick="saveQCResult('FOLLOWUP')">
                            <i class="fas fa-user-clock me-2"></i>Takip (FOLLOWUP)
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Enhanced JavaScript with Session Management -->
    <script>
    var currentRecordingStatus = '<?php echo $recording_status; ?>';
    var agentStatus = '<?php echo $agent_status; ?>';
    var currentLeadId = '';
    var currentPhoneNumber = '';
    var webphoneActive = false;
    var callActive = false;
    var sessionTimeout = null;
    
    // WebPhone Variables - ice_agent.php'den referans alınarak
    let webphoneSession = null;
    let webphoneRegistered = false;
    let userAgent = null;

    // WebPhone configuration
    const webphoneConfig = {
        extension: '<?php echo $phone_login; ?>',
        password: '<?php echo $phone_pass; ?>',
        server: '<?php echo $server_ip; ?>',
        websocketUrl: '<?php echo $web_socket_url; ?>',
        autoAnswer: <?php echo ($webphone_auto_answer == 'Y') ? 'true' : 'false'; ?>,
        dialpadColor: '<?php echo $webphone_dialpad_color; ?>',
        protocol: '<?php echo $protocol; ?>'
    };

    // Session Management Functions
    function initSessionManagement() {
        // Session timeout warning (25 dakika sonra uyar, 30 dakikada logout)
        sessionTimeout = setTimeout(showSessionWarning, 25 * 60 * 1000);
        
        // Her 5 dakikada bir session'ı yenile
        setInterval(refreshSession, 5 * 60 * 1000);
    }

    function showSessionWarning() {
        const warning = document.getElementById('sessionWarning');
        const timer = document.getElementById('sessionTimer');
        let minutes = 5;
        
        warning.style.display = 'block';
        
        const countdown = setInterval(() => {
            minutes--;
            timer.textContent = minutes;
            
            if (minutes <= 0) {
                clearInterval(countdown);
                // Force logout
                window.location.href = '?action=logout';
            }
        }, 60000);
    }

    function refreshSession() {
        // Silent session refresh
        fetch('?action=status_check&ajax=1')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Session refreshed successfully
                    console.log('Session refreshed');
                }
            })
            .catch(error => {
                console.error('Session refresh failed:', error);
            });
    }

    // WebPhone Functions - ice_agent.php'den referans alınarak düzeltilmiş
    function initializeWebPhone() {
        console.log('Initializing WebPhone with config:', webphoneConfig);
        updateWebPhoneStatus('Bağlanıyor...');
        
        if (!webphoneConfig.extension || !webphoneConfig.password) {
            showNotification('WebPhone: Extension bilgileri eksik', 'error');
            updateWebPhoneStatus('Hata: Bilgiler eksik');
            return;
        }

        try {
            // SIP.js UserAgent oluştur
            const uri = `sip:${webphoneConfig.extension}@${webphoneConfig.server}`;
            
            const userAgentOptions = {
                uri: SIP.UserAgent.makeURI(uri),
                transportOptions: {
                    server: webphoneConfig.websocketUrl,
                    keepAliveInterval: 30,
                    connectionTimeout: 15,
                    maxReconnectionAttempts: 10,
                    reconnectionTimeout: 4
                },
                authorizationUsername: webphoneConfig.extension,
                authorizationPassword: webphoneConfig.password,
                displayName: '<?php echo $full_name; ?>',
                sessionDescriptionHandlerFactoryOptions: {
                    constraints: {
                        audio: true,
                        video: false
                    }
                },
                delegate: {
                    onConnect: () => {
                        console.log('WebPhone connected to server');
                        showNotification('WebPhone: Sunucuya bağlandı', 'success');
                        updateWebPhoneStatus('Bağlandı');
                    },
                    onDisconnect: (error) => {
                        console.log('WebPhone disconnected:', error);
                        webphoneRegistered = false;
                        showNotification('WebPhone: Bağlantı kesildi', 'warning');
                        updateWebPhoneStatus('Bağlantı kesildi');
                    },
                    onInvite: (invitation) => {
                        console.log('Incoming call from:', invitation.remoteIdentity.uri.user);
                        handleIncomingCall(invitation);
                    }
                }
            };

            userAgent = new SIP.UserAgent(userAgentOptions);
            
            // Start the user agent
            userAgent.start().then(() => {
                console.log('WebPhone UserAgent started');
                updateWebPhoneStatus('Kayıt oluyor...');
                
                // Register
                const registerer = new SIP.Registerer(userAgent);
                registerer.register().then(() => {
                    webphoneRegistered = true;
                    showNotification(`WebPhone: ${webphoneConfig.extension} kayıtlı`, 'success');
                    updateWebPhoneStatus('Kayıtlı ✓');
                }).catch((error) => {
                    console.error('WebPhone registration failed:', error);
                    showNotification('WebPhone: Kayıt başarısız - ' + error.message, 'error');
                    updateWebPhoneStatus('Kayıt hatası');
                });
                
            }).catch((error) => {
                console.error('WebPhone UserAgent start failed:', error);
                showNotification('WebPhone: Başlatma hatası - ' + error.message, 'error');
                updateWebPhoneStatus('Başlatma hatası');
            });

        } catch (error) {
            console.error('WebPhone initialization error:', error);
            showNotification('WebPhone: Başlatma hatası - ' + error.message, 'error');
            updateWebPhoneStatus('Başlatma hatası');
        }
    }

    function updateWebPhoneStatus(status) {
        const statusElement = document.getElementById('webphoneStatus');
        if (statusElement) {
            statusElement.textContent = status;
            
            // Status'a göre renk değiştir
            statusElement.className = 'webphone-status';
            if (status.includes('✓') || status.includes('Kayıtlı')) {
                statusElement.style.background = 'rgba(16, 185, 129, 0.2)';
                statusElement.style.color = '#10b981';
            } else if (status.includes('Hata') || status.includes('hatası')) {
                statusElement.style.background = 'rgba(239, 68, 68, 0.2)';
                statusElement.style.color = '#ef4444';
            } else if (status.includes('Görüşmede')) {
                statusElement.style.background = 'rgba(6, 182, 212, 0.2)';
                statusElement.style.color = '#06b6d4';
            } else {
                statusElement.style.background = 'rgba(245, 158, 11, 0.2)';
                statusElement.style.color = '#f59e0b';
            }
        }
    }

    function makeWebRTCCall(number) {
        if (!webphoneRegistered || !userAgent) {
            showNotification('WebPhone: Kayıtlı değil', 'error');
            return;
        }
        
        try {
            const target = SIP.UserAgent.makeURI(`sip:${number}@${webphoneConfig.server}`);
            if (!target) {
                showNotification('WebPhone: Geçersiz numara', 'error');
                return;
            }

            const inviter = new SIP.Inviter(userAgent, target, {
                sessionDescriptionHandlerOptions: {
                    constraints: {
                        audio: true,
                        video: false
                    }
                }
            });

            inviter.stateChange.addListener((newState) => {
                console.log('Call state changed to:', newState);
                switch (newState) {
                    case SIP.SessionState.Establishing:
                        showNotification(`WebPhone: ${number} aranıyor...`, 'info');
                        updateWebPhoneStatus('Arıyor...');
                        break;
                    case SIP.SessionState.Established:
                        webphoneSession = inviter;
                        showNotification(`WebPhone: ${number} ile görüşme başladı`, 'success');
                        updateWebPhoneStatus('Görüşmede ✓');
                        break;
                    case SIP.SessionState.Terminated:
                        webphoneSession = null;
                        showNotification('WebPhone: Görüşme sonlandı', 'info');
                        updateWebPhoneStatus('Kayıtlı ✓');
                        break;
                }
            });

            inviter.invite().catch((error) => {
                console.error('Call failed:', error);
                showNotification('WebPhone: Arama başarısız - ' + error.message, 'error');
                updateWebPhoneStatus('Kayıtlı ✓');
            });

        } catch (error) {
            console.error('WebRTC call error:', error);
            showNotification('WebPhone: Arama hatası - ' + error.message, 'error');
        }
    }

    function hangupWebRTCCall() {
        if (webphoneSession) {
            try {
                webphoneSession.bye();
                webphoneSession = null;
                showNotification('WebPhone: Görüşme sonlandırıldı', 'info');
                updateWebPhoneStatus('Kayıtlı ✓');
            } catch (error) {
                console.error('Hangup error:', error);
                showNotification('WebPhone: Sonlandırma hatası', 'error');
            }
        } else {
            showNotification('WebPhone: Aktif görüşme bulunamadı', 'warning');
        }
    }

    function handleIncomingCall(invitation) {
        const callerNumber = invitation.remoteIdentity.uri.user;
        
        if (webphoneConfig.autoAnswer) {
            invitation.accept().then(() => {
                webphoneSession = invitation;
                showNotification(`WebPhone: ${callerNumber} otomatik yanıtlandı`, 'success');
                updateWebPhoneStatus('Görüşmede ✓');
            });
        } else {
            if (confirm(`WebPhone: ${callerNumber} arıyor. Yanıtlamak istiyor musunuz?`)) {
                invitation.accept().then(() => {
                    webphoneSession = invitation;
                    showNotification(`WebPhone: ${callerNumber} yanıtlandı`, 'success');
                    updateWebPhoneStatus('Görüşmede ✓');
                });
            } else {
                invitation.reject();
                showNotification(`WebPhone: ${callerNumber} reddedildi`, 'info');
            }
        }
    }

    // WebPhone reconnection
    function reconnectWebPhone() {
        if (userAgent && userAgent.state === SIP.UserAgentState.Stopped) {
            console.log('Attempting WebPhone reconnection...');
            showNotification('WebPhone: Yeniden bağlanılıyor...', 'info');
            updateWebPhoneStatus('Yeniden bağlanıyor...');
            initializeWebPhone();
        }
    }

    // WebPhone Functions
    function toggleWebphone() {
        const container = document.getElementById('webphoneContainer');
        webphoneActive = !webphoneActive;
        
        if (webphoneActive) {
            container.classList.add('active');
        } else {
            container.classList.remove('active');
        }
    }
    
    function addDigit(digit) {
        const dialNumber = document.getElementById('dialNumber');
        dialNumber.value += digit;
    }
    
    function clearDialNumber() {
        document.getElementById('dialNumber').value = '';
    }
    
    function makeWebphoneCall() {
        const number = document.getElementById('dialNumber').value;
        if (number.trim() === '') {
            showNotification('WebPhone: Lütfen telefon numarası giriniz', 'warning');
            return;
        }
        
        const cleanNumber = number.replace(/[^\d]/g, '');
        if (cleanNumber.length < 3) {
            showNotification('WebPhone: Geçersiz telefon numarası', 'warning');
            return;
        }
        
        makeWebRTCCall(cleanNumber);
    }
    
    function hangupWebphoneCall() {
        hangupWebRTCCall();
    }

    // Enhanced Call Functions
    function makeCall(phoneNumber, leadId) {
        if (callActive) {
            showNotification('Zaten aktif bir çağrınız var. Önce mevcut çağrıyı sonlandırın.', 'warning');
            return;
        }
        
        if (confirm('Bu numarayı aramak istediğinize emin misiniz?\n' + phoneNumber)) {
            currentPhoneNumber = phoneNumber;
            currentLeadId = leadId;
            callActive = true;
            
            // WebPhone numarasını güncelle
            document.getElementById('dialNumber').value = phoneNumber;
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '?action=manual_dial&phone_number=' + phoneNumber + '&lead_id=' + leadId + '&ajax=1', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                showNotification('Arama başlatıldı: ' + phoneNumber + '<br>Müşteri: ' + response.customer, 'success');
                                updateCallCount();
                                updateAgentStatus('INCALL');
                                showHangupControls();
                            } else {
                                callActive = false;
                                showNotification('Arama başlatılamadı: ' + response.message, 'error');
                            }
                        } catch (e) {
                            showNotification('Arama başlatıldı: ' + phoneNumber, 'success');
                            updateAgentStatus('INCALL');
                            showHangupControls();
                        }
                    } else {
                        callActive = false;
                        showNotification('Bağlantı hatası', 'error');
                    }
                }
            };
            xhr.send();
        }
    }
    
    function hangupCall() {
        if (!callActive) {
            showNotification('Aktif çağrı bulunamadı', 'warning');
            return;
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?action=hangup_call&ajax=1', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        callActive = false;
                        hideHangupControls();
                        updateAgentStatus('PAUSED');
                        
                        if (response.show_qc_modal) {
                            var qcModal = new bootstrap.Modal(document.getElementById('qcResultModal'));
                            qcModal.show();
                        }
                        
                        showNotification('Çağrı sonlandırıldı', 'success');
                    }
                } catch (e) {
                    callActive = false;
                    hideHangupControls();
                    updateAgentStatus('PAUSED');
                    showNotification('Çağrı sonlandırıldı', 'success');
                    
                    var qcModal = new bootstrap.Modal(document.getElementById('qcResultModal'));
                    qcModal.show();
                }
            }
        };
        xhr.send();
    }
    
    function showHangupControls() {
        const hangupBtn = document.createElement('button');
        hangupBtn.id = 'floatingHangupBtn';
        hangupBtn.className = 'btn btn-hangup position-fixed';
        hangupBtn.style.cssText = 'bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1050; box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);';
        hangupBtn.innerHTML = '<i class="fas fa-phone-slash me-2"></i>Çağrıyı Sonlandır';
        hangupBtn.onclick = hangupCall;
        document.body.appendChild(hangupBtn);
    }
    
    function hideHangupControls() {
        const hangupBtn = document.getElementById('floatingHangupBtn');
        if (hangupBtn) {
            hangupBtn.remove();
        }
    }

    function changeStatus(newStatus) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?action=change_status&status=' + newStatus + '&ajax=1', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        updateAgentStatus(response.agent_status);
                        showNotification('Durum güncellendi: ' + response.agent_status, 'success');
                    }
                } catch (e) {
                    updateAgentStatus(newStatus);
                    showNotification('Durum güncellendi: ' + newStatus, 'success');
                }
            }
        };
        xhr.send();
    }

    function toggleRecording() {
        var action = currentRecordingStatus === 'OFF' ? 'recording_start' : 'recording_stop';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?action=' + action + '&ajax=1', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        currentRecordingStatus = response.recording;
                        updateRecordingStatus();
                        showNotification(response.message, 'success');
                    }
                } catch (e) {
                    currentRecordingStatus = currentRecordingStatus === 'OFF' ? 'ON' : 'OFF';
                    updateRecordingStatus();
                    showNotification('Kayıt durumu değiştirildi', 'success');
                }
            }
        };
        xhr.send();
    }

    function updateAgentStatus(status) {
        agentStatus = status;
        const statusElement = document.getElementById('agent-status');
        
        statusElement.className = 'agent-status-badge';
        statusElement.classList.add('status-' + status);
        statusElement.textContent = status;
    }

    function updateRecordingStatus() {
        const recordingElement = document.getElementById('recording-status');
        const recordBtn = document.getElementById('record-btn');
        
        recordingElement.textContent = currentRecordingStatus;
        recordingElement.className = 'fw-bold ' + (currentRecordingStatus === 'ON' ? 'text-danger' : 'text-secondary');
        
        if (currentRecordingStatus === 'ON') {
            recordBtn.className = 'btn btn-danger btn-sm';
            recordBtn.innerHTML = '<i class="fas fa-stop"></i>';
            recordBtn.title = 'Stop Recording';
        } else {
            recordBtn.className = 'btn btn-info btn-sm';
            recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            recordBtn.title = 'Start Recording';
        }
    }

    function updateCallCount() {
        const countElement = document.getElementById('calls-count');
        const currentCount = parseInt(countElement.textContent) || 0;
        countElement.textContent = currentCount + 1;
    }

    function saveQCResult(result) {
        if (!currentLeadId) {
            showNotification('Lead ID bulunamadı', 'error');
            return;
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?action=save_qc_result&qc_result=' + result + '&lead_id=' + currentLeadId + '&phone_number=' + currentPhoneNumber + '&ajax=1', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showNotification(response.message, 'success');
                        updateAgentStatus('READY');
                        
                        var qcModal = bootstrap.Modal.getInstance(document.getElementById('qcResultModal'));
                        if (qcModal) {
                            qcModal.hide();
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } catch (e) {
                    showNotification('QC sonucu kaydedildi: ' + result, 'success');
                    var qcModal = bootstrap.Modal.getInstance(document.getElementById('qcResultModal'));
                    if (qcModal) {
                        qcModal.hide();
                    }
                    setTimeout(() => window.location.reload(), 2000);
                }
            }
        };
        xhr.send();
    }

    function showDetails(recordId) {
        showNotification('Detay gösterimi: ID ' + recordId + '<br>Bu özellik geliştirme aşamasında.', 'info');
    }

    function showNotification(message, type = 'info') {
        const iconMap = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle', 
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        const colorMap = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning', 
            'info': 'alert-info'
        };
        
        const notification = document.createElement('div');
        notification.className = `alert ${colorMap[type]} alert-dismissible fade show notification`;
        notification.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 350px; max-width: 400px;';
        notification.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <i class="fas ${iconMap[type]} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        document.body.appendChild(notification);
        
        setTimeout(function() {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 8000);
    }

    function checkAgentStatus() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?action=status_check&ajax=1', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        updateAgentStatus(response.agent_status);
                        document.getElementById('calls-count').textContent = response.calls_today;
                        currentRecordingStatus = response.recording;
                        updateRecordingStatus();
                    }
                } catch (e) {
                    // Sessiz hata
                }
            }
        };
        xhr.send();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            switch(e.key) {
                case '1':
                    e.preventDefault();
                    changeStatus('READY');
                    break;
                case '2':
                    e.preventDefault();
                    changeStatus('PAUSED');
                    break;
                case 'r':
                    e.preventDefault();
                    toggleRecording();
                    break;
                case 'h':
                    e.preventDefault();
                    if (callActive) hangupCall();
                    break;
                case 'w':
                    e.preventDefault();
                    toggleWebphone();
                    break;
            }
        }
        
        if (e.key === 'Escape' && webphoneActive) {
            toggleWebphone();
        }
    });

    // Page load initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Session management'ı başlat
        initSessionManagement();
        
        updateRecordingStatus();
        
        // Status kontrolü her 15 saniyede
        setInterval(checkAgentStatus, 15000);
        
        // WebPhone connection check every 30 seconds
        setInterval(() => {
            if (userAgent && userAgent.state === SIP.UserAgentState.Stopped && webphoneRegistered) {
                webphoneRegistered = false;
                updateWebPhoneStatus('Bağlantı kesildi');
                reconnectWebPhone();
            }
        }, 30000);
        
        // Keyboard shortcuts bilgisi
        setTimeout(() => {
            showNotification(`
                <strong>Klavye Kısayolları:</strong><br>
                Ctrl+1: Ready | Ctrl+2: Pause | Ctrl+R: Record<br>
                Ctrl+H: Hangup | Ctrl+W: WebPhone | ESC: Close WebPhone
            `, 'info');
        }, 1000);
        
        // WebPhone başlangıçta kapalı
        const webphoneContainer = document.getElementById('webphoneContainer');
        webphoneContainer.classList.remove('active');
        
        // WebPhone'u başlat
        setTimeout(() => {
            initializeWebPhone();
        }, 3000);
        
        // Dialpad color'ı uygula
        <?php if (!empty($webphone_dialpad_color)): ?>
        const style = document.createElement('style');
        style.textContent = `
            .dial-button {
                background: <?php echo $webphone_dialpad_color; ?> !important;
            }
        `;
        document.head.appendChild(style);
        <?php endif; ?>
    });

    // Page visibility handling
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            document.title = '💤 ' + document.title.replace('💤 ', '');
        } else {
            document.title = document.title.replace('💤 ', '');
        }
    });

    // Enhanced error handling
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
        showNotification('Bir hata oluştu. Lütfen sayfayı yenileyin.', 'error');
    });

    // Connection monitoring
    let connectionCheckInterval;
    function startConnectionMonitoring() {
        connectionCheckInterval = setInterval(() => {
            fetch('?action=status_check&ajax=1')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    document.body.classList.remove('connection-lost');
                })
                .catch(error => {
                    console.error('Connection error:', error);
                    document.body.classList.add('connection-lost');
                    showNotification('Bağlantı problemi tespit edildi', 'warning');
                });
        }, 30000);
    }

    startConnectionMonitoring();

    // Add CSS for additional effects
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .status-INCALL {
            animation: pulse 2s infinite;
        }
        
        .connection-lost {
            filter: grayscale(0.5);
        }
        
        .connection-lost::before {
            content: "Bağlantı Problemi";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ef4444;
            color: white;
            text-align: center;
            padding: 0.5rem;
            z-index: 9999;
            font-weight: 600;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(37, 99, 235, 0.05) !important;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>