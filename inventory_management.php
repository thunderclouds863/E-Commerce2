<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
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

function uploadImage($image)
{
    $targetDir = "uploads/";
    $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png"];

    if (!in_array($imageFileType, $allowedTypes)) {
        return "error_type";
    }

    if ($image["size"] > 2 * 1024 * 1024) {
        return "error_size";
    }

    $i = 1;
    $targetFile = $targetDir . "produk" . $i . "." . $imageFileType;

    while (file_exists($targetFile)) {
        $i++;
        $targetFile = $targetDir . "produk" . $i . "." . $imageFileType;
    }

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        return $targetFile;
    }

    return "error_upload";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_product') {
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];

      $image = null;
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = uploadImage($_FILES['image']);
      }

      $sql = "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssdss", $name, $description, $price, $stock, $image);
      $stmt->execute();
    }

    if ($action === 'edit_product') {
      $id = $_POST['id'];
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];
      $existingImage = $_POST['existing_image'];

      $image = $existingImage;
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = uploadImage($_FILES['image']);
      }

      $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssdssi", $name, $description, $price, $stock, $image, $id);
      $stmt->execute();
    }
    if ($action === 'delete_product') {
      $id = $_POST['id'];
      $sql = "DELETE FROM products WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();
    }
  }
}

$products = $conn->query("SELECT * FROM products");
$usernameDisplay = htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management - Guspart Autoshop</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <header class="site-header">
    <div class="header-inner">
      <a href="admin_dashboard.php" class="brand-logo">
        <img src="gambar1.png" alt="Guspart Autoshop Logo">
        <span class="brand-name">Guspart Autoshop</span>
        <span class="brand-badge">Admin</span>
      </a>
      <nav class="site-nav" aria-label="Admin navigation">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="order-management.php">Order Management</a>
        <a href="inventory_management.php" class="active">Inventory Management</a>
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
          <h1>Inventory Management</h1>
          <p>Add, edit, and maintain spare part stock and pricing.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="togglePopup('add')">+ Add Product</button>
      </div>

      <div class="surface-card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive" style="border: none; border-radius: 0;">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($product = $products->fetch_assoc()): ?>
                <?php
                $imgPath = $product['image'];
                if ($imgPath && !file_exists($imgPath) && file_exists('uploads/' . $imgPath)) {
                  $imgPath = 'uploads/' . $imgPath;
                }
                ?>
                <tr>
                  <td><strong>#<?= (int)$product['id'] ?></strong></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <?php if ($imgPath && file_exists($imgPath)): ?>
                        <img class="table-thumb" src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                      <?php endif; ?>
                      <strong><?= htmlspecialchars($product['name']) ?></strong>
                    </div>
                  </td>
                  <td style="color: var(--text-secondary); max-width: 280px;"><?= htmlspecialchars($product['description']) ?></td>
                  <td style="white-space: nowrap;"><strong>Rp <?= number_format($product['price'], 0, ',', '.') ?></strong></td>
                  <td>
                    <span class="badge <?= ((int)$product['stock'] <= 10) ? 'badge-warning' : 'badge-success' ?>">
                      <?= (int)$product['stock'] ?> pcs
                    </span>
                  </td>
                  <td>
                    <div class="table-actions">
                      <button type="button" class="btn btn-outline btn-sm" onclick='editProduct(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, "UTF-8") ?>)'>Edit</button>
                      <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Product Modal -->
  <div class="popup" id="popup-add" role="dialog" aria-modal="true" aria-labelledby="add-modal-title">
    <div class="popup-content">
      <div class="popup-header">
        <h3 id="add-modal-title">Add New Product</h3>
        <button type="button" class="btn btn-outline btn-sm" onclick="togglePopup('add')" aria-label="Close">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <div class="form-group">
          <label for="add-name">Product Name</label>
          <input id="add-name" type="text" name="name" required>
        </div>
        <div class="form-group">
          <label for="add-description">Description</label>
          <textarea id="add-description" name="description" required></textarea>
        </div>
        <div class="form-group">
          <label for="add-price">Price (Rp)</label>
          <input id="add-price" type="number" name="price" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="add-stock">Stock</label>
          <input id="add-stock" type="number" name="stock" required>
        </div>
        <div class="form-group">
          <label for="add-image">Product Image</label>
          <input id="add-image" type="file" name="image" accept="image/*">
        </div>
        <div class="popup-actions">
          <button type="button" class="btn btn-outline" onclick="togglePopup('add')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Product Modal -->
  <div class="popup" id="popup-edit" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="popup-content">
      <div class="popup-header">
        <h3 id="edit-modal-title">Edit Product</h3>
        <button type="button" class="btn btn-outline btn-sm" onclick="togglePopup('edit')" aria-label="Close">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_product">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="existing_image" id="edit-existing-image">
        <div class="form-group">
          <label for="edit-name">Product Name</label>
          <input type="text" name="name" id="edit-name" required>
        </div>
        <div class="form-group">
          <label for="edit-description">Description</label>
          <textarea name="description" id="edit-description" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-price">Price (Rp)</label>
          <input type="number" name="price" id="edit-price" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="edit-stock">Stock</label>
          <input type="number" name="stock" id="edit-stock" required>
        </div>
        <div class="form-group">
          <label for="edit-image">Replace Image (optional)</label>
          <input id="edit-image" type="file" name="image" accept="image/*">
        </div>
        <div class="popup-actions">
          <button type="button" class="btn btn-outline" onclick="togglePopup('edit')">Cancel</button>
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
    function togglePopup(type) {
      const popupAdd = document.getElementById('popup-add');
      const popupEdit = document.getElementById('popup-edit');
      if (type === 'add') popupAdd.classList.toggle('active');
      if (type === 'edit') popupEdit.classList.toggle('active');
    }

    function editProduct(product) {
      document.getElementById('edit-id').value = product.id;
      document.getElementById('edit-name').value = product.name;
      document.getElementById('edit-description').value = product.description;
      document.getElementById('edit-price').value = product.price;
      document.getElementById('edit-stock').value = product.stock;
      document.getElementById('edit-existing-image').value = product.image || '';
      togglePopup('edit');
    }
  </script>
</body>

</html>
