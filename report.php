<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'owner') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch completed orders
$sql = "SELECT * FROM orders WHERE status = 'Selesai'";
$result = $conn->query($sql);

$total_sales = 0;
$monthly_sales = [];

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        $total_sales += $order['total_amount'];

        // Process sales by month
        $order_month = date('Y-m', strtotime($order['created_at']));
        if (!isset($monthly_sales[$order_month])) {
            $monthly_sales[$order_month] = 0;
        }
        $monthly_sales[$order_month] += $order['total_amount'];
    }

    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #e4e4e4;
        }

        header {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        header a {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            margin-right: 25px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        header a:hover {
            background-color: #333333;
        }

        .container {
            margin-top: 40px;
        }

        .info-card {
            background-color: #1f1f1f;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .info-card h2 {
            margin-bottom: 15px;
            font-size: 1.8rem;
            color: #ffa500;
        }

        .info-card p {
            font-size: 1.3rem;
        }

        .table-wrapper {
            background-color: #1f1f1f;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            padding: 35px;
        }

        .table th, .table td {
            background-color: lightgrey;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        .table th {
            background-color: #333333;
            color: #ffffff;
        }

        .btn-info {
            background-color: #ffa500;
            border: none;
            padding: 10px 25px;
            font-size: 1rem;
            color: #121212;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-info:hover {
            background-color: #ff8c00;
        }

        .modal-header {
            background-color: #333333;
            color: #ffffff;
        }

        .modal-body {
            font-size: 1.2rem;
            color: #333;
        }

        footer {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 25px 0;
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
        }

        .footer-link {
            color: #ffa500;
            text-decoration: none;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            header {
                padding: 20px;
            }

            .info-card {
                padding: 25px;
            }

            .table-wrapper {
                padding: 25px;
            }

            .btn-info {
                padding: 8px 20px;
                font-size: 0.9rem;
            }
        }

        .chart-container {
            background-color: #1f1f1f;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .chart-title {
            color: #ffa500;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <header>
        <div><a href="owner_dashboard.php">Owner Dashboard</a></div>
        <h1>Sales Report</h1>
    </header>

    <div class="container mt-5">
        <!-- Sales Summary -->
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="info-card text-center">
                    <h2>Total Orders Completed</h2>
                    <p class="fs-3"><?php echo $result->num_rows; ?></p>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="info-card text-center">
                    <h2>Total Sales</h2>
                    <p class="text-success fs-3">Rp <?php echo number_format($total_sales, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="chart-container">
            <h3 class="chart-title">Monthly Sales Overview</h3>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Orders Table -->
        <div class="table-wrapper">
            <table id="salesTable" class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <button class="btn-info view-details" data-order-id="<?php echo $order['id']; ?>">View Details</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Customer Information</h5>
                    <p id="customerInfo"></p>
                    <h5>Order Details</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetails">
                            <!-- AJAX-loaded content -->
                        </tbody>
                    </table>
                    <h5>Shipping Address</h5>
                    <p id="shippingAddress"></p>
                    <h5>Payment Method</h5>
                    <p id="paymentMethod"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & DataTables Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function () {
            $('#salesTable').DataTable();

            // Handle "View Details" button click
            $('.view-details').on('click', function () {
                const orderId = $(this).data('order-id');
                $.ajax({
                    url: 'fetch_order_details.php',
                    method: 'POST',
                    data: { order_id: orderId },
                    dataType: 'json',
                    success: function (data) {
                        $('#customerInfo').text(`${data.customer_name} (${data.customer_email})`);
                        $('#shippingAddress').text(data.shipping_address);
                        $('#paymentMethod').text(data.payment_method);
                        let orderDetailsHTML = '';
                        data.order_items.forEach(item => {
                            orderDetailsHTML += `<tr>
                                <td>${item.product_name}</td>
                                <td>${item.quantity}</td>
                                <td>Rp ${item.price}</td>
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

            // Chart.js - Monthly Sales Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_keys($monthly_sales)); ?>,
                    datasets: [{
                        label: 'Total Sales (Rp)',
                        data: <?php echo json_encode(array_values($monthly_sales)); ?>,
                        borderColor: '#ffa500',
                        backgroundColor: 'rgba(255, 165, 0, 0.3)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: false, // Disable dynamic resizing
                    maintainAspectRatio: true,
                    aspectRatio: 2, // Fixed aspect ratio
                    scales: {
                        x: {
                            ticks: { color: '#e4e4e4' }
                        },
                        y: {
                            ticks: { color: '#e4e4e4' }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>
