<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guspart Autoshop - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
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

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 40px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #f4a460;
        }

        main {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 50px 100px;
            min-height: 80vh;
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

        .signup-text a {
            color: #f4a460;
            text-decoration: none;
            font-weight: 500;
        }


        .signup-form {
            background: #b9b9b9;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .signup-form h2 {
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

        .signup-btn {
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

        .signup-btn:hover {
            background: #ff7f50;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #f4a460;
            outline: none;
            box-shadow: 0 0 5px rgba(244, 164, 96, 0.5);
        }

        .signup-btn {
            width: 100%;
            padding: 12px;
            background: #f4a460;
            border: none;
            border-radius: 20px;
            color: white;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .signup-btn:hover {
            background: #e58e3a;
            transform: translateY(-3px);
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
            gap: 10%;
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

    <main>
        <div class="welcome-text">
            <h1>Welcome to<br>Guspart Autoshop</h1>
            <p class="login-text">Already Registered? <a href="login.php">Login</a> here</p>
        </div>

        <div class="signup-form">
    <h2>Create an Account</h2>
    <!-- Tampilkan pesan error atau success -->
    <?php if (isset($_GET['error'])): ?>
        <div style="color: red; font-weight: bold; text-align: center; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div style="color: green; text-align: center; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="process_register.php">
        <div class="form-group">
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
                <option value="courier">Kurir</option>
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="signup-btn">Sign Up</button>
    </form>
</div>

    </main>

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