<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['owner']);

$conn = db();

$sql = "SELECT * FROM orders WHERE status = 'Selesai' ORDER BY created_at DESC";
$result = $conn->query($sql);

$total_sales = 0;
$monthly_sales = [];

if ($result && $result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        $total_sales += $order['total_amount'];

        $order_month = date('Y-m', strtotime($order['created_at']));
        if (!isset($monthly_sales[$order_month])) {
            $monthly_sales[$order_month] = 0;
        }
        $monthly_sales[$order_month] += $order['total_amount'];
    }
    ksort($monthly_sales);
    $result->data_seek(0);
}

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chart-canvas-wrap {
            position: relative;
            width: 100%;
            height: 320px;
        }

        .dataTables_wrapper .row {
            margin-bottom: 12px;
        }

        .modal-header {
            background-color: var(--primary);
            color: #fff;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-body h5 {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            margin-top: 16px;
            margin-bottom: 6px;
        }

        .modal-body h5:first-child {
            margin-top: 0;
        }
    </style>
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
                <a href="management-user.php">User Management</a>
                <a href="report.php" class="active">Sales Report</a>
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
                    <h1>Sales Report</h1>
                    <p>Summary of completed orders and monthly revenue performance.</p>
                </div>
                <a href="owner_dashboard.php" class="btn btn-outline btn-sm">&larr; Back to Dashboard</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Orders Completed</div>
                    <div class="stat-value"><?= (int)($result ? $result->num_rows : 0) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Sales Revenue</div>
                    <div class="stat-value accent"><?= format_rp($total_sales) ?></div>
                </div>
            </div>

            <div class="surface-card" style="margin-bottom: 28px;">
                <h2 style="font-size: 1.25rem; color: var(--primary); margin-bottom: 18px;">Monthly Sales Overview</h2>
                <div class="chart-canvas-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="surface-card">
                <h2 style="font-size: 1.25rem; color: var(--primary); margin-bottom: 18px;">Completed Orders</h2>
                <div class="table-responsive" style="border: none;">
                    <table id="salesTable" class="table data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result): ?>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$order['id'] ?></strong></td>
                                        <td><?= e($order['created_at']) ?></td>
                                        <td><strong><?= format_rp($order['total_amount']) ?></strong></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm view-details" data-order-id="<?= (int)$order['id'] ?>">View Details</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Customer Information</h5>
                    <p id="customerInfo"></p>
                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table data-table" style="min-width: 360px;">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody id="orderDetails"></tbody>
                        </table>
                    </div>
                    <h5>Shipping Address</h5>
                    <p id="shippingAddress"></p>
                    <h5>Payment Method</h5>
                    <p id="paymentMethod"></p>
                </div>
            </div>
        </div>
    </div>

    <footer class="portal-footer">
        &copy; 2024 Guspart Autoshop. All Rights Reserved.
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function () {
            $('#salesTable').DataTable();

            $('.view-details').on('click', function () {
                const orderId = $(this).data('order-id');
                $.ajax({
                    url: 'fetch_order_details.php',
                    method: 'POST',
                    data: { order_id: orderId },
                    dataType: 'json',
                    success: function (data) {
                        const emailPart = data.customer_email ? ` (${data.customer_email})` : '';
                        $('#customerInfo').text(`${data.customer_name || 'Customer'}${emailPart}`);
                        $('#shippingAddress').text(data.shipping_address || '-');
                        $('#paymentMethod').text((data.payment_method || '-').replace(/_/g, ' '));
                        let orderDetailsHTML = '';
                        (data.order_items || []).forEach(item => {
                            const priceFmt = Number(item.price || 0).toLocaleString('id-ID');
                            orderDetailsHTML += `<tr>
                                <td>${$('<div>').text(item.product_name).html()}</td>
                                <td>${Number(item.quantity)}</td>
                                <td>Rp ${priceFmt}</td>
                            </tr>`;
                        });
                        $('#orderDetails').html(orderDetailsHTML);
                        $('#detailModal').modal('show');
                    },
                    error: function () {
                        alert('Failed to fetch order details.');
                    }
                });
            });

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_keys($monthly_sales)); ?>,
                    datasets: [{
                        label: 'Total Sales (Rp)',
                        data: <?php echo json_encode(array_values($monthly_sales)); ?>,
                        borderColor: '#D97736',
                        backgroundColor: 'rgba(217, 119, 54, 0.16)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#1B3B3C',
                        tension: 0.25,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#152226' }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#4B5D63' },
                            grid: { color: '#E1E7EA' }
                        },
                        y: {
                            ticks: { color: '#4B5D63' },
                            grid: { color: '#E1E7EA' }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
