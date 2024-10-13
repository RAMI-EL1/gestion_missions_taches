<?php
session_start();
include 'datab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user exists in the users table
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User ID does not exist in the database.");
}

// Handle form submission for creating a new mission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Insert the new mission into the database
    $stmt = $conn->prepare("INSERT INTO missions (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $description);
    
    if ($stmt->execute()) {
        echo "Mission created successfully.";
    } else {
        echo "Error: " . $stmt->error; // This will provide information about the error
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Create Mission</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Create a New Mission</h2>
    <form action="missions.php" method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Mission Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" ></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Mission</button>
    </form>
</div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
