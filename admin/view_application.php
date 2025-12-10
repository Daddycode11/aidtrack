<?php
// admin/view_application.php
require_once '../helpers.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('dashboard.php');

$stmt = $mysqli->prepare("SELECT a.*, u.name, u.phone, u.barangay FROM applications a JOIN users u ON u.id = a.user_id WHERE a.id = ? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$app = $res->fetch_assoc();
$stmt->close();
if (!$app) redirect('dashboard.php');

// get docs
$stmt = $mysqli->prepare("SELECT * FROM documents WHERE application_id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$docs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $amt = floatval($_POST['amount_granted'] ?? 0);
        $note = trim($_POST['admin_note'] ?? '');
        $stmt = $mysqli->prepare("UPDATE applications SET status='approved', amount_granted=?, notes=CONCAT(IFNULL(notes,''), '\n\nAdmin note: ',?) WHERE id = ?");
        $stmt->bind_param('dsi',$amt,$note,$id);
        $stmt->execute();
        $stmt->close();
        // send a message
        $msg = "Your application #$id was approved. Please coordinate to claim. Admin note: $note";
        $stmt2 = $mysqli->prepare("INSERT INTO messages (user_id, sender, message) VALUES (?, 'admin', ?)");
        $stmt2->bind_param('is', $app['user_id'], $msg);
        $stmt2->execute();
        $stmt2->close();
        flash_set('success','Application approved.');
        redirect('dashboard.php');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['admin_note'] ?? '');
        $stmt = $mysqli->prepare("UPDATE applications SET status='rejected', notes=CONCAT(IFNULL(notes,''), '\n\nAdmin note: ',?) WHERE id = ?");
        $stmt->bind_param('si', $reason, $id);
        $stmt->execute();
        $stmt->close();
        $msg = "Your application #$id was rejected. Reason: $reason";
        $stmt2 = $mysqli->prepare("INSERT INTO messages (user_id, sender, message) VALUES (?, 'admin', ?)");
        $stmt2->bind_param('is', $app['user_id'], $msg);
        $stmt2->execute();
        $stmt2->close();
        flash_set('success','Application rejected.');
        redirect('dashboard.php');
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>View Application</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="container">
  <h2>Application #<?=htmlspecialchars($app['id'])?></h2>
  <p><a href="dashboard.php">&larr; Back</a></p>

  <div class="card">
    <strong><?=htmlspecialchars(ucfirst($app['type']))?></strong>
    <div>Applicant: <?=htmlspecialchars($app['name'])?> — <?=htmlspecialchars($app['phone'])?></div>
    <div>Barangay: <?=htmlspecialchars($app['barangay'])?></div>
    <div>Date of request: <?=htmlspecialchars($app['date_of_request'])?></div>
    <div>Status: <?=htmlspecialchars($app['status'])?></div>
    <div style="margin-top:8px"><?=nl2br(htmlspecialchars($app['notes']))?></div>
    <div style="margin-top:8px">
      <strong>Documents:</strong>
      <?php if (empty($docs)): ?> None <?php else: foreach($docs as $d): ?>
        <div><a href="../uploads/<?=rawurlencode($d['filename'])?>" target="_blank"><?=htmlspecialchars($d['original_name'])?></a></div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div class="card section">
    <h4>Admin Action</h4>
    <form method="post">
      <label>Amount to grant (₱)</label><br><input type="number" name="amount_granted" step="0.01" value="<?=htmlspecialchars($app['amount_granted'])?>"><br>
      <label>Admin note / reason</label><br><textarea name="admin_note"></textarea><br><br>
      <button type="submit" name="action" value="approve">Approve</button>
      <button type="submit" name="action" value="reject">Reject</button>
    </form>
  </div>
</div>
</body></html>
