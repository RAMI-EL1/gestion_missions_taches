<?php
session_start();
include 'datab.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Redirect to login if not admin
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    // Validate action
    if ($action === 'activate') {
        $sql = "UPDATE users SET status = 'active' WHERE id = ?";
    } elseif ($action === 'deactivate') {
        $sql = "UPDATE users SET status = 'inactive' WHERE id = ?";
    } else {
        // Invalid action
        header('Location: admin.php'); // Redirect to admin page if action is invalid
        exit;
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        // Redirect back to admin page after update
        header('Location: admin.php');
        exit;
    } else {
        // Handle error
        echo "Error updating record: " . $conn->error;
    }
} else {
    // Invalid request
    header('Location: admin.php'); // Redirect to admin page if request is invalid
    exit;
}

$stmt->close();
$conn->close();
?>
