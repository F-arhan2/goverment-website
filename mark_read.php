<?php
session_start();
include 'db.php';

if (isset($_SESSION['aadhar_no'])) {
    $aadhar = $_SESSION['aadhar_no'];
    $stmt   = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE aadhar_no = :aadhar");
    $stmt->execute([':aadhar' => $aadhar]);
}

$pdo = null;
?>
