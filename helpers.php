<?php
// helpers.php
require_once __DIR__ . '/config.php';
session_start();

// Safe redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Require login
function require_login() {
    if (empty($_SESSION['user'])) redirect('../login.php');
}

// Require admin or super admin
function require_admin() {
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])) {
        redirect('../login.php');
    }
}

// Require client
function require_client() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
        redirect('../login.php');
    }
}

// Get user by phone
function get_user_by_phone($phone) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, phone, name, barangay, role FROM users WHERE phone=? LIMIT 1");
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    $stmt->close();
    return $u ?: null;
}

// **get_count helper**
function get_count($table, $where='1=1') {
    global $mysqli;
    $res = $mysqli->query("SELECT COUNT(*) AS c FROM `$table` WHERE $where");
    if(!$res) return 0;
    $r = $res->fetch_assoc();
    return (int)$r['c'];
}

// Flash messages
function flash_set($k,$v){ $_SESSION['flash'][$k]=$v; }
function flash_get($k){ $v=$_SESSION['flash'][$k]??null; unset($_SESSION['flash'][$k]); return $v; }
