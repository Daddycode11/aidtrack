<?php
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS',''); // set your MySQL password
define('DB_NAME','aidtrack');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// First connect WITHOUT DB_NAME
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error);
}

// Create database if it doesn't exist
$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

// Select the database
$mysqli->select_db(DB_NAME);
