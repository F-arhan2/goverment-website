<?php
include 'db.php';

// Counts
$loginCount    = $pdo->query("SELECT COUNT(*) FROM login")->fetchColumn();
$feedbackCount = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$pendingCount  = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status='Pending'")->fetchColumn();
$progressCount = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status='In Progress'")->fetchColumn();
$resolvedCount = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status='Resolved'")->fetchColumn();

// Data
$loginRows    = $pdo->query("SELECT aadhar_no, name, address FROM login")->fetchAll(PDO::FETCH_ASSOC);
$feedbackRows = $pdo->query("SELECT id, problem, area_name, municipalhead_name, status FROM feedback")->fetchAll(PDO::FETCH_ASSOC);
$areaRows     = $pdo->query("SELECT DISTINCT area_name FROM feedback ORDER BY area_name")->fetchAll(PDO::FETCH_ASSOC);

$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Municipal Data Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{overflow-x:hidden;}
.login-scroll,.feedback-scroll{scrollbar-width:thin;scrollbar-color:#475569 transparent;}
.login-scroll::-webkit-scrollbar,.feedback-scroll::-webkit-scrollbar{width:6px;}
.login-scroll::-webkit-scrollbar-track,.feedback-scroll::-webkit-scrollbar-track{background:transparent;}
.login-scroll::-webkit-scrollbar-thumb,.feedback-scroll::-webkit-scrollbar-thumb{background-color:#475569;border-radius:6px;}
body{font-family:'Inter',sans-serif;background:#0f172a;min-height:100vh;padding:1.5rem;color:#e2e8f0;line-height:1.6;position:relative;}
body::before,body::after{content:'';position:fixed;border-radius:50%;filter:blur(120px);pointer-events:none;}
body::before{width:400px;height:400px;top:-100px;left:15%;background:rgba(59,130,246,0.07);}
body::after{width:350px;height:350px;bottom:-80px;right:10%;background:rgba(34,197,94,0.05);}
.container{max-width:1100px;margin:0 auto;position:relative;z-index:1;}
.top-bar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;}
.top-bar-left{display:flex;align-items:center;gap:0.75rem;}
.top-bar-icon{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:12px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);}
.top-bar-icon svg{width:20px;height:20px;color:#3b82f6;}
.top-bar-text h1{font-size:1.25rem;font-weight:700;color:#f1f5f9;}
.top-bar-text p{font-size:0.8125rem;color:#94a3b8;}
.logout-btn{color:#94a3b8;text-decoration:none;}
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;}
.stat-card{display:flex;align-items:center;gap:1rem;padding:1.25rem;border-radius:16px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);}
.stat-icon{display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;flex-shrink:0;}
.stat-icon svg{width:24px;height:24px;}
.stat-icon.amber{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);}
.stat-icon.amber svg{color:#f59e0b;}
.stat-icon.blue{background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);}
.stat-icon.blue svg{color:#3b82f6;}
.stat-icon.green{background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.2);}
.stat-icon.green svg{color:#22c55e;}
.stat-value{font-size:1.5rem;font-weight:700;color:#f1f5f9;}
.stat-label{font-size:0.8125rem;color:#94a3b8;}
.filter-bar{display:flex;flex-wrap:wrap;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:16px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);margin-bottom:1.5rem;}
.filter-label{display:flex;align-items:center;gap:6px;font-size:0.8125rem;font-weight:600;color:#0068f9;}
.filter-label svg{width:16px;height:16px;}
.filter-select{padding:0.5rem 2rem 0.5rem 0.75rem;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);color:#f1f5f9;font-size:0.8125rem;font-family:inherit;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml;utf8,<svg fill='%2394a3b8' height='16' viewBox='0 0 24 24' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");background-repeat:no-repeat;background-position:right 8px center;}
.filter-select option{background:#1e293b;color:#f1f5f9;}
.glass-table{border-radius:16px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);overflow:hidden;margin-bottom:2rem;}
.glass-table-header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.08);}
.glass-table-header-left{display:flex;align-items:center;gap:0.5rem;}
.glass-table-header-left svg{width:20px;height:20px;color:#3b82f6;}
.glass-table-header-left h2{font-size:1.0625rem;font-weight:600;color:#f1f5f9;}
.badge{display:inline-flex;align-items:center;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;}
.badge-blue{background:rgba(59,130,246,0.1);color:#3b82f6;border:1px solid rgba(59,130,246,0.2);}
.badge-red{background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.2);}
.login-scroll{max-height:40vh;overflow-y:auto;}
.login-scroll thead th{position:sticky;top:0;background:#0f172a;z-index:5;}
.feedback-scroll{max-height:40vh;overflow-y:auto;}
.feedback-scroll thead th{position:sticky;top:0;background:#0f172a;z-index:5;}
.data-table{width:100%;border-collapse:collapse;}
.data-table thead th{text-align:left;padding:0.75rem 1.5rem;font-size:0.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid rgba(255,255,255,0.06);}
.data-table thead th:first-child{text-align:center;width:50px;}
.data-table tbody td{padding:0.875rem 1.5rem;font-size:0.8125rem;color:#e2e8f0;border-bottom:1px solid rgba(255,255,255,0.04);}
.data-table tbody td:first-child{text-align:center;color:#94a3b8;}
.data-table tbody tr:last-child td{border-bottom:none;}
.data-table tbody tr:hover{background:rgba(255,255,255,0.02);}
.cell-mono{font-family:'Courier New',monospace;font-size:0.75rem;}
.cell-bold{font-weight:500;}
.cell-muted{color:#94a3b8;}
.cell-problem{max-width:300px;}
.cell-problem p{display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;}
.empty-row td{text-align:center!important;padding:2.5rem 1.5rem!important;color:#94a3b8;}
.status-select{appearance:none;-webkit-appearance:none;padding:0.375rem 1.75rem 0.375rem 0.625rem;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);color:#f1f5f9;font-size:0.75rem;font-weight:500;font-family:inherit;outline:none;cursor:pointer;background-image:url("data:image/svg+xml;utf8,<svg fill='%2394a3b8' height='14' viewBox='0 0 24 24' width='14' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");background-repeat:no-repeat;background-position:right 6px center;}
.status-select option{background:#1e293b;color:#f1f5f9;}
.mobile-cards{display:none;}
.mobile-card{padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.05);}
.mobile-card:last-child{border-bottom:none;}
.mobile-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;margin-bottom:0.5rem;}
.mobile-card-num{font-size:0.75rem;color:#94a3b8;font-family:monospace;}
.mobile-card p{font-size:0.8125rem;color:#e2e8f0;margin-bottom:0.75rem;line-height:1.5;}
.mobile-card-meta{display:flex;flex-direction:column;gap:4px;margin-bottom:0.75rem;}
.mobile-card-meta span{font-size:0.75rem;}
.meta-label{color:#94a3b8;}
.meta-value{color:#f1f5f9;font-weight:500;}
.status-badge{display:inline-flex;align-items:center;gap:4px;padding:0.25rem 0.625rem;border-radius:9999px;font-size:0.6875rem;font-weight:500;border:1px solid;}
.status-pending{background:rgba(245,158,11,0.1);color:#f59e0b;border-color:rgba(245,158,11,0.2);}
.status-progress{background:rgba(59,130,246,0.1);color:#3b82f6;border-color:rgba(59,130,246,0.2);}
.status-resolved{background:rgba(34,197,94,0.1);color:#22c55e;border-color:rgba(34,197,94,0.2);}
.problem-link{color:inherit;text-decoration:none;}
.problem-link:hover{text-decoration:underline;}
.footer{text-align:center;font-size:0.75rem;color:#64748b;padding-bottom:1rem;}
@media(max-width:768px){body{padding:1rem;}.stats-grid{grid-template-columns:1fr;}.top-bar{flex-direction:column;align-items:flex-start;}.desktop-table{display:none;}.mobile-cards{display:block;}.glass-table-header{padding:0.875rem 1.25rem;}}
@media(min-width:769px){.mobile-cards{display:none;}}
</style>
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="top-bar-left">
            <div class="top-bar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
            </div>
            <div class="top-bar-text">
                <h1>Municipal Data Portal</h1>
                <p>Overview of all registered records</p>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
            <div><div class="stat-value"><?php echo $pendingCount; ?></div><div class="stat-label">Pending</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg></div>
            <div><div class="stat-value"><?php echo $progressCount; ?></div><div class="stat-label">In Progress</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
            <div><div class="stat-value"><?php echo $resolvedCount; ?></div><div class="stat-label">Resolved</div></div>
        </div>
    </div>

    <!-- Login Table -->
    <div class="glass-table">
        <div class="glass-table-header">
            <div class="glass-table-header-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                <h2>Login Records</h2>
            </div>
            <span class="badge badge-blue"><?php echo $loginCount; ?> records</span>
        </div>
        <div class="desktop-table login-scroll">
            <table class="data-table">
                <thead><tr><th>#</th><th>Aadhaar No.</th><th>Name</th><th>Address</th></tr></thead>
                <tbody>
                    <?php if(!empty($loginRows)): $i=1; foreach($loginRows as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td class="cell-mono"><?php echo htmlspecialchars($row['aadhar_no']); ?></td>
                            <td class="cell-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($row['address']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr class="empty-row"><td colspan="4">No login records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mobile-cards">
            <?php if(!empty($loginRows)): $i=1; foreach($loginRows as $row): ?>
                <div class="mobile-card">
                    <div class="mobile-card-top"><span class="mobile-card-num">#<?php echo $i++; ?></span></div>
                    <div class="mobile-card-meta">
                        <span><span class="meta-label">Aadhaar: </span><span class="meta-value" style="font-family:monospace;"><?php echo htmlspecialchars($row['aadhar_no']); ?></span></span>
                        <span><span class="meta-label">Name: </span><span class="meta-value"><?php echo htmlspecialchars($row['name']); ?></span></span>
                        <span><span class="meta-label">Address: </span><span class="meta-value"><?php echo htmlspecialchars($row['address']); ?></span></span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div style="padding:2rem;text-align:center;color:#94a3b8;">No login records found.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
        <div class="filter-label">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
            Filter by Area
        </div>
        <select id="areaSearch" class="filter-select" onchange="filterData()">
            <option value="">All Areas</option>
            <?php foreach($areaRows as $area): ?>
                <option value="<?php echo htmlspecialchars($area['area_name']); ?>"><?php echo htmlspecialchars($area['area_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="statusSearch" class="filter-select" onchange="filterData()">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
        </select>
    </div>

    <!-- Feedback Table -->
    <div class="glass-table">
        <div class="glass-table-header">
            <div class="glass-table-header-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                <h2>Feedback Reports</h2>
            </div>
            <span class="badge badge-red"><?php echo $feedbackCount; ?> reports</span>
        </div>
        <div class="desktop-table feedback-scroll">
            <table class="data-table">
                <thead><tr><th>#</th><th>Problem</th><th>Area Name</th><th>Municipal Head</th><th>Status</th></tr></thead>
                <tbody id="feedbackBody">
                    <?php if(!empty($feedbackRows)): $j=1; foreach($feedbackRows as $row): ?>
                        <tr data-area="<?php echo htmlspecialchars($row['area_name']); ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>">
                            <td><?php echo $j++; ?></td>
                            <td class="cell-problem"><p><a href="view_problem.php?id=<?php echo $row['id']; ?>" target="_blank" class="problem-link"><?php echo htmlspecialchars($row['problem']); ?></a></p></td>
                            <td class="cell-bold"><?php echo htmlspecialchars($row['area_name']); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($row['municipalhead_name']); ?></td>
                            <td>
                                <form method="post" action="update_status.php" style="margin:0;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option <?php if($row['status']=="Pending") echo "selected"; ?>>Pending</option>
                                        <option <?php if($row['status']=="In Progress") echo "selected"; ?>>In Progress</option>
                                        <option <?php if($row['status']=="Resolved") echo "selected"; ?>>Resolved</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr class="empty-row"><td colspan="5">No feedback records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mobile-cards" id="feedbackMobileBody">
            <?php if(!empty($feedbackRows)): $j=1; foreach($feedbackRows as $row):
                $sc = $row['status']==='In Progress' ? 'status-progress' : ($row['status']==='Resolved' ? 'status-resolved' : 'status-pending');
            ?>
                <div class="mobile-card" data-area="<?php echo htmlspecialchars($row['area_name']); ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>">
                    <div class="mobile-card-top">
                        <span class="mobile-card-num">#<?php echo $j++; ?></span>
                        <span class="status-badge <?php echo $sc; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                    </div>
                    <p><a href="view_problem.php?id=<?php echo $row['id']; ?>" style="color:#38bdf8;text-decoration:none;"><?php echo htmlspecialchars($row['problem']); ?></a></p>
                    <div class="mobile-card-meta">
                        <span><span class="meta-label">Area: </span><span class="meta-value"><?php echo htmlspecialchars($row['area_name']); ?></span></span>
                        <span><span class="meta-label">Councillor: </span><span class="meta-value"><?php echo htmlspecialchars($row['municipalhead_name']); ?></span></span>
                    </div>
                    <form method="post" action="update_status.php" style="margin:0;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <select name="status" class="status-select" onchange="this.form.submit()" style="width:100%;">
                            <option <?php if($row['status']=="Pending") echo "selected"; ?>>Pending</option>
                            <option <?php if($row['status']=="In Progress") echo "selected"; ?>>In Progress</option>
                            <option <?php if($row['status']=="Resolved") echo "selected"; ?>>Resolved</option>
                        </select>
                    </form>
                </div>
            <?php endforeach; else: ?>
                <div style="padding:2rem;text-align:center;color:#94a3b8;">No feedback records found.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">Municipal Public Service Portal</div>
</div>

<script>
function filterData(){
    var area   = document.getElementById("areaSearch").value.toLowerCase();
    var status = document.getElementById("statusSearch").value.toLowerCase();
    document.querySelectorAll("#feedbackBody tr").forEach(function(row){
        var ok = (area===""||row.getAttribute("data-area").toLowerCase()===area) && (status===""||row.getAttribute("data-status").toLowerCase()===status);
        row.style.display = ok ? "" : "none";
    });
    document.querySelectorAll("#feedbackMobileBody .mobile-card").forEach(function(card){
        var ok = (area===""||card.getAttribute("data-area").toLowerCase()===area) && (status===""||card.getAttribute("data-status").toLowerCase()===status);
        card.style.display = ok ? "" : "none";
    });
}
</script>
</body>
</html>
