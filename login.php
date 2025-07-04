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

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Plain text password

    // Modified query to check username and password directly
    $stmt = $conn->prepare("SELECT id, username, role, status FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // User found and password matches
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username; // Store username in session

        // Check user role and approval status
        if ($user['role'] == 'customer') {
            // Redirect to the customer dashboard (index.php)
            header("Location: index.php");
            exit;  // Make sure to exit to prevent further code execution
        } elseif (($user['role'] == 'owner' || $user['role'] == 'admin' || $user['role'] == 'courier') && $user['status'] == 'approved') {
            // Redirect to the respective dashboard if approved
            $role_dashboard = $user['role'] . '_dashboard.php'; // Generate the correct dashboard
            header("Location: $role_dashboard");
            exit;  // Ensure to exit after the redirect
        } elseif (($user['role'] == 'admin' || $user['role'] == 'courier') && $user['status'] != 'approved') {
            // User is not approved yet
            $error_message = "Your account is pending approval by the owner.";
            header("Location: login.php?error=" . urlencode($error_message));
            exit;  // Make sure to exit here
        }
    } else {
        // Invalid username or password
        $error_message = "Invalid username or password!";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    }

    $stmt->close();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guspart Autoshop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(120deg, #e7e6e1, #d9d9d9);
            color: #333;
            overflow-x: hidden;
        }

        header {
            padding: 20px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 40px;
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #f4a460;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
        }

        .main-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 50px 10%;
            min-height: calc(100vh - 120px);
        }

        .welcome-text {
            flex: 1;
            margin-bottom: 30px;
        }

        .welcome-text h1 {
            color: 36454F;
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        .welcome-text p {
            color: #314e52;
            font-size: 18px;
        }

        .welcome-text a {
            color: whitesmoke;
            font-weight: bold;
            text-decoration: none;
            padding: 8px 16px;
            background: #28545a;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .welcome-text a:hover {
            background: white;
            color: #333;
        }

        .login-form {
            background: #b9b9b9;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .login-form h2 {
            color: #2F4F4F;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2F4F4F;
            text-transform: uppercase;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #f4a460;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #ff7f50;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        footer {
            background-color: #2F4F4F;
            color: #E0E0E0;
            padding: 20px;
            text-align: center;
        }

        footer .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: left;
            gap: 5%;
        }

        .feature {
            display: flex;
            align-items: left;
            gap: 10px;
            text-align: left;
        }

        .feature h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .feature p {
            font-size: 0.9em;
            color: #A7D5D5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav {
                display: none;
                flex-direction: column;
                background: white;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                padding: 10px 0;
            }

            nav.show {
                display: flex;
            }

            .hamburger {
                display: flex;
            }

            footer .features {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <img src=gambar1.png alt="Logo">
            <h2>Guspart Autoshop</h2>
        </div>
        <nav>
            <a href="#hero">Home</a>
            <a href="#products">Product</a>
            <a href="Order.php">Order</a>
            <a href="#footer">Contact Us</a>
            <a href="login.php">Login</a>
        </nav>
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>

    <div class="main-content">
        <div class="welcome-text">
            <h1>Welcome to<br>Guspart Autoshop</h1>
            <p>Don't Have an Account? <a href="register.php">Register here</a></p>
        </div>

        <div class="login-form">
            <h2>Login</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; font-weight: bold; margin-bottom: 20px; text-align: center">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message" style="color: red; font-weight: bold; margin-bottom: 20px; text-align: center"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="" disabled selected>-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                        <option value="courier">Kurir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn" name="submit">Login</button>
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password</a>
                </div>
            </form>
        </div>

    </div>

    <footer>
        <div class="features">
            <div class="feature">
                <span><img src=gambar3.png></span>
                <div>
                    <h3>Free Shipping</h3>
                    <p>Order now and you'll know</p>
                </div>
            </div>
            <div class="feature">
                <span><img src=gambar4.png></span>
                <div>
                    <h3>24/7 Support</h3>
                    <p>Order now and you'll know</p>
                </div>
            </div>
            <div class="feature">
                <span><img src=gambar5.png></span>
                <div>
                    <h3>Free return</h3>
                    <p>Order now and you'll know</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('show');
        }
    </script>
</body>

</html>