<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'owner') {
  header("Location: index.php");
  exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle POST Requests for approving, rejecting, and deleting users
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    // Ensure that the action is valid to prevent potential issues
    $valid_actions = ['approve_user', 'reject_user', 'delete_user'];
    if (in_array($action, $valid_actions)) {
      switch ($action) {
        case 'approve_user':
          $sql = "UPDATE users SET status = ? WHERE id = ?";
          $stmt = $conn->prepare($sql);
          $status = 'approved';
          $stmt->bind_param("si", $status, $user_id);
          $stmt->execute();
          break;

        case 'reject_user':
          $sql = "UPDATE users SET status = ? WHERE id = ?";
          $stmt = $conn->prepare($sql);
          $status = 'rejected';
          $stmt->bind_param("si", $status, $user_id);
          $stmt->execute();
          break;

        case 'delete_user':
          $sql = "DELETE FROM users WHERE id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          break;
      }
    } else {
      // Invalid action
      echo "Invalid action.";
    }
  }
}

// Fetch users with different statuses and roles
$pending_users = $conn->query("SELECT * FROM users WHERE status = 'pending'");
$customers = $conn->query("SELECT * FROM users WHERE role = 'customer' AND status = 'approved'");
$employees = $conn->query("SELECT * FROM users WHERE role = 'admin' AND status = 'approved'");
$couriers = $conn->query("SELECT * FROM users WHERE role = 'courier' AND status = 'approved'");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f7f7f7;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      background-color: #3a4e7f;
      color: #fff;
      padding: 20px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
    }

    nav {
      display: flex;
      justify-content: center;
      background-color: #5c6bc0;
      padding: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    nav a {
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      margin: 0 15px;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    nav a:hover {
      background-color: #3a4e7f;
      border-radius: 5px;
    }

    .content {
      flex-grow: 1;
      padding: 30px;
      background-color: #fff;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .content h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #3a4e7f;
    }

    .content h3 {
      color: #3a4e7f;
      font-size: 20px;
      margin-bottom: 10px;
    }

    .table-container {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      background-color: #ffffff;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th,
    table td {
      padding: 12px;
      border: 1px solid #e0e0e0;
      text-align: left;
    }

    table th {
      background-color: #5c6bc0;
      color: white;
    }

    table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    table button {
      background-color: #4caf50;
      color: white;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    table button:hover {
      background-color: #388e3c;
    }

    table .reject {
      background-color: #f44336;
    }

    table .reject:hover {
      background-color: #d32f2f;
    }

    footer {
      background-color: #3a4e7f;
      color: white;
      text-align: center;
      padding: 15px;
      font-size: 14px;
      position: relative;
      bottom: 0;
      width: 100%;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
      .content {
        grid-template-columns: 1fr;
      }

      nav {
        flex-direction: column;
      }

      .table-container {
        padding: 15px;
      }
    }
  </style>
</head>

<body>
  <header>
    Owner Dashboard
  </header>

  <nav>
    <a href="owner_dashboard.php">Home</a>
    <a href="management-user.php">User Management</a>
    <a href="report.php">Report</a>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">
    <div class="table-container">
      <h3>Pending Users</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($user = $pending_users->fetch_assoc()): ?>
            <tr>
              <td><?= $user['id'] ?></td>
              <td><?= $user['username'] ?></td>
              <td><?= $user['role'] ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" name="action" value="approve_user">Approve</button>
                </form>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" name="action" value="reject_user" class="reject">Reject</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Active Users (Customers, Employees, Couriers) -->
    <div class="table-container">
      <h3>Active Users</h3>

      <h4>Customers</h4>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($customer = $customers->fetch_assoc()): ?>
            <tr>
              <td><?= $customer['id'] ?></td>
              <td><?= $customer['username'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <h4>Admins</h4>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($employee = $employees->fetch_assoc()): ?>
            <tr>
              <td><?= $employee['id'] ?></td>
              <td><?= $employee['username'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <h4>Couriers</h4>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($courier = $couriers->fetch_assoc()): ?>
            <tr>
              <td><?= $courier['id'] ?></td>
              <td><?= $courier['username'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer>
    <p>&copy; 2024 Burjo Restaurant. All Rights Reserved.</p>
  </footer>
</body>

</html>
