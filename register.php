<?php
require_once __DIR__ . '/helpers.php'; // Adjusted path

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (!$phone || !$name || !$password || !$confirm_password) {
        $errors[] = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    } else {
        $mysqli->select_db(DB_NAME);

        // Check if phone already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Phone number is already registered.';
        } else {
            // Insert new user as client
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $mysqli->prepare("INSERT INTO users (phone, name, barangay, password_hash, role) VALUES (?, ?, ?, ?, 'client')");
            $stmt2->bind_param('ssss', $phone, $name, $barangay, $hash);
            if ($stmt2->execute()) {
                // Auto-login after registration
                $user_id = $stmt2->insert_id;
                $_SESSION['user'] = [
                    'id' => $user_id,
                    'phone' => $phone,
                    'name' => $name,
                    'barangay' => $barangay,
                    'role' => 'client'
                ];
                $stmt2->close();
                header('Location: client/dashboard.php');
                exit;
            } else {
                $errors[] = 'Registration failed: ' . $mysqli->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    font-family:'Poppins',sans-serif;
    margin:0; padding:0;
    height:100vh; display:flex;
    justify-content:center; align-items:center;
    position:relative; overflow:hidden;
}
body::before {
    content:"";
    position:absolute; top:0; left:0; right:0; bottom:0;
    background:url('assets/images/BG.png') center/cover no-repeat;
    opacity:0.2; z-index:-1;
}
body::after {
    content:"";
    position:absolute; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.3); z-index:0;
}
.container {
    position:relative; z-index:1;
    background:#fff; padding:2rem;
    border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);
    width:100%; max-width:400px; text-align:center;
}
.logo { width:120px; margin:0 auto 1.5rem; }
h2 { margin-bottom:1rem; color:#333; }
label { display:block; margin-bottom:0.5rem; font-weight:500; text-align:left; }
input { width:100%; padding:0.5rem; margin-bottom:1rem; border-radius:6px; border:1px solid #ccc; }
button {
    width:100%; padding:0.7rem; border:none; border-radius:8px;
    background:#FF8A00; /* Updated button color */
    color:#fff; font-weight:600; font-size:1rem;
    cursor:pointer; transition:0.3s;
}
button:hover { background:#e07a00; } /* Hover effect */
.error {
    background:#fff6f6; padding:8px;
    border:1px solid #ffdede; color:#8b0000;
    border-radius:6px; margin-bottom:1rem;
}
p { margin-top:1rem; }
a { color:#FF8A00; text-decoration:none; font-weight:500; }
a:hover { text-decoration:underline; }
@media(max-width:480px){ .container { padding:1.5rem; } .logo { width:100px; } }
.home-btn {
    position:absolute; top:20px; right:20px;
    background:#FF8A00; /* Updated home button color */
    color:#fff; padding:0.5rem 1rem;
    border-radius:8px; text-decoration:none; font-weight:600;
    transition:0.3s; z-index:2;
}
.home-btn:hover { background:#e07a00; }
</style>
</head>
<body>
<div class="container">
  <img src="assets/images/AIDTRACK-logo.png" alt="Logo" class="logo">
  <a href="index.php" class="home-btn">Home</a>
  <h2>Register</h2>

  <?php if ($errors): foreach ($errors as $e): ?>
    <div class="error"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; endif; ?>

  <form method="post">
    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required autofocus>

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

    <label for="barangay">Barangay</label>
    <input type="text" id="barangay" name="barangay" value="<?= htmlspecialchars($_POST['barangay'] ?? '') ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <button type="submit">Register</button>
  </form>

  <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
