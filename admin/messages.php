<?php
// admin/messages.php
require_once __DIR__ . '/../helpers.php';
require_admin();

// --- Message Data Query (Placeholder) ---
$messages = [
    ['id' => 301, 'sender' => 'jason.b@email.com', 'subject' => 'Follow up on my medical aid', 'created_at' => '2025-10-06 10:00:00', 'is_read' => false],
    ['id' => 302, 'sender' => 'maria.d@email.com', 'subject' => 'Thank You for the approval!', 'created_at' => '2025-10-05 15:30:00', 'is_read' => true],
    ['id' => 303, 'sender' => 'anna.g@email.com', 'subject' => 'Appeal for rejected livelihood aid', 'created_at' => '2025-10-04 09:15:00', 'is_read' => false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
/* --- Admin Dashboard CSS (Self-Contained) --- */

/* Global Reset & Font */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { 
    font-family: 'Poppins', sans-serif; 
    background: #f4f6fc; /* Very light background */
    color: #333; 
}
a { text-decoration: none; }

/* App Layout */
.app { display: flex; min-height: 100vh; }

/* Sidebar - Using colors from the MONITOR PANEL image */
.sidebar {
    width: 240px;
    background-color: #FFA500; /* Orange color from the image */
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}
.sidebar-logo {
    font-size: 1.8rem;
    text-align: center;
    margin: 1.5rem 0;
    font-weight: 700;
    letter-spacing: 1px;
    color: #fff;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 0.9rem 1.5rem;
    color: #333; /* Dark text for better contrast on orange */
    border-radius: 6px;
    margin: 0.3rem 1rem;
    transition: 0.2s;
    background-color: #FFC04C; /* Lighter orange for normal state */
    font-weight: 500;
}
.sidebar-nav a.active,
.sidebar-nav a:hover { 
    background-color: #fff; /* White background on active/hover */
    color: #FFA500; /* Orange text on active/hover */
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Main content */
.main { flex: 1; display: flex; flex-direction: column; }
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 2rem;
    background-color: #fff;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.header h1 { font-size: 1.5rem; font-weight: 600; }
.btn-logout {
    background-color: #DC3545; /* Red color for logout */
    color: #fff;
    padding: 0.5rem 1.2rem;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    transition: 0.2s;
}
.btn-logout:hover { background-color: #c82333; }

/* Main Content Area */
.content { padding: 1.5rem 2rem; }

/* Card styles */
.card {
    background: #fff;
    padding: 1.2rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
}
.section-card .card-title { 
    font-weight: 600; 
    font-size: 1.2rem; 
    margin-bottom: 1rem; 
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}
.card-body { 
    max-height: 70vh; 
    overflow-y: auto; 
}

/* Data Table */
.data-table-container { overflow-x: auto; }
.data-table-container table { width: 100%; border-collapse: collapse; min-width: 700px; } /* Adjusted min-width */
.data-table-container th, .data-table-container td { padding: 0.9rem 1rem; border-bottom: 1px solid #eee; text-align: left; font-size: 0.9rem; }
.data-table-container th { background: #f8f8f8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #666; }
.data-table-container tr:hover { background: #f0f8ff; }

/* Custom Status color coding for Messages */
td.status-false { color: #DC3545; font-weight: 700; } /* Unread */
td.status-true { color: #28A745; font-weight: 500; } /* Read */

/* Generic Status color coding (kept from Aid History) */
td.status-pending { color: #FFA500; font-weight: 600; } 
td.status-approved { color: #28A745; font-weight: 600; } 
td.status-rejected { color: #DC3545; font-weight: 600; } 

/* Responsive adjustments */
@media (max-width: 1024px) {
    .data-section-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; min-height: unset; border-right: none; }
    .app { flex-direction: column; }
    .sidebar-nav { display: flex; flex-wrap: wrap; justify-content: space-around; margin: 0 0 1rem 0; }
    .sidebar-nav a { margin: 0.2rem; padding: 0.5rem 1rem; flex-grow: 1; justify-content: center;}
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
            <a href="dashboard.php">Dashboard</a>
            <a href="user.php">Users</a>
            <a href="applications.php">Applications</a>
            <a href="messages.php" class="active">Messages</a> <a href="aid_history.php">Aid History</a> 
            <a href="beneficiaries.php">Beneficiaries</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Messages Inbox</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">

            <div class="card section-card">
                <div class="card-title">Messages (<?= count($messages) ?> Total)</div>
                <div class="card-body">
                    <div class="data-table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Sender</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($messages as $msg): ?>
                                    <tr>
                                        <td class="status-<?= $msg['is_read'] ? 'true' : 'false' ?>"><?= $msg['is_read'] ? 'Read' : 'Unread' ?></td>
                                        <td><?= htmlspecialchars($msg['sender']) ?></td>
                                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($msg['created_at']))) ?></td>
                                        <td>
                                            <a href="message_view.php?id=<?= $msg['id'] ?>">View</a> | 
                                            <a href="message_delete.php?id=<?= $msg['id'] ?>" onclick="return confirm('Delete this message?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>