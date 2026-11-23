<?php
session_start();
require 'auth.php';

// Check if file is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("No file specified.");
}

$file = basename($_GET['file']); // sanitize
$path = __DIR__ . "/uploads/gallery/" . $file;

if (file_exists($path)) {
    if (unlink($path)) {
        header("Location: dashboard.php?msg=Upload deleted successfully");
        exit;
    } else {
        die("Error: Could not delete file.");
    }
} else {
    die("Error: File does not exist.");
}
