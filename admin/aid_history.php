<?php
// admin/aid_history.php
require_once __DIR__ . '/../helpers.php';
require_admin(); // ensure admin or super_admin

// --- Filter and Search Logic ---
// Initialize filter variables
$search_lastname = $_GET['last_name'] ?? '';
$search_firstname = $_GET['first_name'] ?? '';
$search_middlename = $_GET['middle_name'] ?? '';
$filter_barangay = $_GET['barangay'] ?? '';
$filter_status = $_GET['status'] ?? '';

// --- Aid History Query (Placeholder) ---
// In a real application, you would use these filter variables to construct a dynamic SQL query.
// Example: "SELECT * FROM beneficiaries_aid WHERE Last_Name LIKE ? AND Status = ? ..."

// Placeholder data structure for full table
$aid_history_data = [
    // Copying the pattern from your AID HISTORY.png
    [ 'No' => 100, 'Last_Name' => 'BALIGUAT', 'First_Name' => 'JASON', 'Middle_Name' => 'ORSOS', 'Barangay' => 'POBLACION', 'Municipality' => 'CALINTAAN', 'Status' => 'PENDING', 'Assistance' => 'MEDICAL', 'Date' => 'Oct 5, 2025'],
    [ 'No' => 101, 'Last_Name' => 'DELA CRUZ', 'First_Name' => 'MARIA', 'Middle_Name' => 'SANTOS', 'Barangay' => 'LOOC', 'Municipality' => 'CALINTAAN', 'Status' => 'APPROVED', 'Assistance' => 'EDUCATIONAL', 'Date' => 'Oct 4, 2025'],
    [ 'No' => 102, 'Last_Name' => 'GONZALES', 'First_Name' => 'ANNA', 'Middle_Name' => 'FERNANDEZ', 'Barangay' => 'BANTAYAN', 'Municipality' => 'CALINTAAN', 'Status' => 'REJECTED', 'Assistance' => 'LIVELIHOOD', 'Date' => 'Oct 3, 2025'],
    // ... add many more rows from your database ...
];

// --- Mock filter application (for display purposes only) ---
// If this were real, you would do this filtering in the SQL query for performance.
$filtered_data = array_filter($aid_history_data, function($aid) use ($search_lastname, $search_firstname, $search_middlename, $filter_status, $filter_barangay) {
    $match_lastname = empty($search_lastname) || stripos($aid['Last_Name'], $search_lastname) !== false;
    $match_firstname = empty($search_firstname) || stripos($aid['First_Name'], $search_firstname) !== false;
    $match_middlename = empty($search_middlename) || stripos($aid['Middle_Name'], $search_middlename) !== false;
    $match_status = empty($filter_status) || $aid['Status'] === $filter_status;
    $match_barangay = empty($filter_barangay) || $aid['Barangay'] === $filter_barangay;

    return $match_lastname && $match_firstname && $match_middlename && $match_status && $match_barangay;
});

// Mock values for select dropdowns
$available_barangays = ['POBLACION', 'LOOC', 'BANTAYAN', 'BAYAN'];
$available_statuses = ['PENDING', 'APPROVED', 'REJECTED'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aid History | Admin Panel</title>
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

/* Stats grid (used for cards, but not on this page) */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}
.card {
    background: #fff;
    padding: 1.2rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
}
.stat-card {
    border-left: 5px solid #007BFF;
}
.stat-card .card-title { 
    font-size: 0.9rem; 
    font-weight: 500; 
    text-transform: uppercase; 
    color: #555; 
}
.stat-card .card-value { 
    font-size: 2.2rem; 
    font-weight: 700; 
    margin-top: 0.3rem;
    color: #007BFF;
}

/* Section cards */
.data-section-grid {
    display: grid;
    grid-template-columns: 1fr 1fr; 
    gap: 1.5rem;
}
.section-card { height: 100%; } 
.section-card .card-title { 
    font-weight: 600; 
    font-size: 1.2rem; 
    margin-bottom: 1rem; 
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}
.card-body { 
    max-height: 70vh; /* Set height for scrolling content */
    overflow-y: auto; 
}

