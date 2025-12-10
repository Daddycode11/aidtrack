<?php
// client/messages.php
require_once __DIR__ . '/../helpers.php';
require_client(); 

// Mocking the logged-in user's data
$logged_in_user_id = $_SESSION['user']['id'] ?? 999; 

// --- Message Data Query (Placeholder) ---
// In a real application, fetch messages WHERE user_id = $logged_in_user_id
$messages = [
    ['id' => 301, 'user_id' => 999, 'sender' => 'Admin', 'subject' => 'Your application #100 is PENDING.', 'message' => 'The medical application you submitted on Oct 5 is currently pending review by the social worker.', 'created_at' => '2025-10-06 10:00:00', 'is_read' => false],
    ['id' => 302, 'user_id' => 999, 'sender' => 'System', 'subject' => 'Application #101 has been APPROVED.', 'message' => 'The educational aid requested on Aug 10 has been approved. Please wait for disbursement details.', 'created_at' => '2025-08-15 15:30:00', 'is_read' => true],
    ['id' => 303, 'user_id' => 999, 'sender' => 'Admin', 'subject' => 'Application #103 has been REJECTED.', 'message' => 'The burial aid request was rejected due to incomplete documentation. Please contact our office.', 'created_at' => '2025-05-21 09:15:00', 'is_read' => false],
];

// In a real app, this filtering is done by the SQL query
$filtered_messages = array_filter($messages, function($msg) use ($logged_in_user_id) {
    return $msg['user_id'] === $logged_in_user_id;
});

// Optionally, mark messages as read if the user visits this page
// if (!empty($mysqli) && !empty($filtered_messages)) {
//     $mysqli->query("UPDATE messages SET is_read = TRUE WHERE user_id = {$logged_in_user_id}");
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Notifications | Client Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
/* --- Client Dashboard CSS (Self-Contained) --- */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Poppins', sans-serif; background: #f4f6fc; color: #333; }
a { text-decoration: none; }
.app { display: flex; min-height: 100vh; }
.sidebar { width: 240px; background-color: #FFA500; color: #fff; display: flex; flex-direction: column; min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
.sidebar-logo { font-size: 1.8rem; text-align: center; margin: 1.5rem 0; font-weight: 700; letter-spacing: 1px; color: #fff; }
.sidebar-nav a { display: flex; align-items: center; padding: 0.9rem 1.5rem; color: #333; border-radius: 6px; margin: 0.3rem 1rem; transition: 0.2s; background-color: #FFC04C; font-weight: 500; }
.sidebar-nav a i { margin-right: 10px; font-size: 1.1em; }
.sidebar-nav a.active, .sidebar-nav a:hover { background-color: #fff; color: #FFA500; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
.main { flex: 1; display: flex; flex-direction: column; }
.header { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 2rem; background-color: #fff; border-bottom: 1px solid #e0e0e0; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.header h1 { font-size: 1.5rem; font-weight: 600; }
.btn-logout { background-color: #DC3545; color: #fff; padding: 0.5rem 1.2rem; border: none; border-radius: 6px; font-weight: 500; transition: 0.2s; }
.btn-logout:hover { background-color: #c82333; }
.content { padding: 1.5rem 2rem; }
.card { background: #fff; padding: 1.2rem 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.card-title { font-weight: 600; font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
.card-body { max-height: 70vh; overflow-y: auto; }
/* Message List */
.message-item { 
    padding: 0.8rem 0; 
    border-bottom: 1px solid #eee; 
    display: flex;
    flex-direction: column;
}
.message-item:last-child {
    border-bottom: none;
}
.msg-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}
.msg-sender { color: #4F46E5; }
.msg-unread .msg-sender, .msg-unread .msg-subject { font-weight: 700; color: #000; }
.msg-subject { margin-top: 0.2rem; }
.msg-time { font-size: 0.75rem; color: #999; }
.msg-text { margin: 0.5rem 0; color: #555; }
/* Responsive adjustments */
@media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; min-height: unset; border-right: none; }
    .app { flex-direction: column; }
    .sidebar-nav { display: flex; flex-wrap: wrap; justify-content: space-around; margin: 0 0 1rem 0; }
    .sidebar-nav a { margin: 0.2rem; padding: 0.5rem 1rem; flex-grow: 1; justify-content: center;}
    .sidebar-nav a i { margin-right: 5px; }
    .sidebar-logo { display: none; }
    .content { padding: 1rem; }
}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">AidTrack</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="apply.php">
                <i class="fas fa-file-alt"></i>TYPE OF REQUEST
            </a>
            <a href="aid_history.php">
                <i class="fas fa-history"></i>Aid History
            </a>
            <a href="messages.php" class="active">
                <i class="fas fa-bell"></i>NOTIFICATION
            </a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Notifications</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">

            <div class="card">
                <div class="card-title">My Messages (<?= count($filtered_messages) ?> Total)</div>
                <div class="card-body">
                    <?php if(empty($filtered_messages)): ?>
                        <p>You have no messages.</p>
                    <?php else: foreach($filtered_messages as $m): ?>
                        <div class="message-item <?= $m['is_read'] ? '' : 'msg-unread' ?>">
                            <div class="msg-header">
                                <span class="msg-sender"><?= htmlspecialchars($m['sender']) ?></span>
                                <small class="msg-time"><?= htmlspecialchars(date('M d, Y H:i', strtotime($m['created_at']))) ?></small>
                            </div>
                            <span class="msg-subject"><?= htmlspecialchars($m['subject']) ?></span>
                            <p class="msg-text"><?= htmlspecialchars(substr($m['message'], 0, 100)) . (strlen($m['message']) > 100 ? '...' : '') ?></p>
                            <a href="view_message.php?id=<?=$m['id']?>" style="color:#007BFF; font-size: 0.9em;">View Full Message</a>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>