<?php
# Enhanced QC.php - VICIdial QC Panel with WebPhone Integration
# Geliştirilmiş Kalite Kontrol Paneli - WebPhone ve gelişmiş özelliklerle

$version = '2.0-001';
$build = '241220-2000';

require_once("../agc/dbconnect_mysqli.php");
require_once("../agc/functions.php");

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

// POST verilerini al
if (isset($_POST["VD_login"]))      {$VD_login=$_POST["VD_login"];}
if (isset($_POST["VD_pass"]))       {$VD_pass=$_POST["VD_pass"];}
if (isset($_POST["phone_login"]))   {$phone_login=$_POST["phone_login"];}
if (isset($_POST["phone_pass"]))    {$phone_pass=$_POST["phone_pass"];}
if (isset($_POST["webphone"]))      {$webphone=$_POST["webphone"];}
echo "<!-- DEBUG: Login=$VD_login, Pass=".substr($VD_pass,0,3)."*** -->";

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
    header("Location: ../index.php");
    exit;
}

// Kullanıcı doğrulaması
$auth = 0;

$auth_message = user_authorization($VD_login,$VD_pass,'',1,0,1,0);

if (preg_match("/^GOOD/",$auth_message)) {
    $auth=1;

}

if ($auth == 0) {
  
    header("Location: ../index.php");
    exit;
}

// Kullanıcı bilgilerini al
$stmt="SELECT full_name,user_level,user_group from vicidial_users where user='$VD_login' and active='Y';";
$rslt=mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row=mysqli_fetch_row($rslt);
    $full_name = $row[0];
    $user_level = $row[1];
    $user_group = $row[2];

    
    // QC yetkisi kontrolü (Level 7+)
    if ($user_level < 7) {
        header("Location: ../index.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
$stmt="SELECT allowed_campaigns from vicidial_user_groups where user_group='$user_group' limit 1;";
$rslt=mysql_to_mysqli($stmt, $link);
 $row=mysqli_fetch_row($rslt);
 $allowed_campaigns = $row[0];

// WebPhone ayarlarını çek
$webphone_url = '';
$webphone_dialpad_color = '';
$webphone_location = 'right';
$phone_server_ip = '';
$webphone_auto_answer = 'N';
// Sistem ayarlarından webphone bilgilerini al
$stmt = "SELECT webphone_url FROM system_settings LIMIT 1;";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    $webphone_url = $row[0]; 
  }

$stmt = "SELECT web_socket_url FROM servers LIMIT 1;";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    $web_socket_url = $row[0]; 
  }

// Kullanıcının phone bilgilerini tekrar çek (webphone için)
$stmt = "SELECT login,pass,phone_ip,phone_context,webphone_auto_answer,server_ip FROM phones WHERE dialplan_number='$phone_login' AND active='Y';";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    $user_phone_login = $row[0];
    $user_phone_pass = $row[1];
    $user_phone_ip = $row[2];
    $user_phone_context = $row[3];
    if ($row[4] != '') { $webphone_auto_answer = $row[4]; }
    $phone_server_ip = $row[5];
   
  }

// WebPhone server bilgilerini al
$webphone_server = '';
$webphone_protocol = 'SIP';
$webphone_extension = $phone_login;

$stmt = "SELECT server_ip,web_socket_url FROM servers WHERE server_ip='$server_ip' OR active='Y' LIMIT 1;";
$rslt = mysql_to_mysqli($stmt, $link);
if (mysqli_num_rows($rslt) > 0) {
    $row = mysqli_fetch_row($rslt);
    $webphone_server = $row[0];
    if ($row[1] != '') {  $websocket_url= $row[1]; }
   
}

// WebRTC/SIP ayarları
$sip_domain =  $server_ip;




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

