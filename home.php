<?php
session_start();
include 'datab.php';
$msg = '';
$emailExists = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $conn->real_escape_string($_POST['email']);
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $emailExists = true; // Email exists
            $user = $result->fetch_assoc();
        } else {
            $msg = "Email does not exist.";
        }
    }

    // If email exists and password is submitted
    if ($emailExists && isset($_POST['password'])) {
        $password = $_POST['password'];

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestion de Tâches et Missions</title>
</head>
<body>
<div class="container">
    <h1 class="mt-5 text-center">GESTION DE TACHES ET MISSIONS</h1>
    
    <p class="text-center text-danger"><?php echo htmlspecialchars($msg); ?></p>

    <form action="home.php" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" <?php echo $emailExists ? 'readonly' : ''; ?>>
        </div>

        <div class="mb-3" id="passwordField" style="display: <?php echo $emailExists ? 'block' : 'none'; ?>;">
            <label for="password" class="form-label">Enter Password</label>
            <input type="password" class="form-control" id="password" name="password" required 
                   placeholder="Enter your password">
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary" id="loginButton">Login</button>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
    </form>
</div>

<script>
    // Show password field if email exists
    const emailInput = document.getElementById('email');
    const passwordField = document.getElementById('passwordField');
    const passwordInput = document.getElementById('password');

    emailInput.addEventListener('focusout', function() {
        // Only submit if there is an email and it doesn't show the password field
        if (emailInput.value && passwordField.style.display === 'none') {
            document.forms[0].submit();
        }
    });

    // Reset the password field placeholder when focused
    passwordInput.addEventListener('focus', function() {
        if (passwordInput.placeholder === 'Invalid password') {
            passwordInput.placeholder = 'Enter your password';
        }
    });
</script>
</body>
</html>
