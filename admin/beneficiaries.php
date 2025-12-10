<?php
// admin/beneficiaries.php
require_once __DIR__ . '/../helpers.php';
require_admin();

// --- Beneficiary Data Query (Placeholder) ---
$beneficiaries = [
    ['id' => 501, 'last_name' => 'BALIGUAT', 'first_name' => 'JASON', 'barangay' => 'POBLACION', 'contact' => '0912-345-6789', 'aids_received' => 5],
    ['id' => 502, 'last_name' => 'DELA CRUZ', 'first_name' => 'MARIA', 'barangay' => 'LOOC', 'contact' => '0998-765-4321', 'aids_received' => 2],
    ['id' => 503, 'last_name' => 'GONZALES', 'first_name' => 'ANNA', 'barangay' => 'BANTAYAN', 'contact' => '0900-111-2222', 'aids_received' => 0],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Beneficiaries | Admin Panel</title>
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

/* Status color coding (kept from Aid History) */
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
            <a href="messages.php">Messages</a>
            <a href="aid_history.php">Aid History</a> 
            <a href="beneficiaries.php" class="active">Beneficiaries</a> </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Registered Beneficiaries</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">

            <div class="card section-card">
                <div class="card-title">Beneficiary List (<?= count($beneficiaries) ?> Total)</div>
                <div class="card-body">
                    <div class="data-table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Barangay</th>
                                    <th>Contact No.</th>
                                    <th>Aids Received</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($beneficiaries as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['id']) ?></td>
                                        <td><?= htmlspecialchars($b['last_name']) ?></td>
                                        <td><?= htmlspecialchars($b['first_name']) ?></td>
                                        <td><?= htmlspecialchars($b['barangay']) ?></td>
                                        <td><?= htmlspecialchars($b['contact']) ?></td>
                                        <td><?= htmlspecialchars($b['aids_received']) ?></td>
                                        <td>
                                            <a href="beneficiary_profile.php?id=<?= $b['id'] ?>">View Profile</a> | 
                                            <a href="beneficiary_new_aid.php?id=<?= $b['id'] ?>">New Aid</a>
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