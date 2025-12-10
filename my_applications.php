<?php
// my_applications.php
require_once 'helpers.php';
require_login();
$uid = $_SESSION['user']['id'];
$stmt = $mysqli->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i',$uid);
$stmt->execute();
$res = $stmt->get_result();
$apps = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>My Applications</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h2>My Applications</h2>
  <p><a href="submit_application.php">Submit New Request</a> | <a href="logout.php">Logout</a></p>
  <?php if (empty($apps)): ?>
    <div class="card">No applications yet.</div>
  <?php else: foreach($apps as $a): ?>
    <div class="card" style="margin-bottom:10px">
      <strong><?=htmlspecialchars(ucfirst($a['type']))?> request</strong>
      <div>Date: <?=htmlspecialchars($a['date_of_request'])?> | Status: <?=htmlspecialchars($a['status'])?></div>
      <div>Requested: ₱<?=number_format($a['amount_requested'],2)?> | Granted: ₱<?=number_format($a['amount_granted'],2)?></div>
      <div style="margin-top:8px"><?=nl2br(htmlspecialchars($a['notes']))?></div>
      <div style="margin-top:8px"><a href="view_application_user.php?id=<?=$a['id']?>">View details</a></div>
    </div>
  <?php endforeach; endif; ?>
</div>
</body></html>

