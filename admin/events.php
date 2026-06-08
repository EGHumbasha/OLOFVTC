<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_id'])) { 
    header("Location: login.php"); 
    exit; 
}

// Handle Add Event
if(isset($_POST['add_event'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];

    // Handle image upload
    $image_path = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $uploadDir = "../admin/uploads/events/";
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_path = $uploadDir . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    $stmt = $conn->prepare("INSERT INTO events (title, description, image, event_date, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $description, $image_path, $event_date]);

    header("Location: events.php");
    exit;
}

// Handle Delete Event
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image FROM events WHERE id=?");
    $stmt->execute([$id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if($event && $event['image'] && file_exists($event['image'])){
        unlink($event['image']);
    }
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->execute([$id]);
    header("Location: events.php");
    exit;
}

// Fetch all events
$events = $conn->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">

    <h3>Manage News & Events</h3>
    <hr>

    <!-- Add Event Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Event</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Event Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="add_event" class="btn btn-success">Add Event</button>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card">
        <div class="card-header">All Events</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($events)): foreach($events as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= htmlspecialchars($e['event_date']) ?></td>
                        <td>
                            <?php if($e['image'] && file_exists($e['image'])): ?>
                                <img src="<?= htmlspecialchars($e['image']) ?>" style="width:80px;height:50px;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_event.php?id=<?= $e['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="events.php?delete=<?= $e['id'] ?>" onclick="return confirm('Delete this event?');" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center">No events found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

</div>
</body>
</html>
