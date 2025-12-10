<?php
// submit_application.php
require_once 'helpers.php';
require_login();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'burial';
    $date_of_request = $_POST['date_of_request'] ?? date('Y-m-d');
    $amount_requested = floatval($_POST['amount_requested'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // insert application
    $stmt = $mysqli->prepare("INSERT INTO applications (user_id,type,date_of_request,amount_requested,notes) VALUES (?,?,?,?,?)");
    $uid = $_SESSION['user']['id'];
    $stmt->bind_param('issds', $uid, $type, $date_of_request, $amount_requested, $notes);
    if ($stmt->execute()) {
        $app_id = $stmt->insert_id;
        // handle file uploads (allow multiple)
        if (!empty($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
            // normalize files
            for ($i=0;$i<count($_FILES['documents']['name']);$i++) {
                $file = [
                    'name'=>$_FILES['documents']['name'][$i],
                    'type'=>$_FILES['documents']['type'][$i],
                    'tmp_name'=>$_FILES['documents']['tmp_name'][$i],
                    'error'=>$_FILES['documents']['error'][$i],
                    'size'=>$_FILES['documents']['size'][$i]
                ];
                $res = handle_upload($file);
                if ($res) {
                    $stmt2 = $mysqli->prepare("INSERT INTO documents (application_id, filename, original_name) VALUES (?,?,?)");
                    $stmt2->bind_param('iss', $app_id, $res['saved'], $res['original']);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
        }
        flash_set('success','Application submitted successfully.');
        redirect('my_applications.php');
    } else {
        $errors[] = 'Error submitting: ' . $stmt->error;
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Submit Application</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h2>Submit Request</h2>
  <?php if ($m = flash_get('success')): ?><div class="card" style="background:#e6ffed;padding:10px;border:1px solid #c6f6d5"><?=htmlspecialchars($m)?></div><?php endif; ?>
  <?php if ($errors): foreach($errors as $e): ?>
    <div class="card" style="background:#fff6f6;padding:8px;border:1px solid #ffdede;color:#8b0000"><?=htmlspecialchars($e)?></div>
  <?php endforeach; endif; ?>

  <form method="post" enctype="multipart/form-data">
    <label>Type</label><br>
    <select name="type">
      <option value="burial">Burial</option>
      <option value="medical">Medical</option>
    </select><br>
    <label>Date of Request</label><br><input type="date" name="date_of_request" value="<?=date('Y-m-d')?>"><br>
    <label>Amount Requested (₱)</label><br><input name="amount_requested" type="number" step="0.01"><br>
    <label>Notes</label><br><textarea name="notes"></textarea><br>
    <label>Documents (PDF, JPG, PNG) — multiple allowed</label><br>
    <input type="file" name="documents[]" multiple accept=".pdf,image/*"><br><br>
    <button type="submit">Submit</button>
  </form>
  <p><a href="my_applications.php">Back to my applications</a></p>
</div>
</body></html>
