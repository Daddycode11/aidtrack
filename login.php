<?php
require_once 'helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$phone || !$password) {
        $errors[] = 'Phone and password are required.';
    } else {
        $mysqli->select_db(DB_NAME);

        $stmt = $mysqli->prepare("SELECT id, phone, name, password_hash, role FROM users WHERE phone = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $phone);
            $stmt->execute();
            $res = $stmt->get_result();
            $u = $res->fetch_assoc();
            $stmt->close();

            if (!$u || !password_verify($password, $u['password_hash'])) {
                $errors[] = 'Invalid phone or password.';
            } else {
                unset($u['password_hash']);
                $_SESSION['user'] = $u;

                switch ($u['role']) {
                    case 'super_admin':
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        exit;
                    case 'client':
                    default:
                        header('Location: client/dashboard.php');
                        exit;
                }
            }
        } else {
            $errors[] = 'Database query failed: ' . $mysqli->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | AidTrack</title>
<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
<style>
/* --- Reset & Body --- */
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; height:100vh; display:flex; justify-content:center; align-items:center; background:#1e1e2f; overflow:hidden; position:relative; }

/* --- Background Image & Overlay --- */
body::before {
    content:"";
    position:absolute; top:0; left:0; right:0; bottom:0;
    background:url('assets/images/BG.png') center/cover no-repeat;
    filter: brightness(0.5);
    z-index:-2;
}
body::after {
    content:""; position:absolute; top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.4);
    z-index:-1;
}

/* --- Container --- */
.container {
    position:relative;
    background:#fff;
    padding:2.5rem 2rem;
    border-radius:16px;
    width:100%;
    max-width:400px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    animation:fadeInUp 0.6s ease forwards;
    opacity:0;
}

/* --- Logo --- */
.logo { width:120px; margin:0 auto 1.5rem; }

/* --- Headings --- */
h2 { margin-bottom:1.5rem; color:#333; font-size:1.5rem; }

/* --- Form --- */
label { display:block; margin-bottom:0.5rem; font-weight:500; text-align:left; }
input { width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc; transition:0.3s; }
input:focus { border-color:#FF8A00; outline:none; box-shadow:0 0 5px rgba(255,138,0,0.3); }

button {
    width:100%; padding:0.7rem; border:none; border-radius:8px;
    background: #FF8A00;
    color:#fff; font-weight:600; font-size:1rem;
    cursor:pointer; transition:0.3s;
}
button:hover {
    background:#e07a00;
    transform: translateY(-2px);
}

/* --- Home Button --- */
.home-btn {
    position:absolute; top:20px; right:20px;
    background:#FF8A00;
    color:#fff; padding:0.5rem 1rem;
    border-radius:8px; font-weight:600; text-decoration:none;
    transition:0.3s; z-index:2;
}
.home-btn:hover { background:#e07a00; transform: translateY(-2px); }

/* --- Error Messages --- */
.error {
    background:#fff6f6; padding:8px; border:1px solid #ffdede;
    color:#8b0000; border-radius:6px; margin-bottom:1rem; text-align:left;
}

/* --- Animations --- */
@keyframes fadeInUp {
    0% { opacity:0; transform: translateY(20px); }
    100% { opacity:1; transform: translateY(0); }
}

/* --- Responsive --- */
@media(max-width:480px){
    .container { padding:1.8rem; border-radius:12px; }
    .logo { width:100px; }
    h2 { font-size:1.3rem; }
}
</style>
</head>
<body>

<a href="index.php" class="home-btn">Home</a>

<div class="container">
    <img src="assets/images/AIDTRACK-logo.png" alt="Logo" class="logo">
    <h2>Login</h2>

    <?php if ($errors): foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; endif; ?>

    <form method="post">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <div style="margin-bottom:1rem; text-align:left;">
            <label style="display:inline-flex;align-items:center;">
                <input type="checkbox" name="remember" style="margin-right:0.5rem;">
                Remember me
            </label>
        </div>

        <button type="submit">Log in</button>
    </form>

    <p>No account? <a href="register.php">Register</a></p>
    <p><a href="forgot-password.php">Forgot your password?</a></p>
</div>

</body>
</html>