// Manuel arama action'ı
if ($action == 'manual_dial' && $phone_number) {
    // Lead bilgilerini kontrol et
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
    
    // Session güncelle
    $stmt = "UPDATE vicidial_live_agents SET status='INCALL',lead_id='$found_lead_id',campaign_id='$lead_campaign',last_call_time='$NOW_TIME',calls_today=calls_today+1 WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    // Call log kayıt
    $uniqueid = $CIDdate . '_' . $VD_login;
    $stmt = "INSERT INTO call_log (uniqueid,channel,channel_group,type,server_ip,extension,number_dialed,caller_code,start_time,start_epoch,end_time,end_epoch,length_in_sec,length_in_min,adapter,lead_id,phone_code,phone_number,user,comments) VALUES('$uniqueid','Local/$phone_login','QC_MANUAL','OUT','$server_ip','$phone_login','$phone_number','QC','$NOW_TIME','$StarTtimE','$NOW_TIME','$StarTtimE','0','0','QC_Panel','$found_lead_id','1','$phone_number','$VD_login','QC_Manual_Dial');";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Arama başlatıldı","phone_number":"'.$phone_number.'","lead_id":"'.$found_lead_id.'","customer":"'.$customer_name.'","uniqueid":"'.$uniqueid.'"}';
        exit;
    }
}

// Çağrı sonlandırma action'ı
if ($action == 'hangup_call') {
    $stmt = "UPDATE vicidial_live_agents SET status='PAUSED',lead_id='',last_call_finish='$NOW_TIME' WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"Çağrı sonlandırıldı","show_qc_modal":true}';
        exit;
    }
}

// QC Sonuç kaydetme
if ($action == 'save_qc_result' && $qc_result && $lead_id) {
    // QC sonuç kodlarını tanımla
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
        // A_ozel_tablo'da QC sonucunu güncelle
        $qc_description = $qc_codes[$qc_result];
        $stmt = "UPDATE A_ozel_tablo SET qc_result='$qc_result', qc_description='$qc_description', qc_agent='$VD_login', qc_date='$NOW_TIME' WHERE lead_id='$lead_id';";
        $rslt = mysql_to_mysqli($stmt, $link);
        
        // Log kayıt
        $stmt = "INSERT INTO vicidial_agent_log (user,event,campaign_id,event_date,event_epoch,lead_id,phone_number,user_group) VALUES('$VD_login','QC_RESULT','QC','$NOW_TIME','$StarTtimE','$lead_id','$phone_number','$user_group');";
        $rslt = mysql_to_mysqli($stmt, $link);
        
        // Agent durumunu READY yap
        $stmt = "UPDATE vicidial_live_agents SET status='READY' WHERE user='$VD_login';";
        $rslt = mysql_to_mysqli($stmt, $link);
    }
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo '{"status":"success","message":"QC sonucu kaydedildi: '.$qc_description.'","qc_result":"'.$qc_result.'"}';
        exit;
    }
}

// Recording control
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

// Agent status değiştirme
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

// Status kontrol
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

// Logout işlemi
if ($action == 'logout') {
    $stmt = "DELETE FROM vicidial_live_agents WHERE user='$VD_login';";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    $stmt = "INSERT INTO vicidial_user_log (user,event,campaign_id,event_date,event_epoch,user_group,session_id) values('$VD_login','LOGOUT','QC','$NOW_TIME','$StarTtimE','$user_group','$session_name');";
    $rslt = mysql_to_mysqli($stmt, $link);
    
    header("Location: ../index.php");
    exit;
}

// Filtre oluşturma
$search_filter = "WHERE lead_id>0 ";
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

// QC sonuç filtresi
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

