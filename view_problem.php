<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid problem ID");
}

$stmt = $pdo->prepare("
    SELECT f.problem, f.status, f.anonymous, f.aadhar_no, f.image_path, l.name
    FROM feedback f
    LEFT JOIN login l ON f.aadhar_no = l.aadhar_no
    WHERE f.id = :id
");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Problem not found");
}

$name   = ($row['anonymous'] == 1) ? "Anonymous"  : ($row['name']      ?? "Unknown");
$aadhar = ($row['anonymous'] == 1) ? "Hidden"      : ($row['aadhar_no'] ?? "Not available");

$pdo = null;
?>
<!DOCTYPE html>
<html>
<head>
<title>Problem Details</title>
<style>
body { font-family: Arial, sans-serif; background: #0f172a; margin: 0; padding: 40px; color: #e5e7eb; }
.problem-box { max-width: 650px; margin: auto; background: #1e293b; padding: 25px 30px; border-radius: 12px; box-shadow: 0 15px 30px rgba(0,0,0,0.4); }
.problem-box h2 { margin-top: 0; margin-bottom: 20px; color: #38bdf8; }
.problem-row { margin: 14px 0; font-size: 16px; line-height: 1.6; }
.problem-row b { color: #f1f5f9; }
.status { padding: 5px 12px; border-radius: 6px; background: #f59e0b; color: black; font-weight: bold; }
.btn-back {
    position: fixed; top: 25px; right: 30px;
    padding: 0.375rem 0.875rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; background: rgba(239,68,68,0.12); color: #f87171;
    transition: background 0.2s; text-decoration: none; z-index: 1000;
}
.btn-back:hover { background: rgba(239,68,68,0.2); }
</style>
</head>
<body>
<div class="problem-box">
    <h2>Problem Details</h2>
    <a href="gov-dash.php" class="btn-back">Back</a>
    <div class="problem-row"><b>Problem:</b> <?php echo htmlspecialchars($row['problem']); ?></div>
    <?php if (!empty($row['image_path'])): ?>
        <div class="problem-row">
            <b>Uploaded Image:</b><br><br>
            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Problem Image"
                 style="max-width:100%;border-radius:10px;border:1px solid #334155;">
        </div>
    <?php endif; ?>
    <div class="problem-row"><b>Name:</b> <?php echo htmlspecialchars($name); ?></div>
    <div class="problem-row"><b>Aadhar:</b> <?php echo htmlspecialchars($aadhar); ?></div>
    <div class="problem-row"><b>Status:</b> <span class="status"><?php echo htmlspecialchars($row['status']); ?></span></div>
</div>
</body>
</html>
