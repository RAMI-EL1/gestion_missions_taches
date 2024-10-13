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

// Check if the user ID is provided
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Get user ID from query string

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Deletion successful, store message in session
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        // Handle error
        $_SESSION['message'] = "Error deleting user.";
    }

    $stmt->close();
}

// Redirect back to admin dashboard
header('Location: admin.php');
exit;
?>
