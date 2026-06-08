<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require "db.php"; // PDO connection

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No event specified.");
}

$id = intval($_GET['id']); // sanitize

// Optional: get image path to delete image from server
$stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($event) {
    // Delete the image if exists
    if (!empty($event['image']) && file_exists(__DIR__ . "/" . $event['image'])) {
        unlink(__DIR__ . "/" . $event['image']);
    }

    // Delete the event record
    $del = $conn->prepare("DELETE FROM events WHERE id = ?");
    $del->execute([$id]);

    header("Location: dashboard.php?msg=Event deleted successfully");
    exit;
} else {
    die("Error: Event not found.");
}
