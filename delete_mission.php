<?php
session_start();
include 'datab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

// Check if the mission ID is set in the query string
if (isset($_GET['id'])) {
    $mission_id = $_GET['id'];

    // Prepare the SQL statement to delete the mission
    $stmt = $conn->prepare("DELETE FROM missions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $mission_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        // Redirect back to the dashboard with a success message
        header('Location: dashboard.php?msg=' . urlencode('Mission deleted successfully.'));
    } else {
        // Redirect back with an error message
        header('Location: dashboard.php?msg=' . urlencode('Error deleting mission: ' . $stmt->error));
    }
} else {
    // Redirect if no mission ID is provided
    header('Location: dashboard.php?msg=' . urlencode('Invalid request.'));
}

$stmt->close();
$conn->close(); // Close the database connection
?>
