<?php
// admin/application_view.php
require_once __DIR__ . '/../helpers.php';
require_admin();

if (!isset($_GET['id'])) {
    header("Location: applications.php");
    exit;
}

$app_id = (int)$_GET['id'];
$message = '';

// --- Handle status update ---
if (isset($_POST['action']) && in_array($_POST['action'], ['approve', 'reject'])) {
    $new_status = strtoupper($_POST['action']);
    if ($new_status === 'APPROVE') $new_status = 'APPROVED';
    $stmt = $mysqli->prepare("UPDATE applications SET status=? WHERE id=?");
    $stmt->bind_param('si', $new_status, $app_id);
    $stmt->execute();
    $stmt->close();
    $message = "<p style='color:green;'>Application has been $new_status.</p>";
}

// --- Fetch application details ---
$stmt = $mysqli->prepare("
    SELECT a.id, u.name AS user_name, u.email, a.type, a.amount, a.purpose, a.status, a.created_at
    FROM applications a
    JOIN users u ON a.client_id = u.id
    WHERE a.id=?
");
$stmt->bind_param('i', $app_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    header("Location: applications.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Details | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f4f6fc; margin:0; }
a { text-decoration:none; }
.app { display:flex; min-height:100vh; }
.sidebar { width:240px; background:#FFA500; color:#fff; display:flex; flex-direction:column; min-height:100vh; padding-top:1rem; }
.sidebar-logo { text-align:center; font-weight:700; font-size:1.8rem; margin-bottom:1rem; }
.sidebar-nav a { display:block; padding:0.8rem 1.5rem; color:#333; margin:0.2rem 1rem; border-radius:6px; background:#FFC04C; font-weight:500; transition:0.2s; }
.sidebar-nav a.active, .sidebar-nav a:hover { background:#fff; color:#FFA500; box-shadow:0 2px 5px rgba(0,0,0,0.2); }

.main { flex:1; display:flex; flex-direction:column; }
.header { display:flex; justify-content:space-between; align-items:center; padding:1rem 2rem; background:#fff; border-bottom:1px solid #e0e0e0; }
.header h1 { font-size:1.5rem; font-weight:600; }
.btn-logout { background:#DC3545; color:#fff; padding:0.5rem 1rem; border-radius:6px; }
.btn-logout:hover { background:#c82333; }

.content { padding:1.5rem 2rem; }
.card { background:#fff; padding:1.2rem 1.5rem; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); max-width:700px; margin:auto; }
.card-title { font-weight:600; font-size:1.2rem; margin-bottom:1rem; border-bottom:1px solid #eee; padding-bottom:0.5rem; }
.detail-row { margin-bottom:1rem; }
.detail-label { font-weight:600; color:#555; margin-bottom:0.3rem; }
.detail-value { padding:0.5rem 0.7rem; background:#f8f8f8; border-radius:4px; }

.status-pending { color:#FFA500; font-weight:600; }
.status-approved { color:#28A745; font-weight:600; }
.status-rejected { color:#DC3545; font-weight:600; }

.btn-action { padding:0.6rem 1.2rem; border:none; border-radius:6px; font-weight:600; cursor:pointer; margin-right:0.5rem; }
.btn-approve { background:#28A745; color:#fff; }
.btn-approve:hover { background:#218838; }
.btn-reject { background:#DC3545; color:#fff; }
.btn-reject:hover { background:#c82333; }

@media(max-width:768px){ .sidebar{width:100%;} .app{flex-direction:column;} .content{padding:1rem;} .card{width:100%;} }
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="applications.php">Applications</a>
            <a href="messages.php">Messages</a>
            <a href="aid_history.php">Aid History</a>
            <a href="beneficiaries.php">Beneficiaries</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Application Details</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">
            <div class="card">
                <div class="card-title">Request ID: <?= htmlspecialchars($app['id']) ?></div>
                <?= $message ?>
                <div class="detail-row">
                    <div class="detail-label">Applicant Name</div>
                    <div class="detail-value"><?= htmlspecialchars($app['user_name']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Applicant Email</div>
                    <div class="detail-value"><?= htmlspecialchars($app['email']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Assistance Type</div>
                    <div class="detail-value"><?= htmlspecialchars($app['type']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Requested Amount</div>
                    <div class="detail-value">₱ <?= number_format($app['amount'],2) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Purpose / Justification</div>
                    <div class="detail-value"><?= htmlspecialchars($app['purpose']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status</div>
                    <div class="detail-value status-<?= strtolower($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submitted At</div>
                    <div class="detail-value"><?= htmlspecialchars(date('M d, Y H:i', strtotime($app['created_at']))) ?></div>
                </div>

                <?php if($app['status'] === 'PENDING'): ?>
                <form method="POST" style="margin-top:1rem;">
                    <button type="submit" name="action" value="approve" class="btn-action btn-approve">Approve</button>
                    <button type="submit" name="action" value="reject" class="btn-action btn-reject">Reject</button>
                </form>
                <?php else: ?>
                    <p style="margin-top:1rem; font-weight:600;">This application has already been processed.</p>
                <?php endif; ?>
                <p style="margin-top:1rem;"><a href="applications.php">&larr; Back to Applications</a></p>
            </div>
        </main>
    </div>
</div>
</body>
</html>
