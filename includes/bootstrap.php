<?php
/**
 * Guspart Autoshop — Shared Bootstrap, Database, Auth & Security Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): mysqli {
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $name = getenv('DB_NAME') ?: 'part';

    $conn = new mysqli($host, $user, $pass, $name);
    if ($conn->connect_error) {
        http_response_code(500);
        die('Database connection failed. Please try again later.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function role_home(string $role): string {
    switch ($role) {
        case 'admin':   return 'admin_dashboard.php';
        case 'owner':   return 'owner_dashboard.php';
        case 'courier': return 'courier_dashboard.php';
        default:        return 'index.php';
    }
}

function require_login(): void {
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        header('Location: login.php');
        exit();
    }
}

function require_role(array $allowedRoles): void {
    require_login();
    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        header('Location: ' . role_home($_SESSION['role']));
        exit();
    }
}

function format_rp($amount): string {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

function status_badge_class(string $status): string {
    $s = strtolower(trim($status));
    if ($s === 'selesai' || $s === 'approved') return 'badge-success';
    if ($s === 'pending' || $s === 'siap dikirim') return 'badge-warning';
    if ($s === 'rejected') return 'badge-danger';
    if ($s === 'dikonfirmasi' || $s === 'dikemas' || $s === 'dikirim' || $s === 'sampai tujuan') return 'badge-info';
    return 'badge-neutral';
}

function resolve_product_image(int $productId, ?string $imageField): string {
    $imageField = trim((string)$imageField);
    if ($imageField !== '') {
        if (file_exists($imageField) && is_file($imageField)) {
            return $imageField;
        }
        if (file_exists('uploads/' . $imageField) && is_file('uploads/' . $imageField)) {
            return 'uploads/' . $imageField;
        }
        $ext = pathinfo($imageField, PATHINFO_EXTENSION);
        if ($ext !== '') {
            $byId = 'uploads/produk' . $productId . '.' . $ext;
            if (file_exists($byId) && is_file($byId)) {
                return $byId;
            }
        }
    }
    foreach (['png', 'jpeg', 'jpg'] as $ext) {
        $candidate = 'uploads/produk' . $productId . '.' . $ext;
        if (file_exists($candidate) && is_file($candidate)) {
            return $candidate;
        }
    }
    return 'gambar2.png';
}

function cart_item_count(): int {
    $count = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if (is_array($item) && isset($item['quantity'])) {
                $count += max(0, (int)$item['quantity']);
            }
        }
    }
    return $count;
}
