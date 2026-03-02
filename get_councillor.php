<?php
include 'db.php';

$area = trim($_GET['area'] ?? '');

if ($area === '') {
    exit;
}

$stmt = $pdo->prepare("SELECT municipalhead_name FROM area_heads WHERE area_name = :area LIMIT 1");
$stmt->execute([':area' => $area]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo $row['municipalhead_name'];
}

$pdo = null;
?>
