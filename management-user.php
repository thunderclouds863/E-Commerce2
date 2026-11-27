<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['owner']);

$conn = db();
$flashSuccess = '';
$flashError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf()) {
        $flashError = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'];

        if ($action === 'add_user') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role     = trim($_POST['role'] ?? '');
            $status   = trim($_POST['status'] ?? 'approved');

            $allowedRoles = ['customer', 'admin', 'courier'];
            $allowedStatuses = ['approved', 'pending', 'rejected'];

            if (strlen($username) < 3 || strlen($username) > 50) {
                $flashError = 'Username must be between 3 and 50 characters.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $flashError = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $flashError = 'Password must be at least 6 characters long.';
            } elseif (!in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatuses, true)) {
                $flashError = 'Invalid role or status selected.';
            } else {
                $chk = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                $chk->bind_param('ss', $username, $email);
                $chk->execute();
                if ($chk->get_result()->num_rows > 0) {
                    $flashError = 'Username or email is already registered.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $conn->prepare('INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
                    $ins->bind_param('sssss', $username, $email, $hash, $role, $status);
                    if ($ins->execute()) {
                        $flashSuccess = "User \"{$username}\" created successfully.";
                    } else {
                        $flashError = 'Failed to create user.';
                    }
                    $ins->close();
                }
                $chk->close();
            }
        } elseif ($action === 'edit_user') {
            $user_id     = (int)($_POST['user_id'] ?? 0);
            $username    = trim($_POST['username'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $role        = trim($_POST['role'] ?? '');
            $status      = trim($_POST['status'] ?? 'approved');
            $newPassword = $_POST['new_password'] ?? '';

            $allowedRoles = ['customer', 'admin', 'courier'];
            $allowedStatuses = ['approved', 'pending', 'rejected'];

            if ($user_id <= 0 || strlen($username) < 3 || strlen($username) > 50) {
                $flashError = 'Please provide a valid username (3–50 characters).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $flashError = 'Please enter a valid email address.';
            } elseif (!in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatuses, true)) {
                $flashError = 'Invalid role or status selected.';
            } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
                $flashError = 'New password must be at least 6 characters long.';
            } else {
                $chk = $conn->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1');
                $chk->bind_param('ssi', $username, $email, $user_id);
                $chk->execute();
                if ($chk->get_result()->num_rows > 0) {
                    $flashError = 'Another account already uses that username or email.';
                } else {
                    if ($newPassword !== '') {
                        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, password = ? WHERE id = ? AND role != 'owner'");
                        $upd->bind_param('sssssi', $username, $email, $role, $status, $hash, $user_id);
                    } else {
                        $upd = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ? AND role != 'owner'");
                        $upd->bind_param('ssssi', $username, $email, $role, $status, $user_id);
                    }
                    if ($upd->execute()) {
                        $flashSuccess = "User \"{$username}\" updated.";
                    } else {
                        $flashError = 'Failed to update user.';
                    }
                    $upd->close();
                }
                $chk->close();
            }
        } elseif (in_array($action, ['approve_user', 'reject_user', 'delete_user'], true)) {
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                $flashError = 'Invalid user ID.';
            } elseif ($user_id === (int)$_SESSION['user_id']) {
                $flashError = 'You cannot modify or delete your own owner account.';
            } elseif ($action === 'approve_user') {
                $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role != 'owner'");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->close();
                $flashSuccess = "User #{$user_id} approved.";
            } elseif ($action === 'reject_user') {
                $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role != 'owner'");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->close();
                $flashSuccess = "User #{$user_id} rejected.";
            } elseif ($action === 'delete_user') {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'owner'");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->close();
                $flashSuccess = "User #{$user_id} deleted.";
            }
        }
    }
}

