<?php
include 'db.php';

$error   = "";
$success = "";

if (isset($_POST['register'])) {

    $name    = trim($_POST['name']);
    $aadhar  = trim($_POST['adhar']);
    $address = trim($_POST['address']);

    if (empty($name) || empty($aadhar) || empty($address)) {
        $error = "All fields are required!";
    }
    elseif (!preg_match("/^[0-9]{12}$/", $aadhar)) {
        $error = "Aadhaar must be exactly 12 digits!";
    }
    else {
        // STEP 1: Check Aadhaar exists in citizens table
        $stmt = $pdo->prepare("SELECT aadhar_no FROM citizens WHERE aadhar_no = :aadhar");
        $stmt->execute([':aadhar' => $aadhar]);
        $citizen = $stmt->fetch();

        if (!$citizen) {
            $error = "Aadhaar number is invalid.";
        } else {
            // STEP 2: Check if already registered
            $stmt2 = $pdo->prepare("SELECT aadhar_no FROM login WHERE aadhar_no = :aadhar");
            $stmt2->execute([':aadhar' => $aadhar]);
            $existing = $stmt2->fetch();

            if ($existing) {
                $error = "You are already registered. Please login.";
            } else {
                // STEP 3: Insert into login table
                $stmt3 = $pdo->prepare("INSERT INTO login (aadhar_no, name, address) VALUES (:aadhar, :name, :address)");
                if ($stmt3->execute([':aadhar' => $aadhar, ':name' => $name, ':address' => $address])) {
                    $success = "Registration successful! Redirecting to login...";
                    header("refresh:2;url=login1.php");
                } else {
                    $error = "Registration failed!";
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
<title>Details - Municipal Service Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', sans-serif; background: #0f172a; min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 1.5rem; color: #e2e8f0; line-height: 1.6; position: relative; overflow-x: hidden;
}
body::before, body::after { content: ''; position: fixed; border-radius: 50%; filter: blur(120px); pointer-events: none; z-index: 0; }
body::before { width: 400px; height: 400px; top: -100px; left: 10%; background: rgba(59,130,246,0.08); }
body::after  { width: 350px; height: 350px; bottom: -80px; right: 10%; background: rgba(34,197,94,0.06); }
.wrapper { width: 100%; max-width: 420px; position: relative; z-index: 1; }
.page-header { text-align: center; margin-bottom: 2rem; }
.page-header .icon-box {
    display: inline-flex; align-items: center; justify-content: center;
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); margin-bottom: 1rem;
}
.page-header .icon-box svg { width: 28px; height: 28px; color: #3b82f6; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; }
.page-header p  { font-size: 0.875rem; color: #94a3b8; margin-top: 0.5rem; }
.glass-wrap {
    padding: 6px; border-radius: 20px; background: rgba(255,255,255,0.05);
    backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1);
}
.card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4); }
.card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
.card-header h2 { font-size: 1.125rem; font-weight: 600; color: #1e293b; }
.card-header p  { font-size: 0.8125rem; color: #64748b; margin-top: 2px; }
.card-body { padding: 1.5rem; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: #1e293b; margin-bottom: 0.375rem; }
.input-wrap { position: relative; }
.input-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #94a3b8; pointer-events: none; }
.input-wrap input {
    width: 100%; padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; font-family: inherit;
    color: #1e293b; background: #fff; outline: none; transition: border-color 0.2s;
}
.input-wrap input:focus { border-color: #3b82f6; }
.input-wrap input::placeholder { color: #94a3b8; }
.form-hint { font-size: 0.75rem; color: #94a3b8; margin-top: 4px; }
.alert { display: flex; align-items: center; gap: 8px; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.875rem; margin-bottom: 1.25rem; }
.alert-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.alert-error .alert-dot { background: #dc2626; }
.alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
.alert-success .alert-dot { background: #16a34a; }
.btn-primary {
    width: 100%; padding: 0.75rem 1.5rem; font-size: 0.875rem; font-weight: 600; font-family: inherit;
    color: #fff; background: #3b82f6; border: none; border-radius: 10px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s, transform 0.1s;
}
.btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
.btn-primary svg { width: 16px; height: 16px; }
.footer-link { text-align: center; margin-top: 1.5rem; }
.footer-link p  { font-size: 0.8125rem; color: #94a3b8; }
.footer-link a  { display: inline-flex; align-items: center; gap: 4px; font-size: 0.8125rem; font-weight: 500; color: #3b82f6; text-decoration: none; margin-top: 0.5rem; }
.footer-link a:hover { color: #2563eb; }
@media (max-width: 480px) { body { padding: 1rem; } .card-body { padding: 1.25rem; } }
</style>
</head>
<body>
<div class="wrapper">
    <div class="page-header">
        <div class="icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
        </div>
        <h1>Municipal Service Portal</h1>
        <p>Details to submit public service feedback</p>
    </div>
    <div class="glass-wrap">
        <div class="card">
            <div class="card-header">
                <h2>Details</h2>
                <p>Enter your details to get started</p>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-error"><span class="alert-dot"></span> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><span class="alert-dot"></span> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adhar">Aadhaar Number</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                            <input type="text" id="adhar" name="adhar" placeholder="xxxx xxxx xxxx" required
                                   pattern="[0-9]{12}" maxlength="12" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   style="font-family:'Courier New',monospace;letter-spacing:2px;">
                        </div>
                        <p class="form-hint">12-digit unique identity number</p>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <input type="text" id="address" name="address" placeholder="Enter your address" required>
                        </div>
                    </div>
                    <button type="submit" name="register" class="btn-primary">
                        Details &amp; Continue
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="footer-link">
        <p>Have an account?</p>
        <a href="login1.php">Login here -></a>
    </div>
</div>
</body>
</html>
