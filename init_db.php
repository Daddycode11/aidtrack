<?php
// init_db.php
require_once 'config.php'; // should create $mysqli

// Create DB if not exists
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    die("Error creating DB: " . $mysqli->error);
}
$mysqli->select_db(DB_NAME);

// Tables
$queries = [];

// users table with super_admin role
$queries[] = "
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  barangay VARCHAR(100),
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('client','admin','super_admin') DEFAULT 'client',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// applications table
$queries[] = "
CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('burial','medical') NOT NULL,
  date_of_request DATE NOT NULL,
  status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  amount_requested DECIMAL(10,2) DEFAULT 0.00,
  amount_granted DECIMAL(10,2) DEFAULT 0.00,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// documents table
$queries[] = "
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// messages table
$queries[] = "
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  sender ENUM('admin','system') DEFAULT 'system',
  message TEXT,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// Run table creation
foreach ($queries as $q) {
    if (!$mysqli->query($q)) {
        die('Error creating table: ' . $mysqli->error);
    }
}

// Create uploads directory
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// --- Create admin account ---
$accounts = [
    [
        'phone' => '09171234567',
        'name' => 'Admin User',
        'password' => 'admin123!',
        'role' => 'admin'
    ],
    [
        'phone' => '09170000001',
        'name' => 'Super Admin',
        'password' => 'superadmin123',
        'role' => 'super_admin'
    ]
];

foreach ($accounts as $acc) {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->bind_param('s', $acc['phone']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $hash = password_hash($acc['password'], PASSWORD_DEFAULT);
        $stmt2 = $mysqli->prepare("INSERT INTO users (phone, name, barangay, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $barangay = 'Office';
        $stmt2->bind_param('sssss', $acc['phone'], $acc['name'], $barangay, $hash, $acc['role']);
        $stmt2->execute();
        echo ucfirst($acc['role']) . " created. Phone: {$acc['phone']} Password: {$acc['password']}\n";
        $stmt2->close();
    } else {
        echo ucfirst($acc['role']) . " already exists (phone {$acc['phone']}).\n";
    }
    $stmt->close();
}

echo "DB initialized successfully.\n";
