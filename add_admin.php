<?php
include 'datab.php';

// Admin credentials
$admin_name = 'Admin';
$admin_email = 'admin@example.com';
$admin_password = password_hash('@admin', PASSWORD_DEFAULT); // Hash the password
$admin_role = 'admin';
$admin_status = 'active';

// Check if admin already exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Insert new admin user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $admin_name, $admin_email, $admin_password, $admin_role, $admin_status);

    if ($stmt->execute()) {
        echo "Admin user created successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Admin user already exists.";
}

$stmt->close();
$conn->close();
?>
