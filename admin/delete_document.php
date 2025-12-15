<?php
// admin/delete_document.php
require_once __DIR__ . '/../helpers.php';
require_admin(); // ensure admin or super_admin

// Get document ID from query string
$doc_id = intval($_GET['id'] ?? 0);
$sort = $_GET['sort'] ?? 'uploaded_at';  // optional sorting
$order = $_GET['order'] ?? 'DESC';       // optional order

if($doc_id <= 0){
    header("Location: applications.php");
    exit;
}

// Fetch document info
$stmt = $mysqli->prepare("SELECT filename FROM application_documents WHERE id=?");
$stmt->bind_param('i', $doc_id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$doc){
    // Document not found
    header("Location: applications.php");
    exit;
}

// Remove file from disk safely
$filePath = __DIR__ . '/../uploads/' . $doc['filename'];
if(file_exists($filePath) && is_file($filePath)){
    @unlink($filePath);
}

// Delete record from database
$stmt = $mysqli->prepare("DELETE FROM application_documents WHERE id=?");
$stmt->bind_param('i', $doc_id);
$stmt->execute();
$stmt->close();

// Redirect back to applications page with optional sorting
header("Location: applications.php?sort={$sort}&order={$order}");
exit;
