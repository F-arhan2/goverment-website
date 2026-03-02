<?php
session_start();
include 'db.php';

if (isset($_POST['id']) && isset($_POST['status'])) {

    $id     = (int) $_POST['id'];
    $status = $_POST['status'];

    // Get old status + aadhar
    $stmt = $pdo->prepare("SELECT status, aadhar_no FROM feedback WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data      = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldStatus = $data['status'];
    $aadhar_no = $data['aadhar_no'];

    // Update status
    $stmt2 = $pdo->prepare("UPDATE feedback SET status = :status WHERE id = :id");
    $stmt2->execute([':status' => $status, ':id' => $id]);

    // Send notification if resolved
    if ($oldStatus != "Resolved" && $status == "Resolved") {
        $message = "Your complaint ID $id has been resolved.";
        $stmt3   = $pdo->prepare("INSERT INTO notifications (aadhar_no, message) VALUES (:aadhar, :message)");
        $stmt3->execute([':aadhar' => $aadhar_no, ':message' => $message]);
    }
}

$pdo = null;
header("Location: gov-dash.php");
exit();
?>
