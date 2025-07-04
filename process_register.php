<?php
// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'part';

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Plain text password
    $confirm_password = $_POST['confirm_password'];

    // Password match validation
    if ($password !== $confirm_password) {
        header("Location: register.php?error=Passwords do not match!");
        exit;
    }

    // Role validation
    $allowed_roles = ['customer', 'admin', 'courier'];
    if (!in_array($role, $allowed_roles)) {
        header("Location: register.php?error=Invalid role selected!");
        exit;
    }

    // Status for Admin and Courier
    $status = ($role === 'customer') ? 'approved' : 'pending';

    // Check if username or email already exists
    $stmt_check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt_check->bind_param('ss', $username, $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        header("Location: register.php?error=Username or email already exists!");
    } else {
        // Insert Data into Database with plain text password
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $username, $email, $password, $role, $status);

        if ($stmt->execute()) {
            header('Location: login.php?success=Account created successfully!');
        } else {
            header("Location: register.php?error=Error: " . $stmt->error);
        }

        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>