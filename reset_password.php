<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'part';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];

    // Verify reset code
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_code = ?");
    $stmt->bind_param('ss', $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
        $stmt->bind_param('ss', $new_password, $email);
        $stmt->execute();

        header("location:login.php");
    } else {
        $message = 'Invalid reset code!';
    }
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
        <h2>Reset Password</h2>
        <?php if (!empty($message)): ?>
            <div style="color: red; font-weight: bold; margin-bottom: 20px; text-align: center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email" required>
            </div>
            <div class="form-group">
                <label>Reset Code</label>
                <input type="text" name="reset_code" placeholder="Kode OTP" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Masukkan Password Baru" required>
            </div>
            <button type="submit" class="login-btn">Reset Password</button>
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
