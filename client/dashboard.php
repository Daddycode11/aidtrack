<?php
// client/dashboard.php
require_once __DIR__ . '/../helpers.php';
require_client();

if (isset($mysqli)) {
    $uid = $_SESSION['user']['id'];

    // --- Fetch user applications ---
    $stmt = $mysqli->prepare("SELECT * FROM applications WHERE user_id=? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else { $apps = []; }

    // --- Fetch recent messages ---
    $stmt = $mysqli->prepare("SELECT * FROM messages WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $msgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else { $msgs = []; }

    // --- Prepare data for doughnut chart ---
    $status_counts = ['pending'=>0,'approved'=>0,'rejected'=>0,'completed'=>0];
    foreach ($apps as $a) {
        $key = strtolower($a['status']);
        if(isset($status_counts[$key])) $status_counts[$key]++;
    }

    // --- Prepare data for monthly trend chart ---
    $monthly_counts = array_fill(1, 12, 0); // Jan-Dec
    foreach ($apps as $a) {
        $month = (int)date('n', strtotime($a['created_at']));
        $monthly_counts[$month]++;
    }

} else {
    $apps = [];
    $msgs = [];
    $status_counts = ['pending'=>0,'approved'=>0,'rejected'=>0,'completed'=>0];
    $monthly_counts = array_fill(1, 12, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Client Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Poppins', sans-serif; background: #f4f6fc; color: #333; }
a { text-decoration: none; }
.app { display: flex; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 240px; background-color: #FFA500; color: #fff; display: flex; flex-direction: column; min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
.sidebar-logo { font-size: 1.8rem; text-align: center; margin: 1.5rem 0; font-weight: 700; letter-spacing: 1px; }
.sidebar-nav a { display: flex; align-items: center; padding: 0.9rem 1.5rem; color: #333; border-radius: 6px; margin: 0.3rem 1rem; transition: 0.2s; background-color: #FFC04C; font-weight: 500; }
.sidebar-nav a i { margin-right: 10px; font-size: 1.1em; }
.sidebar-nav a.active, .sidebar-nav a:hover { background-color: #fff; color: #FFA500; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }

/* Main */
.main { flex: 1; display: flex; flex-direction: column; }
.header { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 2rem; background-color: #fff; border-bottom: 1px solid #e0e0e0; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.header h1 { font-size: 1.5rem; font-weight: 600; }
.btn-logout { background-color: #FF8A00; color: #fff; padding: 0.5rem 1.2rem; border: none; border-radius: 6px; font-weight: 500; transition: 0.2s; }
.btn-logout:hover { background-color: #e07a00; }

/* Main content area */
.content { padding: 1.5rem 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card { background: #fff; padding: 1.2rem 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.card-title { font-weight: 600; font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
.card-body { max-height: 60vh; overflow-y: auto; }

/* Application list */
.app-item { padding: 0.8rem 0; border-bottom: 1px solid #eee; }
.app-item:last-child { border-bottom: none; }
.app-item strong { font-weight: 600; font-size: 1.1rem; display: block; margin-bottom: 0.2rem; }
.app-status { font-weight: 500; }
.status-pending { color: #FFA500; font-weight: 600; }
.status-approved { color: #28A745; font-weight: 600; }
.status-rejected { color: #DC3545; font-weight: 600; }
.status-completed { color: #007BFF; font-weight: 600; }

/* Messages */
.message-item { border-bottom: 1px solid #eee; padding: 0.8rem 0; }
.message-item:last-child { border-bottom: none; }
.msg-sender { font-weight: 600; color: #FF8A00; margin-right: 0.5rem; }
.msg-time { font-size: 0.75rem; color: #999; display: block; margin-top: 0.2rem; }

/* Responsive */
@media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; min-height: unset; border-right: none; }
    .app { flex-direction: column; }
    .sidebar-nav { display: flex; flex-wrap: wrap; justify-content: space-around; margin: 0 0 1rem 0; }
    .sidebar-nav a { margin: 0.2rem; padding: 0.5rem 1rem; flex-grow: 1; justify-content: center; }
    .sidebar-nav a i { margin-right: 5px; }
    .sidebar-logo { display: none; }
    .content { padding: 1rem; display: flex; flex-direction: column; }
}
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="apply.php"><i class="fas fa-file-alt"></i>Type of Request</a>
            <a href="aid_history.php"><i class="fas fa-history"></i>Aid History</a>
            <a href="messages.php"><i class="fas fa-bell"></i>Notification</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Welcome, <?=htmlspecialchars($_SESSION['user']['name'] ?? 'Client')?></h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">
            <!-- Applications -->
            <section class="card">
                <div class="card-title">My Applications</div>
                <div class="card-body">
                    <?php if(empty($apps)): ?>
                        <p>No applications yet. <a href="apply.php" style="color:#FF8A00;">Start a new application.</a></p>
                    <?php else: foreach($apps as $a): ?>
                        <div class="app-item">
                            <strong><?=ucfirst($a['type'])?> Aid</strong>
                            <p>Status: <span class="app-status status-<?=strtolower($a['status'])?>"><?=htmlspecialchars($a['status'])?></span><br>
                            Requested: ₱<?=number_format($a['amount_requested'] ?? 0, 2)?></p>
                            <a href="view_application_user.php?id=<?=$a['id']?>" style="color:#FF8A00;">View details</a>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- Messages -->
            <section class="card">
                <div class="card-title">Recent Messages</div>
                <div class="card-body">
                    <?php if(empty($msgs)): ?>
                        <p>No recent messages.</p>
                    <?php else: foreach($msgs as $m): ?>
                        <div class="message-item">
                            <span class="msg-sender"><?=htmlspecialchars($m['sender'])?>:</span>
                            <span><?=htmlspecialchars(substr($m['message'], 0, 60)) . (strlen($m['message']) > 60 ? '...' : '')?></span>
                            <small class="msg-time"><?=htmlspecialchars($m['created_at'])?></small>
                            <a href="messages.php?id=<?=$m['id']?>" style="display:block; font-size: 0.9em; color:#FF8A00;">View</a>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- Doughnut Chart -->
            <section class="card">
                <div class="card-title">Applications Overview</div>
                <div class="card-body">
                    <canvas id="statusChart" style="max-height:300px;"></canvas>
                </div>
            </section>

            <!-- Monthly Trend Chart -->
            <section class="card">
                <div class="card-title">Monthly Requests Trend</div>
                <div class="card-body">
                    <canvas id="monthlyChart" style="max-height:300px;"></canvas>
                </div>
            </section>

        </main>
    </div>
</div>

<script>
// Doughnut Chart
const ctx1 = document.getElementById('statusChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Pending','Approved','Rejected','Completed'],
        datasets: [{
            data: <?=json_encode(array_values($status_counts))?>,
            backgroundColor: ['#FFA500','#28A745','#DC3545','#007BFF'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        animation: { animateScale:true, duration:1000 }
    }
});

// Monthly Requests Line Chart
const ctx2 = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Requests',
            data: <?=json_encode(array_values($monthly_counts))?>,
            fill: true,
            backgroundColor: 'rgba(255,138,0,0.2)',
            borderColor: '#FF8A00',
            tension: 0.3,
            pointBackgroundColor: '#FF8A00',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { mode:'index', intersect:false } },
        scales: { y: { beginAtZero: true, stepSize: 1 } },
        animation: { duration: 1200, easing: 'easeOutQuart' }
    }
});
</script>
</body>
</html>
