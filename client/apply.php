<?php 
// client/apply.php
require_once __DIR__ . '/../helpers.php';
require_client(); // ensure logged-in client

$message = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $notes = trim($_POST['notes'] ?? ''); // optional

    // Validate input
    if (!$type || !$amount) {
        $errors[] = 'Type and Amount are required.';
    } elseif (!is_numeric(str_replace(',', '', $amount))) {
        $errors[] = 'Amount must be a valid number.';
    }

    if (!$errors) {
        $user_id = $_SESSION['user']['id'];
        $clean_amount = str_replace(',', '', $amount);

        $stmt = $mysqli->prepare("
            INSERT INTO applications (user_id, type, amount_requested, notes, status, date_of_request, created_at)
            VALUES (?, ?, ?, ?, 'pending', CURDATE(), NOW())
        ");
        $stmt->bind_param('isds', $user_id, $type, $clean_amount, $notes);

        if ($stmt->execute()) {
            $application_id = $stmt->insert_id; // get inserted application ID

            // --- Document Upload Handling ---
            $required_docs = ['valid_id', 'birth_certificate', 'barangay_clearance'];
            $optional_docs = ['other_doc'];
            $all_docs = array_merge($required_docs, $optional_docs);

            $allowed_types = ['image/jpeg','image/png','application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $upload_dir = __DIR__ . '/../uploads/';

            foreach ($all_docs as $doc) {
                if (!empty($_FILES[$doc]['name'])) {
                    $file = $_FILES[$doc];
                    if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $safe_name = $doc . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $upload_dir . $safe_name)) {
                            $stmt_file = $mysqli->prepare("
                                INSERT INTO application_documents (application_id, document_type, filename, uploaded_at)
                                VALUES (?, ?, ?, NOW())
                            ");
                            $stmt_file->bind_param('iss', $application_id, $doc, $safe_name);
                            $stmt_file->execute();
                            $stmt_file->close();
                        } else {
                            $errors[] = "Failed to save uploaded file: $doc";
                        }
                    } else {
                        $errors[] = "Invalid file for $doc (type/size)";
                    }
                } elseif (in_array($doc, $required_docs)) {
                    $errors[] = ucfirst(str_replace('_',' ',$doc)) . ' is required.';
                }
            }
            // --- End Document Upload Handling ---

            if (!$errors) {
                $message = "<p style='color:green;'>Application submitted successfully! It is now pending review.</p>";
            }
        } else {
            $errors[] = "Failed to submit application: " . $mysqli->error;
        }
        $stmt->close();
    }
}

// Fetch client applications
$stmt2 = $mysqli->prepare("
    SELECT id, type, amount_requested AS amount, notes, status, date_of_request, created_at
    FROM applications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt2->bind_param('i', $_SESSION['user']['id']);
$stmt2->execute();
$applications = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>New Aid Request | Client Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
/* --- Existing Styles --- */
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

/* Content Area */
.content { padding: 1.5rem 2rem; }
.card { background: #fff; padding: 1.5rem 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.card-title { font-weight: 600; font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { font-weight: 600; display: block; margin-bottom: 0.5rem; color: #555; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px; }

/* Buttons */
.btn-submit { background: #007BFF; color: #fff; padding: 0.8rem 1.5rem; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-submit:hover { background: #0056b3; }

/* Application Table */
.applications-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.applications-table th, .applications-table td { border: 1px solid #eee; padding: 0.6rem 0.8rem; text-align: left; }
.applications-table th { background: #f8f8f8; }
.status-pending { color: #FFA500; font-weight: 600; }
.status-approved { color: #28A745; font-weight: 600; }
.status-rejected { color: #DC3545; font-weight: 600; }

/* Responsive */
@media(max-width:768px){
    .app { flex-direction: column; }
    .sidebar { width: 100%; }
    .sidebar-logo { display: none; }
    .sidebar-nav { flex-direction: row; flex-wrap: wrap; justify-content: space-around; margin-bottom: 1rem; }
    .sidebar-nav a { margin: 0.2rem; flex-grow: 1; justify-content: center; padding: 0.5rem 0.8rem; }
    .content { padding: 1rem; }
}
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="apply.php" class="active"><i class="fas fa-file-alt"></i>Type of Request</a>
            <a href="aid_history.php"><i class="fas fa-history"></i>Aid History</a>
            <a href="messages.php"><i class="fas fa-bell"></i>Notification</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Submit New Request</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">
            <!-- Form Card -->
            <div class="card">
                <div class="card-title">Submit a New Request for Assistance</div>
                <?php foreach($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
                <?= $message ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="type">Assistance Type</label>
                        <select id="type" name="type" required>
                            <option value="">-- Select Type --</option>
                            <option value="burial">Burial Assistance</option>
                            <option value="medical">Medical Assistance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Requested Amount (₱)</label>
                        <input type="text" id="amount" name="amount" placeholder="e.g., 5,000.00" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes / Justification (optional)</label>
                        <textarea id="notes" name="notes" rows="4"></textarea>
                    </div>

                    <!-- Labeled Document Uploads -->
                    <div class="form-group">
                        <label>Upload Documents (JPG, PNG, PDF | Max 5MB each)</label>
                        
                        <label for="valid_id">Valid ID</label>
                        <input type="file" id="valid_id" name="valid_id" required>
                        
                        <label for="birth_certificate">Birth Certificate</label>
                        <input type="file" id="birth_certificate" name="birth_certificate" required>
                        
                        <label for="barangay_clearance">Barangay Clearance</label>
                        <input type="file" id="barangay_clearance" name="barangay_clearance" required>
                        
                        <label for="other_doc">Other Document (optional)</label>
                        <input type="file" id="other_doc" name="other_doc">
                    </div>

                    <button type="submit" class="btn-submit">Submit Request</button>
                </form>
            </div>

            <!-- Previous Applications -->
            <?php if($applications): ?>
            <div class="card">
                <div class="card-title">My Previous Applications</div>
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($applications as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['type']) ?></td>
                            <td><?= number_format($app['amount'],2) ?></td>
                            <td><?= htmlspecialchars($app['notes']) ?></td>
                            <td class="status-<?= strtolower($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></td>
                            <td><?= htmlspecialchars($app['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>
