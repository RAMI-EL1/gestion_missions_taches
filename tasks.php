<?php
session_start();
include 'datab.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the mission ID from the query string
if (!isset($_GET['mission_id'])) {
    die("No mission selected.");
}

$mission_id = $_GET['mission_id'];

// Fetch the mission title to display
$stmt = $conn->prepare("SELECT title FROM missions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $mission_id, $user_id);
$stmt->execute();
$missionResult = $stmt->get_result();

if ($missionResult->num_rows === 0) {
    die("Mission not found or you don't have permission to access it.");
}

$mission = $missionResult->fetch_assoc();
$mission_title = $mission['title'];

// Handle task deletion
if (isset($_GET['delete_task_id'])) {
    $task_id = $_GET['delete_task_id'];
    $deleteStmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $task_id, $user_id);
    $deleteStmt->execute();
    header("Location: tasks.php?mission_id=$mission_id"); // Reload page after deletion
    exit();
}

// Handle task status update
if (isset($_POST['update_status']) && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $updateStmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
    $updateStmt->bind_param("sii", $status, $task_id, $user_id);
    $updateStmt->execute();
    header("Location: tasks.php?mission_id=$mission_id"); // Reload page after status update
    exit();
}

// Handle form submission for creating a new task
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];

    // Insert the new task into the database
    $stmt = $conn->prepare("INSERT INTO tasks (name, description, priority, status, user_id, mission_id) VALUES (?, ?, ?, 'Pending', ?, ?)");
    $stmt->bind_param("ssiii", $name, $description, $priority, $user_id, $mission_id);
    
    if ($stmt->execute()) {
        $msg = "Task created successfully.";
    } else {
        $msg = "Error: " . $stmt->error;
    }
}

// Fetch tasks for the selected mission
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND mission_id = ?");
$stmt->bind_param("ii", $user_id, $mission_id);
$stmt->execute();
$tasksResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Tasks for Mission: <?php echo htmlspecialchars($mission_title); ?></title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Tasks for Mission: <?php echo htmlspecialchars($mission_title); ?></h2>

    <!-- Display success or error messages -->
    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <!-- Task Creation Form -->
    <h3 class="mt-4">Create a New Task</h3>
    <form action="tasks.php?mission_id=<?php echo htmlspecialchars($mission_id); ?>" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Task Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <input type="number" class="form-control" id="priority" name="priority" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Task</button>
    </form>

    <!-- Display Tasks -->
    <h3 class="mt-4">Your Tasks</h3>
    <ul class="list-group">
        <?php if ($tasksResult->num_rows > 0): ?>
            <?php while ($task = $tasksResult->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($task['name']); ?></strong><br>
                        <?php echo htmlspecialchars($task['description']); ?><br>
                        Status: <?php echo htmlspecialchars($task['status']); ?> <!-- Ensure this reflects current status -->
                    </div>
                    <div>
                        <!-- Delete Task Button -->
                        <a href="tasks.php?mission_id=<?php echo $mission_id; ?>&delete_task_id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm">Delete</a>

                        <!-- Status Dropdown -->
                        <form action="tasks.php?mission_id=<?php echo $mission_id; ?>" method="POST" class="d-inline">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <div class="dropdown d-inline">
                                <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="statusDropdown<?php echo $task['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    Update Status
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="statusDropdown<?php echo $task['id']; ?>">
                                    <li><button class="dropdown-item" type="submit" name="status" value="Pending">Pending</button></li>
                                    <li><button class="dropdown-item" type="submit" name="status" value="Done">Done</button></li>
                                </ul>
                            </div>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li class="list-group-item">No tasks available for this mission.</li>
        <?php endif; ?>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
