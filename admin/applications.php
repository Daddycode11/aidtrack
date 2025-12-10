<?php
// admin/applications.php
require_once __DIR__ . '/../helpers.php';
require_admin();

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $action = strtolower($_POST['action']);
    $app_id = intval($_POST['id']);
    $admin_id = $_SESSION['admin_id']; // must exist from login

    // REJECT with reason
    if ($action === 'rejected') {
        $reason = trim($_POST['reason']);

        $stmt = $mysqli->prepare("UPDATE applications SET status='rejected', rejection_reason=? WHERE id=?");
        $stmt->bind_param('si', $reason, $app_id);
        $stmt->execute();
        $stmt->close();

        // log action
        $stmt = $mysqli->prepare("INSERT INTO admin_actions (application_id, admin_id, action, details) VALUES (?, ?, 'reject', ?)");
        $stmt->bind_param('iis', $app_id, $admin_id, $reason);
        $stmt->execute();
        $stmt->close();

        // notify user
        $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES ((SELECT user_id FROM applications WHERE id=?), ?)");
        $msg = "Your application has been rejected for the following reason: $reason";
        $stmt->bind_param('is', $app_id, $msg);
        $stmt->execute();
        $stmt->close();

        header("Location: applications.php");
        exit;
    }

    // APPROVE without amount release yet
    if ($action === 'approved') {
        $stmt = $mysqli->prepare("UPDATE applications SET status='approved', rejection_reason=NULL WHERE id=?");
        $stmt->bind_param('i', $app_id);
        $stmt->execute();
        $stmt->close();

        // log action
        $stmt = $mysqli->prepare("INSERT INTO admin_actions (application_id, admin_id, action) VALUES (?, ?, 'approve')");
        $stmt->bind_param('ii', $app_id, $admin_id);
        $stmt->execute();
        $stmt->close();

        // notify user
        $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES ((SELECT user_id FROM applications WHERE id=?), 'Your loan application has been approved.')");
        $stmt->bind_param('i', $app_id);
        $stmt->execute();
        $stmt->close();

        header("Location: applications.php");
        exit;
    }
}

// Fetch all applications with client name
$result = $mysqli->query("
    SELECT a.id, a.type, a.amount_requested, a.notes, a.status, a.date_of_request, a.created_at,
           u.name AS client_name
    FROM applications a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
");

$applications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applications | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* --- Admin Dashboard CSS --- */
* { box-sizing: border-box; margin: 0; padding: 0; }
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
.card { background:#fff; padding:1.2rem 1.5rem; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.card-title { font-weight:600; font-size:1.2rem; margin-bottom:1rem; }
.data-table-container { overflow-x:auto; }
.data-table-container table { width:100%; border-collapse:collapse; min-width:650px; }
.data-table-container th, .data-table-container td { padding:0.9rem 1rem; border-bottom:1px solid #eee; text-align:left; font-size:0.9rem; }
td.status-pending { color:#FFA500; font-weight:600; }
td.status-approved { color:#28A745; font-weight:600; }
td.status-rejected { color:#DC3545; font-weight:600; }
a.action-btn, button.action-btn { margin-right:0.5rem; padding:0.3rem 0.6rem; border-radius:4px; font-size:0.85rem; color:#fff; border:none; cursor:pointer; }
a.approve, button.approve { background:#28A745; }
a.reject, button.reject { background:#DC3545; }
.data-table-container tr:hover { background:#f0f8ff; }

/* Modal styles */
#rejectModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:999; }
#rejectModal .modal-content { background:#fff; padding:1.5rem; border-radius:10px; width:90%; max-width:400px; position:relative; }
#rejectModal h3 { margin-bottom:0.5rem; }
#rejectModal textarea { width:100%; padding:0.5rem; margin-bottom:1rem; border-radius:5px; border:1px solid #ccc; resize:none; }

/* Responsive */
@media(max-width:1024px) { .data-table-container table { min-width:550px; } }
@media(max-width:768px) { .sidebar { width:100%; height:auto; min-height:unset; border-right:none; } .app { flex-direction:column; } .sidebar-nav { display:flex; flex-wrap:wrap; justify-content:space-around; margin:0 0 1rem 0; } .sidebar-nav a { margin:0.2rem; padding:0.5rem 1rem; flex-grow:1; justify-content:center;} .sidebar-logo { display:none; } .content { padding:1rem; } }
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="user.php">Users</a>
            <a href="applications.php" class="active">Applications</a>
            <a href="messages.php">Messages</a>
            <a href="aid_history.php">Aid History</a> 
            <a href="beneficiaries.php">Beneficiaries</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>All Applications</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">
            <div class="card">
                <div class="card-title">Applications Queue (<?= count($applications) ?>)</div>
                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client Name</th>
                                <th>Type</th>
                                <th>Amount Requested</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($applications as $app): ?>
                            <tr>
                                <td><?= $app['id'] ?></td>
                                <td><?= htmlspecialchars($app['client_name']) ?></td>
                                <td><?= htmlspecialchars($app['type']) ?></td>
                                <td><?= number_format($app['amount_requested'], 2) ?></td>
                                <td><?= htmlspecialchars($app['notes'] ?? '') ?></td>
                                <td class="status-<?= strtolower($app['status']) ?>"><?= ucfirst($app['status']) ?></td>
                                <td>
                                    <?php if($app['status'] === 'pending'): ?>
                                    <!-- Approve Form -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="approved">
                                        <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                        <button type="submit" class="action-btn approve">Approve</button>
                                    </form>

                                    <!-- Reject Button triggers modal -->
                                    <button class="action-btn reject" onclick="openRejectModal(<?= $app['id'] ?>, '<?= htmlspecialchars($app['client_name'], ENT_QUOTES) ?>')">Reject</button>
                                    <?php else: ?>
                                    <em>Action completed</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal">
    <div class="modal-content">
        <h3>Reject Application</h3>
        <p id="rejectClientName"></p>
        <form method="post">
            <input type="hidden" name="action" value="rejected">
            <input type="hidden" name="id" id="rejectAppId" value="">
            <textarea name="reason" placeholder="Enter rejection reason" required rows="4"></textarea>
            <div style="text-align:right;">
                <button type="button" onclick="closeRejectModal()" style="margin-right:0.5rem; background:#6c757d; color:#fff; padding:0.5rem 1rem; border:none; border-radius:5px;">Cancel</button>
                <button type="submit" style="background:#dc3545; color:#fff; padding:0.5rem 1rem; border:none; border-radius:5px;">Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
// Open modal and populate data
function openRejectModal(appId, clientName) {
    document.getElementById('rejectAppId').value = appId;
    document.getElementById('rejectClientName').textContent = `Are you sure you want to reject ${clientName}'s application?`;
    document.getElementById('rejectModal').style.display = 'flex';
}

// Close modal
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
</body>
</html>
