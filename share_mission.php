<?php
session_start();
include 'datab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mission_id = $_POST['mission_id'];
    
    // Check if any user IDs were selected
    if (isset($_POST['user_ids'])) {
        $user_ids = $_POST['user_ids'];

        // Prepare the SQL statement to insert shared mission
        $stmt = $conn->prepare("INSERT INTO shared_missions (mission_id, user_id) VALUES (?, ?)");
        
        // Prepare the statement for copying mission details
        $missionStmt = $conn->prepare("SELECT title, description FROM missions WHERE id = ?");

        // Prepare the statement for inserting tasks
        $insertTaskStmt = $conn->prepare("INSERT INTO tasks (mission_id, name, description, priority, status, user_id) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($user_ids as $user_id_to_share) {
            // First, insert into the shared_missions table
            $stmt->bind_param("ii", $mission_id, $user_id_to_share);
            if (!$stmt->execute()) {
                echo "Error sharing mission with user ID $user_id_to_share: " . $stmt->error;
                continue; // Skip to next user on error
            }

            // Now, fetch the mission details to insert into the user's missions
            $missionStmt->bind_param("i", $mission_id);
            $missionStmt->execute();
            $missionResult = $missionStmt->get_result();

            if ($missionResult->num_rows > 0) {
                $mission = $missionResult->fetch_assoc();

                // Insert the mission for the user
                $insertStmt = $conn->prepare("INSERT INTO missions (user_id, title, description) VALUES (?, ?, ?)");
                $insertStmt->bind_param("iss", $user_id_to_share, $mission['title'], $mission['description']);

                if (!$insertStmt->execute()) {
                    echo "Error adding mission for user ID $user_id_to_share: " . $insertStmt->error;
                } else {
                    $new_mission_id = $conn->insert_id; // Get the last inserted ID from the connection

                    // Now, share tasks associated with this mission
                    $tasksStmt = $conn->prepare("SELECT name, description FROM tasks WHERE mission_id = ?");
                    $tasksStmt->bind_param("i", $mission_id);
                    $tasksStmt->execute();
                    $tasksResult = $tasksStmt->get_result();

                    while ($task = $tasksResult->fetch_assoc()) {
                        // Assuming you want to assign default values for priority and status for shared tasks
                        $priority = 1; // Default priority
                        $status = 'Pending'; // Default status
                        $insertTaskStmt->bind_param("issssi", $new_mission_id, $task['name'], $task['description'], $priority, $status, $user_id_to_share);
                        
                        if (!$insertTaskStmt->execute()) {
                            echo "Error adding task for user ID $user_id_to_share: " . $insertTaskStmt->error;
                        }
                    }
                }
            }
        }

        // Close all statements
        $stmt->close();
        $missionStmt->close();
        $tasksStmt->close();
        $insertTaskStmt->close(); // Close the insertTaskStmt here
        
        // Redirect back to the dashboard with a success message
        header('Location: dashboard.php?msg=' . urlencode('Mission shared successfully!'));
        exit();
    } else {
        // No users selected
        header('Location: dashboard.php?msg=' . urlencode('No users selected to share the mission.'));
        exit();
    }
} else {
    // Invalid request method
    header('Location: dashboard.php?msg=' . urlencode('Invalid request.'));
    exit();
}

$conn->close(); // Close the database connection
?>