$pending_users = $conn->query("SELECT id, username, email, role, status FROM users WHERE status = 'pending' AND role != 'owner' ORDER BY id DESC");
$all_managed_users = $conn->query("SELECT id, username, email, role, status FROM users WHERE role != 'owner' ORDER BY FIELD(role, 'admin', 'courier', 'customer'), id ASC");
$pendingCount = $pending_users ? (int)$pending_users->num_rows : 0;
$managedCount = $all_managed_users ? (int)$all_managed_users->num_rows : 0;

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
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
  <a href="#main-content" class="skip-link">Skip to main content</a>

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

  <main id="main-content" class="page-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h1>User Management</h1>
          <p>Approve pending registrations, or add, edit, and remove customer, admin, and courier accounts.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="toggleUserModal('add')">+ Add User</button>
      </div>

      <?php if ($flashSuccess !== ''): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= e($flashSuccess) ?></div>
      <?php endif; ?>
      <?php if ($flashError !== ''): ?>
        <div class="alert alert-error" role="alert" aria-live="polite"><?= e($flashError) ?></div>
      <?php endif; ?>

      <!-- Pending Approvals -->
      <div class="surface-card" style="padding: 0; overflow: hidden; margin-bottom: 28px;">
        <div class="section-card-header">
          <h2>Pending Approvals <span class="count-pill <?= $pendingCount > 0 ? 'warning' : '' ?>"><?= $pendingCount ?></span></h2>
        </div>
        <div class="table-responsive" style="border: none; border-radius: 0;">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($pending_users && $pending_users->num_rows > 0): ?>
                <?php while ($user = $pending_users->fetch_assoc()): ?>
                  <tr>
                    <td><strong>#<?= (int)$user['id'] ?></strong></td>
                    <td><strong><?= e($user['username']) ?></strong></td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="badge badge-warning"><?= e(ucfirst($user['role'])) ?></span></td>
                    <td>
                      <div class="table-actions">
                        <form method="POST" style="display:inline;">
                          <?= csrf_field() ?>
                          <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                          <button type="submit" name="action" value="approve_user" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" style="display:inline;">
                          <?= csrf_field() ?>
                          <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                          <button type="submit" name="action" value="reject_user" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 28px;">No pending user requests.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Managed Users (Admins, Couriers, Customers) -->
      <div class="surface-card" style="padding: 0; overflow: hidden;">
        <div class="section-card-header">
          <h2>All Users <span class="count-pill"><?= $managedCount ?></span></h2>
          <div class="table-toolbar">
            <select id="roleFilter" aria-label="Filter users by role">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="courier">Courier</option>
              <option value="customer">Customer</option>
            </select>
            <input type="text" id="userSearch" class="search-input" placeholder="Search username or email..." aria-label="Filter users">
          </div>
        </div>
        <div class="table-responsive" style="border: none; border-radius: 0;">
          <table class="data-table" id="usersTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($all_managed_users && $all_managed_users->num_rows > 0): ?>
                <?php while ($u = $all_managed_users->fetch_assoc()): ?>
                  <?php $userJson = json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
                  <tr data-search="<?= e(strtolower($u['username'] . ' ' . $u['email'] . ' ' . $u['role'])) ?>" data-role="<?= e(strtolower($u['role'])) ?>">
                    <td><strong>#<?= (int)$u['id'] ?></strong></td>
                    <td><strong><?= e($u['username']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge badge-neutral"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td><span class="badge <?= status_badge_class($u['status']) ?>"><?= e(ucfirst($u['status'])) ?></span></td>
                    <td>
                      <div class="table-actions">
                        <button type="button" class="btn btn-outline btn-sm js-edit-user" data-user="<?= e($userJson) ?>">Edit</button>
                        <form method="POST" style="display:inline;" class="js-delete-user-form" data-username="<?= e($u['username']) ?>">
                          <?= csrf_field() ?>
                          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                          <button type="submit" name="action" value="delete_user" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 28px;">No users found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add User Modal -->
  <div class="popup" id="popup-add-user" role="dialog" aria-modal="true" aria-labelledby="add-user-title">
    <div class="popup-content">
      <div class="popup-header">
        <h3 id="add-user-title">Add New User</h3>
        <button type="button" class="btn btn-outline btn-sm" onclick="toggleUserModal('add')" aria-label="Close">&times;</button>
      </div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_user">
        <div class="form-group">
          <label for="add-username">Username</label>
          <input id="add-username" type="text" name="username" minlength="3" maxlength="50" placeholder="Enter username" required>
        </div>
        <div class="form-group">
          <label for="add-email">Email</label>
          <input id="add-email" type="email" name="email" placeholder="user@example.com" required>
        </div>
        <div class="form-group">
          <label for="add-password">Password</label>
          <div class="password-wrap">
            <input id="add-password" type="password" name="password" minlength="6" placeholder="Minimum 6 characters" required>
            <button type="button" class="password-toggle" data-target="add-password" aria-label="Show password" aria-pressed="false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="add-role">Role</label>
          <select id="add-role" name="role" required>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
            <option value="courier">Courier</option>
          </select>
        </div>
        <div class="form-group">
          <label for="add-status">Status</label>
          <select id="add-status" name="status" required>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        <div class="popup-actions">
          <button type="button" class="btn btn-outline" onclick="toggleUserModal('add')">Cancel</button>
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="popup" id="popup-edit-user" role="dialog" aria-modal="true" aria-labelledby="edit-user-title">
    <div class="popup-content">
      <div class="popup-header">
        <h3 id="edit-user-title">Edit User</h3>
        <button type="button" class="btn btn-outline btn-sm" onclick="toggleUserModal('edit')" aria-label="Close">&times;</button>
      </div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="edit_user">
        <input type="hidden" name="user_id" id="edit-user-id">
        <div class="form-group">
          <label for="edit-username">Username</label>
          <input id="edit-username" type="text" name="username" minlength="3" maxlength="50" required>
        </div>
        <div class="form-group">
          <label for="edit-email">Email</label>
          <input id="edit-email" type="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="edit-role">Role</label>
          <select id="edit-role" name="role" required>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
            <option value="courier">Courier</option>
          </select>
        </div>
        <div class="form-group">
          <label for="edit-status">Status</label>
          <select id="edit-status" name="status" required>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="form-group">
          <label for="edit-new-password">New Password (optional)</label>
          <div class="password-wrap">
            <input id="edit-new-password" type="password" name="new_password" minlength="6" placeholder="Leave blank to keep current password">
            <button type="button" class="password-toggle" data-target="edit-new-password" aria-label="Show password" aria-pressed="false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>
        <div class="popup-actions">
          <button type="button" class="btn btn-outline" onclick="toggleUserModal('edit')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <footer class="portal-footer">
    &copy; 2024 Guspart Autoshop. All Rights Reserved.
  </footer>

  <script src="assets/js/main.js"></script>
  <script>
    function toggleUserModal(type) {
      const addModal = document.getElementById('popup-add-user');
      const editModal = document.getElementById('popup-edit-user');
      if (type === 'add') addModal.classList.toggle('active');
      if (type === 'edit') editModal.classList.toggle('active');
    }

    function openEditUser(user) {
      document.getElementById('edit-user-id').value = user.id;
      document.getElementById('edit-username').value = user.username;
      document.getElementById('edit-email').value = user.email || '';
      document.getElementById('edit-role').value = user.role;
      document.getElementById('edit-status').value = user.status;
      document.getElementById('edit-new-password').value = '';
      toggleUserModal('edit');
    }

    document.querySelectorAll('.js-edit-user').forEach(function (btn) {
      btn.addEventListener('click', function () {
        try {
          const user = JSON.parse(btn.getAttribute('data-user') || '{}');
          openEditUser(user);
        } catch (err) {}
      });
    });

    document.querySelectorAll('.js-delete-user-form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        const uname = form.getAttribute('data-username') || 'this user';
        if (!window.confirm('Delete user "' + uname + '"? This action cannot be undone.')) {
          e.preventDefault();
        }
      });
    });

    const userSearch = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    function applyUserFilters() {
      const q = (userSearch ? userSearch.value : '').toLowerCase().trim();
      const role = roleFilter ? roleFilter.value.toLowerCase() : '';
      document.querySelectorAll('#usersTable tbody tr[data-search]').forEach(function (row) {
        const matchesText = row.getAttribute('data-search').includes(q);
        const matchesRole = !role || row.getAttribute('data-role') === role;
        row.style.display = (matchesText && matchesRole) ? '' : 'none';
      });
    }
    if (userSearch) userSearch.addEventListener('input', applyUserFilters);
    if (roleFilter) roleFilter.addEventListener('change', applyUserFilters);
  </script>
</body>

</html>
