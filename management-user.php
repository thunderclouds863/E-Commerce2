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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

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
      echo "Invalid action.";
    }
  }
}

$pending_users = $conn->query("SELECT * FROM users WHERE status = 'pending'");
$customers = $conn->query("SELECT * FROM users WHERE role = 'customer' AND status = 'approved'");
$employees = $conn->query("SELECT * FROM users WHERE role = 'admin' AND status = 'approved'");
$couriers = $conn->query("SELECT * FROM users WHERE role = 'courier' AND status = 'approved'");

$usernameDisplay = htmlspecialchars($_SESSION['username'] ?? 'Owner', ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'] ?? 'O', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - Guspart Autoshop</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <header class="site-header">
    <div class="header-inner">
      <a href="owner_dashboard.php" class="brand-logo">
        <img src="gambar1.png" alt="Guspart Autoshop Logo">
        <span class="brand-name">Guspart Autoshop</span>
        <span class="brand-badge">Owner</span>
      </a>
      <nav class="site-nav" aria-label="Owner navigation">
        <a href="owner_dashboard.php">Dashboard</a>
        <a href="management-user.php" class="active">User Management</a>
        <a href="report.php">Sales Report</a>
        <div class="user-menu">
          <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
            <span class="user-avatar"><?= $avatarInitial ?></span>
            <span><?= $usernameDisplay ?></span>
          </button>
          <div class="dropdown-menu">
            <a href="logout.php">Logout</a>
          </div>
        </div>
      </nav>
      <button type="button" class="hamburger" onclick="toggleMenu()" aria-label="Toggle navigation" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>

  <main class="page-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h1>User Management</h1>
          <p>Approve pending registrations and review active accounts by role.</p>
        </div>
      </div>

      <div class="portal-grid-2">
        <div class="surface-card">
          <h2 style="font-size: 1.25rem; color: var(--primary); margin-bottom: 16px;">Pending Approvals</h2>
          <div class="table-responsive">
            <table class="data-table" style="min-width: 360px;">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($pending_users->num_rows > 0): ?>
                  <?php while ($user = $pending_users->fetch_assoc()): ?>
                    <tr>
                      <td><strong>#<?= (int)$user['id'] ?></strong></td>
                      <td><?= htmlspecialchars($user['username']) ?></td>
                      <td><span class="badge badge-warning"><?= htmlspecialchars(ucfirst($user['role'])) ?></span></td>
                      <td>
                        <div class="table-actions">
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                            <button type="submit" name="action" value="approve_user" class="btn btn-success btn-sm">Approve</button>
                          </form>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                            <button type="submit" name="action" value="reject_user" class="btn btn-danger btn-sm">Reject</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 28px;">No pending user requests.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="surface-card" style="display: flex; flex-direction: column; gap: 22px;">
          <h2 style="font-size: 1.25rem; color: var(--primary);">Active Users</h2>

          <div>
            <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 10px;">Customers</h3>
            <div class="table-responsive">
              <table class="data-table" style="min-width: 260px;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                      <td>#<?= (int)$customer['id'] ?></td>
                      <td><strong><?= htmlspecialchars($customer['username']) ?></strong></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div>
            <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 10px;">Admins</h3>
            <div class="table-responsive">
              <table class="data-table" style="min-width: 260px;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($employee = $employees->fetch_assoc()): ?>
                    <tr>
                      <td>#<?= (int)$employee['id'] ?></td>
                      <td><strong><?= htmlspecialchars($employee['username']) ?></strong></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div>
            <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 10px;">Couriers</h3>
            <div class="table-responsive">
              <table class="data-table" style="min-width: 260px;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($courier = $couriers->fetch_assoc()): ?>
                    <tr>
                      <td>#<?= (int)$courier['id'] ?></td>
                      <td><strong><?= htmlspecialchars($courier['username']) ?></strong></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="portal-footer">
    &copy; 2024 Guspart Autoshop. All Rights Reserved.
  </footer>

  <script src="assets/js/main.js"></script>
</body>

</html>
