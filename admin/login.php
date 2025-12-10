<?php
// admin/login.php
require_once '../helpers.php';
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    redirect('dashboard.php');
}
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT id,phone,name,password_hash,role FROM users WHERE phone = ? AND role='admin' LIMIT 1");
    $stmt->bind_param('s',$phone);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    $stmt->close();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        $errors[] = 'Invalid admin credentials.';
    } else {
        unset($u['password_hash']);
        $_SESSION['user'] = $u;
        redirect('dashboard.php');
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin Login</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="container">
  <h2>Admin Login</h2>
  <?php if ($errors) foreach($errors as $e): ?>
    <div class="card" style="background:#fff6f6;padding:8px;border:1px solid #ffdede;color:#8b0000"><?=htmlspecialchars($e)?></div>
  <?php endforeach; ?>
  <form method="post">
    <label>Phone</label><br><input name="phone" required><br>
    <label>Password</label><br><input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
  </form>
</div>
</body></html>
