<?php
session_start();
include 'datab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information (including name) from the database
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
} else {
    $name = "User"; // Default name if not found
}

// Handle form submission for creating a new mission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Insert the new mission into the database
    $stmt = $conn->prepare("INSERT INTO missions (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $description);
    
    if ($stmt->execute()) {
        $msg = "Mission created successfully.";
    } else {
        $msg = "Error: " . $stmt->error; // Show error if mission creation fails
    }
}

// Fetch user-specific missions from the database
$missionResult = $conn->query("SELECT * FROM missions WHERE user_id = $user_id");

// Fetch all users for sharing
$usersResult = $conn->query("SELECT id, name FROM users WHERE id != $user_id"); // Exclude the current user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Welcome, <?php echo htmlspecialchars($name); ?></h2>
    <p>This is your dashboard. You can view and manage your missions and tasks here.</p>

    <!-- Mission Creation Form -->
    <h3 class="mt-4">Create a New Mission</h3>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <form action="dashboard.php" method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Mission Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Mission</button>
    </form>

    <!-- Display Missions -->
<h3 class="mt-4">Your Missions</h3>
<ul class="list-group">
    <?php if ($missionResult->num_rows > 0): ?>
        <?php while ($mission = $missionResult->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="me-auto">
                    <strong><?php echo htmlspecialchars($mission['title']); ?></strong><br>
                    <?php echo htmlspecialchars($mission['description']); ?> - <?php echo htmlspecialchars($mission['status']); ?>
                </div>
                <div>
                    <a href="tasks.php?mission_id=<?php echo $mission['id']; ?>" class="btn btn-info btn-sm me-2">Inspect Tasks</a>
                    <button type="button" class="btn btn-warning btn-sm" onclick="setMissionId(<?php echo $mission['id']; ?>);">
                        Share
                    </button>
                    <a href="delete_mission.php?id=<?php echo $mission['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this mission?');">Delete</a>
                </div>
            </li>
        <?php endwhile; ?>
    <?php else: ?>
        <li class="list-group-item">No missions created yet.</li>
    <?php endif; ?>
</ul>


    <!-- Share Mission Modal -->
    <div class="modal fade" id="shareMissionModal" tabindex="-1" aria-labelledby="shareMissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareMissionModalLabel">Select Users to Share Mission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="shareMissionForm" action="share_mission.php" method="POST">
                        <input type="hidden" name="mission_id" id="mission_id" value="">
                        <ul class="list-group">
                            <?php if ($usersResult->num_rows > 0): ?>
                                <?php while ($user = $usersResult->fetch_assoc()): ?>
                                    <li class="list-group-item">
                                        <input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="list-group-item">No other users available.</li>
                            <?php endif; ?>
                        </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
                    </form>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="mt-5 mb-3"> <!-- Add margin to drop it down further -->
        <a href="logout.php" class="btn btn-danger">Log Out</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setMissionId(missionId) {
    document.getElementById('mission_id').value = missionId;
    var myModal = new bootstrap.Modal(document.getElementById('shareMissionModal'));
    myModal.show(); // Show the modal when the button is clicked
}
</script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
