<?php
session_start();
include 'db.php';

if (!isset($_SESSION['aadhar_no'])) {
    header("Location: login1.php");
    exit();
}

$aadhar = $_SESSION['aadhar_no'];
$name   = $_SESSION['name'];

// SUMMARY
$total = $pending = $progress = $resolved = 0;

$q = $pdo->prepare("SELECT status FROM feedback WHERE aadhar_no = :aadhar");
$q->execute([':aadhar' => $aadhar]);
while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    if ($r['status'] == "Pending")         $pending++;
    elseif ($r['status'] == "In Progress") $progress++;
    elseif ($r['status'] == "Resolved")    $resolved++;
}

// NOTIFICATIONS - unread count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE aadhar_no = :aadhar AND is_read = false");
$stmt->execute([':aadhar' => $aadhar]);
$notif_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// NOTIFICATIONS - all
$stmt2 = $pdo->prepare("SELECT * FROM notifications WHERE aadhar_no = :aadhar ORDER BY id DESC");
$stmt2->execute([':aadhar' => $aadhar]);
$notifications = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// COMPLAINTS
$stmt3 = $pdo->prepare("SELECT * FROM feedback WHERE aadhar_no = :aadhar ORDER BY id DESC");
$stmt3->execute([':aadhar' => $aadhar]);
$complaints = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{ font-family:'Inter',sans-serif; background:#0f172a; color:#e2e8f0; }
.header{ display:flex; justify-content:space-between; align-items:center; padding:20px 30px; background:rgba(255,255,255,0.05); border-bottom:1px solid rgba(255,255,255,0.1); }
.header-right{ display:flex; align-items:center; gap:20px; }
.submit-btn{ background:rgb(45,173,13); color:white; padding:8px 16px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; }
.logout-btn{ color:#94a3b8; text-decoration:none; }
.cards{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; padding:30px; }
.card{ padding:25px; border-radius:16px; background:rgba(255,255,255,0.05); text-align:center; }
.table-container{ padding:30px; }
.table-wrapper{ max-height:700px; overflow-y:auto; border-radius:12px; }
table{ width:100%; border-collapse:collapse; background:rgba(255,255,255,0.05); }
th,td{ padding:14px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.05); }
th{ background:rgba(255,255,255,0.08); }
.complaint-img{ width:60px; height:60px; object-fit:cover; border-radius:6px; cursor:pointer; transition:0.2s; }
.complaint-img:hover{ transform:scale(1.1); }
.img-modal{ display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); justify-content:center; align-items:center; z-index:1000; }
.img-modal img{ max-width:85%; max-height:85%; border-radius:12px; }
.notification{ position:relative; cursor:pointer; font-size:20px; }
.badge{ background:red; color:white; border-radius:50%; padding:3px 7px; font-size:11px; position:absolute; top:-8px; right:-10px; }
.notif-box{ background:#111827; width:320px; max-height:200px; overflow-y:auto; position:absolute; right:0; top:35px; display:none; border-radius:10px; border:1px solid rgba(255,255,255,0.1); z-index:999; font-size:12px; }
.notif-item{ padding:6px 10px; border-bottom:1px solid rgba(255,255,255,0.05); line-height:1.2; }
.notif-item small{ font-size:10px; color:#9ca3af; }
</style>
<script>
function toggleNotif(){
    var box = document.getElementById("notifBox");
    if(box.style.display === "block"){
        box.style.display = "none";
    } else {
        box.style.display = "block";
        fetch("mark_read.php");
        var badge = document.querySelector(".badge");
        if(badge) badge.style.display = "none";
    }
}
document.addEventListener("DOMContentLoaded", function(){
    const images = document.querySelectorAll(".complaint-img");
    const modal  = document.getElementById("imgModal");
    const modalImg = document.getElementById("modalImg");
    images.forEach(img => {
        img.onclick = function(){ modal.style.display = "flex"; modalImg.src = this.src; }
    });
});
function closeModal(){ document.getElementById("imgModal").style.display = "none"; }
</script>
</head>
<body>

<div class="header">
    <div>Welcome, <?php echo htmlspecialchars($name); ?></div>
    <div class="header-right">
        <a href="gfeedback.php" class="submit-btn">+ New Complaint</a>
        <div class="notification" onclick="toggleNotif()">
            🔔
            <?php if($notif_count > 0): ?>
                <span class="badge"><?php echo $notif_count; ?></span>
            <?php endif; ?>
            <div class="notif-box" id="notifBox">
                <?php if(empty($notifications)): ?>
                    <div class="notif-item">No notifications</div>
                <?php else: ?>
                    <?php foreach($notifications as $row): ?>
                        <div class="notif-item">
                            <?php echo htmlspecialchars($row['message']); ?><br>
                            <small><?php echo $row['created_at']; ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</div>

<div class="cards">
    <div class="card">Total<br><strong><?php echo $total; ?></strong></div>
    <div class="card">Pending<br><strong><?php echo $pending; ?></strong></div>
    <div class="card">In Progress<br><strong><?php echo $progress; ?></strong></div>
    <div class="card">Resolved<br><strong><?php echo $resolved; ?></strong></div>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table>
            <tr><th>ID</th><th>Problem</th><th>Area</th><th>Status</th><th>Date</th><th>Image</th></tr>
            <?php if(empty($complaints)): ?>
                <tr><td colspan="6">No complaints yet</td></tr>
            <?php else: ?>
                <?php foreach($complaints as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['problem']); ?></td>
                        <td><?php echo htmlspecialchars($row['area_name']); ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <?php if(!empty($row['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="complaint-img">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<div id="imgModal" class="img-modal" onclick="closeModal()">
    <img id="modalImg">
</div>

</body>
</html>
