<?php
session_start();
include 'db.php';

$error = "";

if (isset($_POST['login'])) {

    $role = $_POST['role'];

    /* CITIZEN LOGIN */
    if ($role == "citizen") {

        $aadhar = trim($_POST['aadhar']);

        if (empty($aadhar)) {
            $error = "Aadhaar is required!";
        }
        elseif (!preg_match("/^[0-9]{12}$/", $aadhar)) {
            $error = "Aadhaar must be 12 digits!";
        }
        else {

            $stmt = $pdo->prepare("SELECT * FROM login WHERE aadhar_no = :aadhar");
            $stmt->execute([':aadhar' => $aadhar]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {

                $_SESSION['aadhar_no'] = $user['aadhar_no'];
                $_SESSION['name']      = $user['name'];
                $_SESSION['address']   = $user['address'];

                header("Location: user-dash.php");
                exit();

            } else {
                $error = "Citizen not found!";
            }
        }
    }

    /* GOVERNMENT LOGIN */
    if ($role == "gov") {

        $gov_id   = trim($_POST['gov_id']);
        $password = trim($_POST['password']);

        if (empty($gov_id) || empty($password)) {
            $error = "Enter Government ID and Password";
        }
        else {

            $stmt = $pdo->prepare("SELECT * FROM gov_login WHERE gov_id = :gov_id AND password = :password");
            $stmt->execute([
                ':gov_id'   => $gov_id,
                ':password' => $password
            ]);
            $gov = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($gov) {

                $_SESSION['gov_id']   = $gov['gov_id'];
                $_SESSION['gov_name'] = $gov['name'];

                header("Location: gov-dash.php");
                exit();

            } else {
                $error = "Government login failed!";
            }
        }
    }
}

$pdo = null; // close connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Municipal Complaint Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', sans-serif;
    background: #0f172a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    color: #e2e8f0;
}

.wrapper { width: 100%; max-width: 420px; }

.page-header {
    text-align: center;
    margin-bottom: 2rem;
}
.page-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f1f5f9;
}
.page-header p {
    font-size: 0.875rem;
    color: #94a3b8;
}

.glass-wrap {
    padding: 6px;
    border-radius: 20px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.1);
}

.card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}
.card-header h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}

.card-body { padding: 1.5rem; }

.form-group { margin-bottom: 1.25rem; }

.form-group label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.375rem;
}

.input-wrap input, select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    color: #1e293b;
    font-family: inherit;
}

.input-wrap input:focus, select:focus {
    border-color: #3b82f6;
    outline: none;
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.btn-primary {
    width: 100%;
    padding: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    background: #3b82f6;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s;
}

.btn-primary:hover { background: #2563eb; }

.footer-link {
    text-align: center;
    margin-top: 1.5rem;
}

.footer-link a {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.85rem;
}

.account { color: #4f5358; }
</style>

<script>
function toggleFields() {
    var role = document.getElementById("role").value;
    if (role === "citizen") {
        document.getElementById("citizenFields").style.display = "block";
        document.getElementById("govFields").style.display = "none";
    } else {
        document.getElementById("citizenFields").style.display = "none";
        document.getElementById("govFields").style.display = "block";
    }
}
</script>

</head>
<body>

<div class="wrapper">

    <div class="page-header">
        <h1>Municipal Complaint Portal</h1>
        <p>Login</p>
    </div>

    <div class="glass-wrap">
        <div class="card">

            <div class="card-header">
                <h2>Login</h2>
            </div>

            <div class="card-body">

                <?php if($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="form-group">
                        <label>Login As</label>
                        <select name="role" id="role" onchange="toggleFields()" required>
                            <option value="citizen">Citizen</option>
                            <option value="gov">Government Official</option>
                        </select>
                    </div>

                    <!-- Citizen Fields -->
                    <!-- Citizen Fields -->
<div id="citizenFields">
    <div class="form-group">
        <label>Aadhaar Number</label>
        <div class="input-wrap">
            <input type="text" name="aadhar" placeholder="Enter 12-digit Aadhaar"
                   maxlength="12"
                   inputmode="numeric"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   pattern="[0-9]{12}"
                   title="Aadhaar must be exactly 12 digits">
        </div>
    </div>
</div>

                    <!-- Government Fields -->
                    <div id="govFields" style="display:none;">
                        <div class="form-group">
                            <label>Government ID</label>
                            <div class="input-wrap">
                                <input type="text" name="gov_id" placeholder="Enter Government ID">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-wrap">
                                <input type="password" name="password" placeholder="Enter Password">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-primary">Login</button>

                </form>

                <div class="footer-link">
                    <p class="account">Don't have an account?</p>
                    <a href="register.php">Register here -></a>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-link">
        <p>Want to submit feedback without giving details?</p>
        <a href="Anonymous.php">Submit Anonymously -></a>
    </div>

</div>

</body>
</html>