header ("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VICIdial QC Panel - Enhanced</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/jssip@3.10.1/dist/jssip.min.js"></script>
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
            right: 20px;
            width: 300px;
            height: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            transition: all 0.3s ease;
            transform: translateX(320px);
        }

        .webphone-container.active {
            transform: translateX(0);
        }

        .webphone-header {
            background: var(--gradient-bg);
            color: white;
            padding: 1rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .webphone-content {
            padding: 1rem;
        }

        .dial-pad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 1rem 0;
        }

        .dial-button {
            aspect-ratio: 1;
            border: none;
            border-radius: 10px;
            background: var(--gradient-bg);
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .dial-button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-clipboard-check me-2"></i>VICIdial QC Panel
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
                    <button class="btn btn-primary btn-sm" onclick="toggleWebphone()" title="WebPhone">
                        <i class="fas fa-phone"></i>
                    </button>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-primary" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($full_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">Level: <?php echo $user_level; ?></span></li>
                            <li><span class="dropdown-item-text">Phone: <?php echo $phone_login; ?></span></li>
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
    <div class="webphone-container" id="webphoneContainer">
        <div class="webphone-header">
            <div>
                <i class="fas fa-phone me-2"></i>WebPhone
            </div>
            <button class="btn btn-sm btn-outline-light" onclick="toggleWebphone()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="webphone-content">
            <div class="mb-3">
                <input type="text" class="form-control" id="dialNumber" placeholder="Telefon numarası" maxlength="15">
            </div>
            <div class="dial-pad">
                <button class="dial-button" onclick="addDigit('1')">1</button>
                <button class="dial-button" onclick="addDigit('2')">2</button>
                <button class="dial-button" onclick="addDigit('3')">3</button>
                <button class="dial-button" onclick="addDigit('4')">4</button>
                <button class="dial-button" onclick="addDigit('5')">5</button>
                <button class="dial-button" onclick="addDigit('6')">6</button>
                <button class="dial-button" onclick="addDigit('7')">7</button>
                <button class="dial-button" onclick="addDigit('8')">8</button>
                <button class="dial-button" onclick="addDigit('9')">9</button>
                <button class="dial-button" onclick="addDigit('*')">*</button>
                <button class="dial-button" onclick="addDigit('0')">0</button>
                <button class="dial-button" onclick="addDigit('#')">#</button>
            </div>
            <div class="d-grid gap-2">
                <button class="btn btn-success" onclick="makeWebphoneCall()">
                    <i class="fas fa-phone me-2"></i>Ara
                </button>
                <button class="btn btn-danger" onclick="hangupWebphoneCall()">
                    <i class="fas fa-phone-slash me-2"></i>Kapat
                </button>
                <button class="btn btn-secondary" onclick="clearDialNumber()">
                    <i class="fas fa-backspace me-2"></i>Temizle
                </button>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    WebPhone Extension: <?php echo $phone_login; ?>
                </small>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
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
                <form method="GET" class="row g-3">
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
                    <a href="?" class="btn btn-outline-secondary btn-sm">
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

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Sayfa navigasyonu" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $current_url = $_SERVER['REQUEST_URI'];
                $current_url = preg_replace('/[?&]page=\d+/', '', $current_url);
                $separator = (strpos($current_url, '?') !== false) ? '&' : '?';
                
                for ($i = 1; $i <= $total_pages; $i++): 
                ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $current_url . $separator . 'page=' . $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
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
    
    <!-- Enhanced JavaScript -->
    <script>
    var currentRecordingStatus = '<?php echo $recording_status; ?>';
    var agentStatus = '<?php echo $agent_status; ?>';
    var currentLeadId = '';
    var currentPhoneNumber = '';
    var webphoneActive = false;
    var callActive = false;
    
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
            showNotification('Lütfen telefon numarası giriniz', 'warning');
            return;
        }
        makeCall(number, '');
    }
    
    function hangupWebphoneCall() {
        hangupCall();
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
                                
                                // Show hangup button
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
                            // Show QC result modal
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
                    
                    // Show QC modal after hangup
                    var qcModal = new bootstrap.Modal(document.getElementById('qcResultModal'));
                    qcModal.show();
                }
            }
        };
        xhr.send();
    }
    
    function showHangupControls() {
        // Add floating hangup button
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
        
        // Remove all status classes
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
                        
                        // Close modal
                        var qcModal = bootstrap.Modal.getInstance(document.getElementById('qcResultModal'));
                        qcModal.hide();
                        
                        // Refresh page after 2 seconds
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                } catch (e) {
                    showNotification('QC sonucu kaydedildi: ' + result, 'success');
                    var qcModal = bootstrap.Modal.getInstance(document.getElementById('qcResultModal'));
                    qcModal.hide();
                    setTimeout(() => location.reload(), 2000);
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

    // Status check ve auto-update
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
        
        // ESC to close webphone
        if (e.key === 'Escape' && webphoneActive) {
            toggleWebphone();
        }
    });

    // Page load initialization
    document.addEventListener('DOMContentLoaded', function() {
        updateRecordingStatus();
        
        // Status kontrolü her 15 saniyede
        setInterval(checkAgentStatus, 15000);
        
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
        
        <?php if ($webphone == '1'): ?>
        // WebPhone aktif ise otomatik aç
        setTimeout(() => {
            showNotification('WebPhone aktif - QC Panel hazır', 'success');
        }, 2000);
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

    // Auto-refresh table every 2 minutes (only if no modal is open)
    setInterval(function() {
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length === 0 && !callActive) {
            // Soft refresh - only update table content
            location.reload();
        }
    }, 120000);

    // Enhanced error handling
  window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    
    // Sadece kritik hataları yakala
    if (e.error && e.error.name === 'TypeError' && e.error.message.includes('SIP')) {
        showNotification('WebPhone hatası: SIP kütüphanesi yüklenemedi', 'warning');
        return;
    }
    
    // Diğer hataları görmezden gel veya sadece console'da logla
    console.warn('JavaScript error logged, but not critical:', e.error);
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
                    // Connection is good
                    document.body.classList.remove('connection-lost');
                })
                .catch(error => {
                    console.error('Connection error:', error);
                    document.body.classList.add('connection-lost');
                    showNotification('Bağlantı problemi tespit edildi', 'warning');
                });
        }, 30000); // Check every 30 seconds
    }

    // Start connection monitoring
    startConnectionMonitoring();

    // Modern UI enhancements
    function addModernTouchesToElements() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--card-shadow)';
            });
        });

        // Add ripple effect to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.height, rect.width);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
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
    `;
    document.head.appendChild(style);

    // Apply modern touches
    setTimeout(addModernTouchesToElements, 500);
    </script>

    <!-- WebPhone Integration Script -->
    <?php if ($webphone == '1'): ?>
    <script>
    // WebPhone Integration - SIP.js or WebRTC implementation would go here
   let webphoneSession = null;
let webphoneRegistered = false;
let userAgent = null;

// MySQL'den alınan WebPhone ayarları
// SIP.js yerine JsSIP kullan
const webphoneConfig = {
    extension: '<?php echo $webphone_extension; ?>',
    password: '<?php echo $phone_pass; ?>',
    server: '<?php echo $sip_domain; ?>',
    websocketUrl: 'wss://<?php echo $sip_domain; ?>:8089/ws'
};

let ua = null;
let session = null;

  



function initializeWebPhone() {
 console.log('WebPhone temporarily disabled for testing');
    showNotification('WebPhone: Test için devre dışı', 'info');
    return;
     if (typeof SIP === 'undefined') {
        console.log('SIP.js not available, skipping WebPhone initialization');
        showNotification('WebPhone: SIP kütüphanesi yüklenemedi', 'warning');
        return;
    }
    console.log('Initializing WebPhone with JsSIP...');
    
    const socket = new JsSIP.WebSocketInterface(webphoneConfig.websocketUrl);
    const configuration = {
        sockets: [socket],
        uri: `sip:${webphoneConfig.extension}@${webphoneConfig.server}`,
        password: webphoneConfig.password,
        display_name: '<?php echo $full_name; ?>'
    };

    ua = new JsSIP.UA(configuration);

    ua.on('connecting', function(e) {
        console.log('WebPhone connecting...');
        showNotification('WebPhone: Bağlanıyor...', 'info');
    });

    ua.on('connected', function(e) {
        console.log('WebPhone connected');
        showNotification('WebPhone: Bağlandı', 'success');
    });

    ua.on('registered', function(e) {
        webphoneRegistered = true;
        showNotification('WebPhone: Kayıtlı', 'success');
        updateWebPhoneStatus('registered');
    });

    ua.on('registrationFailed', function(e) {
        console.error('Registration failed:', e);
        showNotification('WebPhone: Kayıt başarısız', 'error');
    });

    ua.on('newRTCSession', function(e) {
        session = e.session;
        if (session.direction === 'incoming') {
            handleIncomingCall(session);
        }
    });

    ua.start();
}

function makeWebRTCCall(number) {
    if (!ua || !ua.isRegistered()) {
        showNotification('WebPhone: Kayıtlı değil', 'error');
        return;
    }

    const target = `sip:${number}@${webphoneConfig.server}`;
    const options = {
        mediaConstraints: { audio: true, video: false }
    };

    session = ua.call(target, options);
    
    session.on('progress', function() {
        showNotification(`Aranıyor: ${number}`, 'info');
    });
    
    session.on('confirmed', function() {
        showNotification(`Görüşme başladı: ${number}`, 'success');
        updateWebPhoneStatus('in_call');
    });
    
    session.on('ended', function() {
        showNotification('Görüşme sonlandı', 'info');
        updateWebPhoneStatus('registered');
        session = null;
    });
}

function hangupWebRTCCall() {
    if (session) {
        session.terminate();
        session = null;
        showNotification('Görüşme sonlandırıldı', 'info');
    }
}
function handleIncomingCall(invitation) {
    const callerNumber = invitation.remoteIdentity.uri.user;
    
    // Auto answer if enabled
    if (webphoneConfig.autoAnswer) {
        invitation.accept().then(() => {
            webphoneSession = invitation;
            showNotification(`WebPhone: ${callerNumber} otomatik yanıtlandı`, 'success');
            updateWebPhoneStatus('in_call');
        });
    } else {
        // Show incoming call notification
        if (confirm(`WebPhone: ${callerNumber} arıyor. Yanıtlamak istiyor musunuz?`)) {
            invitation.accept().then(() => {
                webphoneSession = invitation;
                showNotification(`WebPhone: ${callerNumber} yanıtlandı`, 'success');
                updateWebPhoneStatus('in_call');
            });
        } else {
            invitation.reject();
            showNotification(`WebPhone: ${callerNumber} reddedildi`, 'info');
        }
    }
}

function updateWebPhoneStatus(status) {
    const statusMap = {
        'registered': { text: 'Kayıtlı', class: 'text-success' },
        'in_call': { text: 'Görüşmede', class: 'text-info' },
        'registration_failed': { text: 'Kayıt Hatası', class: 'text-danger' },
        'start_failed': { text: 'Başlatma Hatası', class: 'text-danger' },
        'init_failed': { text: 'Başlatma Hatası', class: 'text-danger' }
    };
    
    const statusInfo = statusMap[status] || { text: 'Bilinmiyor', class: 'text-secondary' };
    
    // Update webphone header
    const webphoneHeader = document.querySelector('.webphone-header div');
    if (webphoneHeader) {
        webphoneHeader.innerHTML = `<i class="fas fa-phone me-2"></i>WebPhone <small class="${statusInfo.class}">(${statusInfo.text})</small>`;
    }
}

// Override existing makeWebphoneCall function
function makeWebphoneCall() {
    const number = document.getElementById('dialNumber').value;
    if (number.trim() === '') {
        showNotification('WebPhone: Lütfen telefon numarası giriniz', 'warning');
        return;
    }
    
    // Clean number (remove non-digits)
    const cleanNumber = number.replace(/[^\d]/g, '');
    if (cleanNumber.length < 3) {
        showNotification('WebPhone: Geçersiz telefon numarası', 'warning');
        return;
    }
    
    makeWebRTCCall(cleanNumber);
}

// Override existing hangupWebphoneCall function  
function hangupWebphoneCall() {
    hangupWebRTCCall();
}

// Initialize WebPhone when document is ready
document.addEventListener('DOMContentLoaded', function() {

     
    // Delay initialization to ensure everything is loaded
    setTimeout(() => {
        <?php if ($webphone == '1' || !empty($webphone_url)): ?>
        initializeWebPhone();
        <?php else: ?>
        console.log('WebPhone disabled in system settings');
        showNotification('WebPhone: Sistem ayarlarında devre dışı', 'info');
        <?php endif; ?>
    }, 3000);
});

// WebPhone reconnection on connection loss
function reconnectWebPhone() {
    if (userAgent && userAgent.state === SIP.UserAgentState.Stopped) {
        console.log('Attempting WebPhone reconnection...');
        showNotification('WebPhone: Yeniden bağlanılıyor...', 'info');
        initializeWebPhone();
    }
}

// Check WebPhone connection every 30 seconds
setInterval(() => {
    if (userAgent && userAgent.state === SIP.UserAgentState.Stopped && webphoneRegistered) {
        webphoneRegistered = false;
        updateWebPhoneStatus('registration_failed');
        reconnectWebPhone();
    }
}, 30000);

// Add dialpad color styling if configured
<?php if (!empty($webphone_dialpad_color)): ?>
document.addEventListener('DOMContentLoaded', function() {
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.log('WebRTC not supported');
        document.querySelector('.btn-primary[onclick="toggleWebphone()"]').style.display = 'none';
        return;
    }
    const style = document.createElement('style');
    style.textContent = `
        .dial-button {
            background: <?php echo $webphone_dialpad_color; ?> !important;
        }
    `;
    document.head.appendChild(style);
});
<?php endif; ?>
</script>
<?php endif; ?>
    <!-- Additional CSS for enhanced animations -->
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-call:active, .btn-hangup:active {
            animation: pulse 0.3s ease-in-out;
        }
        
        .webphone-container {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .agent-status-badge {
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        .status-INCALL {
            animation: pulse 1s infinite;
        }
        
        .qc-result-badge {
            transition: all 0.3s ease;
        }
        
        .qc-result-badge:hover {
            transform: scale(1.1);
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(37, 99, 235, 0.05) !important;
            transform: translateX(5px);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive Design Enhancements */
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .navbar-nav .nav-item .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-sm {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }
        }
        
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }
            
            .card {
                border-radius: 10px;
            }
            
            .stats-card {
                padding: 1rem;
                text-align: center;
            }
            
            .row .col-md-3 {
                margin-bottom: 1rem;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --gradient-bg: linear-gradient(135deg, #1e293b 0%, #374151 100%);
            }
            
            .card {
                background: rgba(31, 41, 55, 0.95);
                color: white;
            }
            
            .table {
                color: white;
            }
            
            .table tbody tr:hover {
                background: rgba(59, 130, 246, 0.1) !important;
            }
            
            .form-control, .form-select {
                background: rgba(55, 65, 81, 0.8);
                border-color: rgba(75, 85, 99, 0.6);
                color: white;
            }
            
            .navbar {
                background: rgba(31, 41, 55, 0.95);
            }
        }
<?php if ($webphone_location == 'bar'): ?>
.webphone-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    top: auto;
    width: 100%;
    height: 200px;
    transform: translateY(200px);
}

.webphone-container.active {
    transform: translateY(0);
}
<?php endif; ?>

.webphone-status {
    font-size: 0.8rem;
    margin-top: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    background: rgba(0,0,0,0.1);
}
    </style>
</body>
</html>
