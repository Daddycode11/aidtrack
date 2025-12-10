<?php
// admin/super_admin_dashboard.php
require_once '../helpers.php';
require_super_admin(); // function must check session user role == 'super_admin'

// basic stats
$totalUsers = get_count('users');
$totalBeneficiaries = get_count('users', "role='beneficiary'");
$totalApplications = get_count('applications');
$pending = get_count('applications', "status='pending'");


// budgets
$budgetRows = $mysqli->query("SELECT * FROM budgets")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Super Admin - AIDTRACK</title>
  <link href="../style.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <header><h2>Super Admin Dashboard</h2>
      <p>Welcome, <?=htmlspecialchars($_SESSION['user']['name'])?> — <a href="logout.php">Logout</a></p>
    </header>

    <section class="card">
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <div class="stat"><strong><?=$totalUsers?></strong><div>Users</div></div>
        <div class="stat"><strong><?=$totalBeneficiaries?></strong><div>Beneficiaries</div></div>
        <div class="stat"><strong><?=$totalApplications?></strong><div>Applications</div></div>
        <div class="stat"><strong><?=$pending?></strong><div>Pending</div></div>
      </div>
    </section>

    <section class="card">
      <h3>Budget / Policy</h3>
      <table style="width:100%;border-collapse:collapse">
        <thead><tr><th>Type</th><th>Max Amount</th><th>Reapply (months)</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($budgetRows as $b): ?>
            <tr>
              <td><?=htmlspecialchars($b['type'])?></td>
              <td>₱<?=number_format($b['max_amount'],2)?></td>
              <td><?=intval($b['reapply_interval_months'])?></td>
              <td><a href="edit_budget.php?id=<?=$b['id']?>">Edit</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="card">
      <h3>Admin Management</h3>
      <p><a href="manage_admins.php">Manage Admin Accounts</a></p>
    </section>

    <section class="card">
      <h3>System / Audit Logs</h3>
      <p><a href="audit_logs.php">View Logs</a></p>
    </section>
  </div>
</body>
</html>
