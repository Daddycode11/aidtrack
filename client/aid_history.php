<?php
// client/aid_history.php
require_once __DIR__ . '/../helpers.php';
require_client(); 

// Mocking the logged-in user's data for display/filter
$logged_in_user_name = $_SESSION['user']['name'] ?? 'Client User';
$logged_in_user_id = $_SESSION['user']['id'] ?? 999; 

// --- Aid History Data Query (Placeholder) ---
// In a real application, fetch from the database using $logged_in_user_id
$aid_history_data = [
    // Mock data for the current user (ID 999)
    [ 'No' => 100, 'user_id' => 999, 'Assistance' => 'Medical', 'Barangay' => 'POBLACION', 'Status' => 'PENDING', 'Date' => '2025-10-05', 'amount' => 5000.00 ],
    [ 'No' => 101, 'user_id' => 999, 'Assistance' => 'Educational', 'Barangay' => 'POBLACION', 'Status' => 'APPROVED', 'Date' => '2025-08-10', 'amount' => 2500.00 ],
    // Mock data for other users to simulate filtering (ID 100)
    [ 'No' => 102, 'user_id' => 100, 'Assistance' => 'Livelihood', 'Barangay' => 'BANTAYAN', 'Status' => 'REJECTED', 'Date' => '2025-07-01', 'amount' => 10000.00 ],
    [ 'No' => 103, 'user_id' => 999, 'Assistance' => 'Burial', 'Barangay' => 'POBLACION', 'Status' => 'REJECTED', 'Date' => '2025-05-20', 'amount' => 8000.00 ],
];

// Apply filter for the logged-in user
$filtered_data = array_filter($aid_history_data, function($aid) use ($logged_in_user_id) {
    return $aid['user_id'] === $logged_in_user_id;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Aid History | Client Panel</title>
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
/* Data Table */
.data-table-container { overflow-x: auto; }
.data-table-container table { width: 100%; border-collapse: collapse; min-width: 650px; }
.data-table-container th, .data-table-container td { padding: 0.9rem 1rem; border-bottom: 1px solid #eee; text-align: left; font-size: 0.9rem; }
.data-table-container th { background: #f8f8f8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #666; }
.data-table-container tr:hover { background: #f0f8ff; }
/* Status color coding */
td.status-pending { color: #FFA500; font-weight: 600; } 
td.status-approved { color: #28A745; font-weight: 600; } 
td.status-rejected { color: #DC3545; font-weight: 600; } 
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
            <a href="aid_history.php" class="active">
                <i class="fas fa-history"></i>Aid History
            </a>
            <a href="messages.php">
                <i class="fas fa-bell"></i>NOTIFICATION
            </a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>My Aid History</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">

            <div class="card">
                <div class="card-title">My Aid Applications (<?= count($filtered_data) ?> Records)</div>
                <div class="card-body">
                    <?php if(empty($filtered_data)): ?>
                        <p>You have no past or pending aid applications.</p>
                    <?php else: ?>
                        <div class="data-table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Application No</th>
                                        <th>Assistance Type</th>
                                        <th>Amount</th>
                                        <th>Barangay</th>
                                        <th>Date Applied</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($filtered_data as $aid): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($aid['No']) ?></td>
                                            <td><?= htmlspecialchars($aid['Assistance']) ?></td>
                                            <td>₱<?= number_format($aid['amount'], 2) ?></td>
                                            <td><?= htmlspecialchars($aid['Barangay']) ?></td>
                                            <td><?= htmlspecialchars(date('M d, Y', strtotime($aid['Date']))) ?></td>
                                            <td class="status-<?= strtolower($aid['Status']) ?>"><?= htmlspecialchars($aid['Status']) ?></td>
                                            <td>
                                                <a href="view_application_user.php?id=<?= $aid['No'] ?>">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>