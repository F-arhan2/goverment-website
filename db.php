<?php
$host     = 'aws-1-ap-south-1.pooler.supabase.com';
$port     = '5432';
$dbname   = 'postgres';
$user     = 'postgres.jgqzfngfjcukmmmqjzes';
$password = '4Ij6qQpvhB67UypK'; // ← paste your actual password

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die(" Connection failed: " . $e->getMessage());
}
?>