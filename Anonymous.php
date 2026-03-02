<?php
session_start();
include 'db.php';

$error      = '';
$success    = '';
$problem    = '';
$area       = '';
$councillor = '';
$areaList   = [];

$resAreas = $pdo->query("SELECT area_name FROM area_heads ORDER BY area_name");
foreach ($resAreas->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $areaList[] = $r['area_name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aadhar_no  = $_SESSION['aadhar_no'] ?? null;
    $problem    = trim($_POST['problem']    ?? '');
    $area       = trim($_POST['area']       ?? '');
    $councillor = trim($_POST['councillor'] ?? '');

    if (!empty($_SERVER['HTTP_CLIENT_IP']))         $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else                                             $ip = $_SERVER['REMOTE_ADDR'];

    if (empty($problem) || empty($area) || empty($councillor)) {
        $error = "Please fill all fields.";
    } else {
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $allowedTypes = ['image/jpeg','image/png','image/webp'];
            if ($_FILES['image']['error'] !== 0)                          $error = "Image upload error.";
            elseif (!in_array($_FILES['image']['type'], $allowedTypes))   $error = "Invalid image type.";
            elseif ($_FILES['image']['size'] > 10*1024*1024)              $error = "Image size exceeds 10MB.";
            elseif (!getimagesize($_FILES['image']['tmp_name']))           $error = "File is not an image.";
            else {
                $ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $dest = "uploads/" . uniqid("img_", true) . "." . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) $imagePath = $dest;
                else $error = "Failed to save image.";
            }
        }

        if (empty($error)) {
            $stmt_check = $pdo->prepare("SELECT id FROM feedback WHERE ip_address = :ip AND created_at >= NOW() - INTERVAL '1 hour'");
            $stmt_check->execute([':ip' => $ip]);
            if ($stmt_check->rowCount() > 0) {
                $error = "You can only submit feedback once per hour.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO feedback (problem, area_name, municipalhead_name, status, ip_address, aadhar_no, image_path) VALUES (:problem, :area, :councillor, 'Pending', :ip, :aadhar, :image)");
                if ($stmt->execute([':problem'=>$problem,':area'=>$area,':councillor'=>$councillor,':ip'=>$ip,':aadhar'=>$aadhar_no,':image'=>$imagePath])) {
                    $success    = "Feedback submitted successfully!";
                    $problem    = $area = $councillor = '';
                } else {
                    $error = "Error submitting feedback.";
                }
            }
        }
    }
}
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Feedback - Municipal Service Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#0f172a;min-height:100vh;padding:1.5rem;color:#e2e8f0;line-height:1.6;position:relative;overflow-x:hidden;}
body::before,body::after{content:'';position:fixed;border-radius:50%;filter:blur(120px);pointer-events:none;}
body::before{width:400px;height:400px;top:-100px;left:15%;background:rgba(59,130,246,0.07);}
body::after{width:350px;height:350px;bottom:-80px;right:10%;background:rgba(34,197,94,0.05);}
.wrapper{max-width:640px;margin:0 auto;position:relative;z-index:1;}
.top-bar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;}
.top-bar-left{display:flex;align-items:center;gap:0.75rem;}
.top-bar-icon{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:12px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);}
.top-bar-icon svg{width:20px;height:20px;color:#3b82f6;}
.top-bar-text h1{font-size:1.25rem;font-weight:700;color:#f1f5f9;}
.top-bar-text p{font-size:0.8125rem;color:#94a3b8;}
.top-bar-right{display:flex;align-items:center;gap:0.75rem;}
.user-name{font-size:0.8125rem;color:#94a3b8;}
.user-name strong{color:#f1f5f9;font-weight:500;}
.btn-back{padding:0.375rem 0.875rem;border-radius:8px;font-size:0.8125rem;font-weight:600;font-family:inherit;border:none;cursor:pointer;background:rgba(239,68,68,0.12);color:#f87171;transition:background 0.2s;text-decoration:none;display:inline-block;}
.btn-back:hover{background:rgba(239,68,68,0.2);}
.glass-wrap{padding:6px;border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.1);}
.card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);}
.card-header{padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#f8fafc;display:flex;align-items:center;gap:0.5rem;}
.card-header svg{width:20px;height:20px;color:#3b82f6;}
.card-header h2{font-size:1.125rem;font-weight:600;color:#1e293b;}
.card-body{padding:1.5rem;}
.form-group{margin-bottom:1.25rem;}
.form-group label{display:block;font-size:0.8125rem;font-weight:600;color:#1e293b;margin-bottom:0.375rem;}
.input-wrap{position:relative;}
.input-wrap>svg{position:absolute;left:12px;top:14px;width:18px;height:18px;color:#94a3b8;pointer-events:none;z-index:1;}
.input-wrap input,.input-wrap textarea,.input-wrap select{width:100%;padding:0.75rem 0.75rem 0.75rem 2.5rem;border:2px solid #e2e8f0;border-radius:10px;font-size:0.875rem;font-family:inherit;color:#1e293b;background:#fff;outline:none;transition:border-color 0.2s;resize:none;}
.input-wrap textarea{padding-right:3.25rem;}
.input-wrap input:focus,.input-wrap textarea:focus,.input-wrap select:focus{border-color:#3b82f6;}
.input-wrap input::placeholder,.input-wrap textarea::placeholder{color:#94a3b8;}
.input-wrap input[readonly]{background:#f8fafc;cursor:not-allowed;color:#64748b;}
.form-hint{font-size:0.75rem;color:#94a3b8;margin-top:4px;}
.alert{display:flex;align-items:center;gap:8px;padding:0.75rem 1rem;border-radius:10px;font-size:0.875rem;margin-bottom:1.25rem;}
.alert svg{width:16px;height:16px;flex-shrink:0;}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;}
.btn-submit{width:100%;padding:0.75rem 1.5rem;font-size:0.875rem;font-weight:600;font-family:inherit;color:#fff;background:#22c55e;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s,transform 0.1s;}
.btn-submit:hover{background:#16a34a;transform:translateY(-1px);}
.btn-submit svg{width:16px;height:16px;}
#micBtn{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;border:none;background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(99,102,241,0.4);transition:transform 0.2s ease,box-shadow 0.2s ease,background 0.3s ease;outline:none;z-index:2;}
#micBtn svg{width:15px;height:15px;position:relative;z-index:1;pointer-events:none;}
#micBtn:hover{transform:translateY(calc(-50% - 2px)) scale(1.07);box-shadow:0 8px 22px rgba(99,102,241,0.55);}
#micBtn:active{transform:translateY(-50%) scale(0.93);}
#micBtn::before,#micBtn::after{content:'';position:absolute;inset:0;border-radius:50%;background:inherit;opacity:0;pointer-events:none;}
#micBtn.listening{background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);box-shadow:0 4px 18px rgba(239,68,68,0.45);animation:micBeat 1s ease-in-out infinite;}
#micBtn.listening::before{animation:micRipple 1.4s ease-out infinite;}
#micBtn.listening::after{animation:micRipple 1.4s ease-out 0.5s infinite;}
@keyframes micBeat{0%,100%{transform:translateY(-50%) scale(1);}50%{transform:translateY(-50%) scale(1.1);}}
@keyframes micRipple{0%{transform:scale(1);opacity:0.5;}100%{transform:scale(2.4);opacity:0;}}
@media(max-width:480px){body{padding:1rem;}.top-bar{flex-direction:column;align-items:flex-start;}.top-bar-right{width:100%;justify-content:space-between;}.card-body{padding:1.25rem;}}
</style>
</head>
<body>
<div class="wrapper">
    <div class="top-bar">
        <div class="top-bar-left">
            <div class="top-bar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
            </div>
            <div class="top-bar-text">
                <h1>Public Service Feedback</h1>
                <p>Help us improve services in your area</p>
            </div>
        </div>
        <div class="top-bar-right">
            <?php if(isset($_SESSION['name'])): ?>
                <span class="user-name">Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
            <?php endif; ?>
            <a href="login1.php" class="btn-back">Back</a>
        </div>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="glass-wrap">
            <div class="card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    <h2>Submit Your Feedback</h2>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="problem">Problem Description</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                            <textarea name="problem" id="problem" rows="4" placeholder="Write or speak your complaint..." required><?php echo htmlspecialchars($problem); ?></textarea>
                            <button type="button" id="micBtn" title="Voice input" aria-label="Start voice input">
                                <svg id="micIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 1a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V5a4 4 0 0 1 4-4Z"/>
                                    <path d="M19 10a1 1 0 0 0-2 0 5 5 0 0 1-10 0 1 1 0 0 0-2 0 7 7 0 0 0 6 6.93V19H9a1 1 0 0 0 0 2h6a1 1 0 0 0 0-2h-2v-2.07A7 7 0 0 0 19 10Z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="area">Area / Location</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <select name="area" id="area" required onchange="fetchCouncillor()">
                                <option value="">Select your area</option>
                                <?php foreach ($areaList as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a); ?>" <?php if($area===$a) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($a); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="councillor">Municipal Councillor</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                            <input type="text" name="councillor" id="councillor" placeholder="Auto-filled based on area" value="<?php echo htmlspecialchars($councillor); ?>" readonly required>
                        </div>
                        <p class="form-hint">Auto-populated based on your area</p>
                    </div>

                    <div class="form-group">
                        <label for="image">Upload Photo (optional)</label>
                        <div class="input-wrap">
                            <input type="file" name="image" id="image" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <p class="form-hint">PNG / JPG / WEBP • Max 10MB</p>
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        Submit Feedback
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function fetchCouncillor(){
    var area = document.getElementById("area").value.trim();
    if(area===""){document.getElementById("councillor").value="";return;}
    fetch("get_councillor.php?area="+encodeURIComponent(area))
        .then(r=>r.text()).then(d=>{document.getElementById("councillor").value=d.trim();})
        .catch(()=>{document.getElementById("councillor").value="";});
}
var recognizing=false,recognition;
var micBtn=document.getElementById("micBtn"),micIcon=document.getElementById("micIcon");
var MIC_IDLE='<path d="M12 1a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V5a4 4 0 0 1 4-4Z"/><path d="M19 10a1 1 0 0 0-2 0 5 5 0 0 1-10 0 1 1 0 0 0-2 0 7 7 0 0 0 6 6.93V19H9a1 1 0 0 0 0 2h6a1 1 0 0 0 0-2h-2v-2.07A7 7 0 0 0 19 10Z"/>';
var MIC_STOP='<rect x="6" y="6" width="12" height="12" rx="2"/>';
function setMicState(on){recognizing=on;micIcon.innerHTML=on?MIC_STOP:MIC_IDLE;micBtn.classList.toggle("listening",on);micBtn.title=on?"Stop listening":"Voice input";}
micBtn.addEventListener("click",function(){
    if(!('webkitSpeechRecognition'in window)&&!('SpeechRecognition'in window)){alert("Use Google Chrome for voice input.");return;}
    if(recognizing){recognition.stop();return;}
    var SR=window.SpeechRecognition||window.webkitSpeechRecognition;
    recognition=new SR();recognition.lang="en-IN";recognition.interimResults=false;recognition.continuous=false;
    recognition.onstart=()=>setMicState(true);
    recognition.onresult=e=>{var t=e.results[0][0].transcript;var ta=document.getElementById("problem");ta.value=ta.value.trim()?ta.value.trim()+" "+t:t;};
    recognition.onerror=e=>{setMicState(false);if(e.error!=="no-speech")alert("Error: "+e.error);};
    recognition.onend=()=>setMicState(false);
    recognition.start();
});
</script>
</body>
</html>