/* Beneficiary Table */
.data-table-container { overflow-x: auto; }
.data-table-container table { width: 100%; border-collapse: collapse; min-width: 900px; } /* Ensures full table is visible without crushing columns */
.data-table-container th, .data-table-container td { padding: 0.9rem 1rem; border-bottom: 1px solid #eee; text-align: left; font-size: 0.9rem; }
.data-table-container th { background: #f8f8f8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #666; }
.data-table-container tr:hover { background: #f0f8ff; }

/* Filter/Search Form Styles */
.filter-form {
    background: #fff;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-form label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #555;
    margin-bottom: 0.3rem;
}
.filter-form input[type="text"],
.filter-form select {
    padding: 0.5rem 0.8rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    width: 100%;
    min-width: 150px;
}
.btn-search {
    background-color: #007BFF;
    color: #fff;
    padding: 0.6rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.2s;
}
.btn-search:hover {
    background-color: #0056b3;
}

/* Status color coding */
td.status-pending { color: #FFA500; font-weight: 600; } /* Orange */
td.status-approved { color: #28A745; font-weight: 600; } /* Green */
td.status-rejected { color: #DC3545; font-weight: 600; } /* Red */


/* Responsive adjustments */
@media (max-width: 1024px) {
    .data-section-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .filter-form { justify-content: space-between; }
    .filter-group { flex-grow: 1; min-width: 45%; }
}
@media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; min-height: unset; border-right: none; }
    .app { flex-direction: column; }
    .sidebar-nav { display: flex; flex-wrap: wrap; justify-content: space-around; margin: 0 0 1rem 0; }
    .sidebar-nav a { margin: 0.2rem; padding: 0.5rem 1rem; flex-grow: 1; justify-content: center;}
    .sidebar-logo { display: none; }
    .content { padding: 1rem; }
    .filter-group { min-width: 100%; }
    .btn-search { width: 100%; }
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
            <a href="aid_history.php" class="active">Aid History</a> 
            <a href="beneficiaries.php">Beneficiaries</a>
        </nav>
    </aside>

    <div class="main">
        <header class="header">
            <h1>Aid History</h1>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </header>

        <main class="content">

            <form method="GET" action="aid_history.php" class="filter-form">
                <div class="filter-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($search_lastname) ?>">
                </div>
                <div class="filter-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($search_firstname) ?>">
                </div>
                <div class="filter-group">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($search_middlename) ?>">
                </div>
                <div class="filter-group">
                    <label for="barangay">Filter by: Barangay</label>
                    <select id="barangay" name="barangay">
                        <option value="">All Barangays</option>
                        <?php foreach ($available_barangays as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= $filter_barangay === $b ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($available_statuses as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $filter_status === $s ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-search">Search</button>
            </form>

            <div class="card section-card">
                <div class="card-title">Aid History Records (<?= count($filtered_data) ?> Results)</div>
                <div class="card-body">
                    <?php if(empty($filtered_data)): ?>
                        <p>No aid records found matching your criteria.</p>
                    <?php else: ?>
                        <div class="data-table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Barangay</th>
                                        <th>Municipality</th>
                                        <th>Status</th>
                                        <th>Assistance</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($filtered_data as $aid): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($aid['No']) ?></td>
                                            <td><?= htmlspecialchars($aid['Last_Name']) ?></td>
                                            <td><?= htmlspecialchars($aid['First_Name']) ?></td>
                                            <td><?= htmlspecialchars($aid['Middle_Name']) ?></td>
                                            <td><?= htmlspecialchars($aid['Barangay']) ?></td>
                                            <td><?= htmlspecialchars($aid['Municipality']) ?></td>
                                            <td class="status-<?= strtolower($aid['Status']) ?>"><?= htmlspecialchars($aid['Status']) ?></td>
                                            <td><?= htmlspecialchars($aid['Assistance']) ?></td>
                                            <td><?= htmlspecialchars($aid['Date']) ?></td>
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