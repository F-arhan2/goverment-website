<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT f.problem, f.status, f.anonymous, f.aadhar_no, l.name
    FROM feedback f
    LEFT JOIN login l ON f.aadhar_no = l.aadhar_no
    WHERE f.id = :id
");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode($row);
} else {
    echo json_encode(["problem" => "Not found"]);
}

$pdo = null;
?>
