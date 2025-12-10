<?php
// admin/dashboard.php
require_once __DIR__ . '/../helpers.php';
require_admin(); // ensure admin or super_admin

// --- Stats Queries ---
$total_users = get_count('users');
$total_applications = get_count('applications');

// Approved, Pending, and Rejected aids counts
$approved_aids = $mysqli->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetch_row()[0];
$pending_aids  = $mysqli->query("SELECT COUNT(*) FROM applications WHERE status='pending'")->fetch_row()[0];
$rejected_aids = $mysqli->query("SELECT COUNT(*) FROM applications WHERE status='rejected'")->fetch_row()[0];

// Applications count by type
$applications_by_type = [];
$result = $mysqli->query("SELECT type, COUNT(*) AS count FROM applications GROUP BY type");
if ($result) {
    $applications_by_type = $result->fetch_all(MYSQLI_ASSOC);
}

// --- Recent messages (last 10) ---
$recent_msgs = [];
$stmt = $mysqli->prepare("SELECT sender, message, created_at FROM messages ORDER BY created_at DESC LIMIT 10");
if ($stmt) {
    $stmt->execute();
    $recent_msgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// --- Recent beneficiary aids (last 10) ---
$recent_aids = [];
$stmt = $mysqli->prepare("
    SELECT 
        u.id AS user_id,
        u.name AS full_name,
        u.barangay,
        a.status,
        a.type AS assistance,
        a.date_of_request AS date_request
    FROM applications a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");
if ($stmt) {
    $stmt->execute();
    $recent_aids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Chart data
$chart_labels = json_encode(['Approved', 'Rejected', 'Pending']);
$chart_data = json_encode([$approved_aids, $rejected_aids, $pending_aids]);

$application_types = json_encode(array_column($applications_by_type, 'type'));
$applications_count = json_encode(array_column($applications_by_type, 'count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; background:#f4f6fc; color:#333; }
a { text-decoration:none; }
.app { display:flex; min-height:100vh; }
.sidebar { width:240px; background:#FFA500; color:#fff; display:flex; flex-direction:column; min-height:100vh; box-shadow:2px 0 5px rgba(0,0,0,0.1); }
.sidebar-logo { font-size:1.8rem; text-align:center; margin:1.5rem 0; font-weight:700; letter-spacing:1px; color:#fff; }
.sidebar-nav a { display:flex; align-items:center; padding:0.9rem 1.5rem; color:#333; border-radius:6px; margin:0.3rem 1rem; transition:0.2s; background:#FFC04C; font-weight:500; }
.sidebar-nav a.active, .sidebar-nav a:hover { background:#fff; color:#FFA500; box-shadow:0 2px 5px rgba(0,0,0,0.2); }
.main { flex:1; display:flex; flex-direction:column; }
.header { display:flex; justify-content:space-between; align-items:center; padding:1.2rem 2rem; background:#fff; border-bottom:1px solid #e0e0e0; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
.header h1 { font-size:1.5rem; font-weight:600; }
.btn-logout { background:#DC3545; color:#fff; padding:0.5rem 1.2rem; border-radius:6px; font-weight:500; transition:0.2s; }
.btn-logout:hover { background:#c82333; }
.content { padding:1.5rem 2rem; }
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.5rem; margin-bottom:1.5rem; }
.card { background:#fff; padding:1.2rem 1.5rem; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.stat-card { border-left:5px solid #007BFF; }
.stat-card .card-title { font-size:0.9rem; font-weight:500; text-transform:uppercase; color:#555; }
.stat-card .card-value { font-size:2.2rem; font-weight:700; margin-top:0.3rem; color:#007BFF; }
.data-section-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(400px,1fr)); gap:1.5rem; }
.section-card { height:100%; }
.section-card .card-title { font-weight:600; font-size:1.2rem; margin-bottom:1rem; border-bottom:1px solid #eee; padding-bottom:0.5rem; }
.card-body { max-height:400px; overflow-y:auto; }
.message-item { border-bottom:1px solid #eee; padding:0.8rem 0; }
.message-item:last-child { border-bottom:none; }
.msg-sender { font-weight:600; color:#4F46E5; }
.msg-text { margin:0.3rem 0; line-height:1.4; }
.msg-time { font-size:0.75rem; color:#999; }
.data-table-container { overflow-x:auto; }
.data-table-container table { width:100%; border-collapse:collapse; min-width:650px; }
.data-table-container th, .data-table-container td { padding:0.9rem 1rem; border-bottom:1px solid #eee; text-align:left; font-size:0.9rem; }
.data-table-container th { background:#f8f8f8; font-weight:600; font-size:0.8rem; text-transform:uppercase; color:#666; }
.data-table-container tr:hover { background:#f0f8ff; }
td.status-pending { color:#FFA500; font-weight:600; }
td.status-approved { color:#28A745; font-weight:600; }
td.status-rejected { color:#DC3545; font-weight:600; }
@media(max-width:1024px) { .data-section-grid { grid-template-columns:1fr; } .stats-grid { grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); } }
@media(max-width:768px) { .sidebar { width:100%; height:auto; min-height:unset; border-right:none; } .app { flex-direction:column; } .sidebar-nav { display:flex; flex-wrap:wrap; justify-content:space-around; margin:0 0 1rem 0; } .sidebar-nav a { margin:0.2rem; padding:0.5rem 1rem; flex-grow:1; justify-content:center;} .sidebar-logo { display:none; } .content { padding:1rem; } }
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="user.php">Users</a>
            <a href="applications.php">Applications</a>
            <a href="messages.php">Messages</a>
            <a href="aid_history.php">Aid History</a> 
            <a href="beneficiaries.php">Beneficiaries</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="card stat-card">
                    <div class="card-title">Total Users</div>
                    <div class="card-value"><?= $total_users ?></div>
                </div>
                <div class="card stat-card">
                    <div class="card-title">Total Applications</div>
                    <div class="card-value"><?= $total_applications ?></div>
                </div>
                <div class="card stat-card" style="border-left-color:#28A745;">
                    <div class="card-title">Approved Aids</div>
                    <div class="card-value"><?= $approved_aids ?></div>
                </div>
                <div class="card stat-card" style="border-left-color:#FFA500;">
                    <div class="card-title">Pending Aids</div>
                    <div class="card-value"><?= $pending_aids ?></div>
                </div>
            </div>

            <!-- Data Section with Charts -->
            <div class="data-section-grid">
                <!-- Recent Messages -->
                <div class="card section-card">
                    <div class="card-title">Recent Messages</div>
                    <div class="card-body">
                        <?php if(empty($recent_msgs)): ?>
                            <p>No messages to display.</p>
                        <?php else: foreach($recent_msgs as $m): ?>
                            <div class="message-item">
                                <span class="msg-sender"><?= htmlspecialchars($m['sender']) ?></span>
                                <p class="msg-text"><?= nl2br(htmlspecialchars(substr($m['message'],0,75))) ?><?= strlen($m['message'])>75?'...':'' ?></p>
                                <small class="msg-time"><?= htmlspecialchars($m['created_at']) ?></small>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Recent Beneficiary Aids -->
                <div class="card section-card">
                    <div class="card-title">Recent Beneficiary Aids</div>
                    <div class="card-body">
                        <?php if(empty($recent_aids)): ?>
                            <p>No recent aid records found.</p>
                        <?php else: ?>
                            <div class="data-table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Barangay</th>
                                            <th>Status</th>
                                            <th>Assistance</th>
                                            <th>Date of Request</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_aids as $aid): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($aid['full_name']) ?></td>
                                                <td><?= htmlspecialchars($aid['barangay'] ?? '-') ?></td>
                                                <td class="status-<?= strtolower($aid['status']) ?>"><?= htmlspecialchars(strtoupper($aid['status'])) ?></td>
                                                <td><?= htmlspecialchars(strtoupper($aid['assistance'])) ?></td>
                                                <td><?= htmlspecialchars($aid['date_request']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Applications Overview Chart -->
                <div class="card section-card">
                    <div class="card-title">Applications Overview</div>
                    <div class="card-body">
                        <canvas id="applicationsChart" style="width:100%;height:250px;"></canvas>
                    </div>
                </div>

                <!-- Applications by Type Chart -->
                <div class="card section-card">
                    <div class="card-title">Applications by Type</div>
                    <div class="card-body">
                        <canvas id="applicationsTypeChart" style="width:100%;height:250px;"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const applicationsChart = new Chart(document.getElementById('applicationsChart'), {
    type: 'bar',
    data: {
        labels: <?= $chart_labels ?>,
        datasets: [{
            label: 'Applications',
            data: <?= $chart_data ?>,
            backgroundColor: ['#28A745','#DC3545','#FFA500'],
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

const applicationsTypeChart = new Chart(document.getElementById('applicationsTypeChart'), {
    type: 'doughnut', // or 'bar' for bar chart
    data: {
        labels: <?= $application_types ?>,
        datasets: [{
            label: 'Applications per Type',
            data: <?= $applications_count ?>,
            backgroundColor: ['#007BFF','#28A745','#FFC107','#DC3545','#6f42c1','#20c997'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 15, padding: 10 } } }
    }
});
</script>
</body>
</html>
