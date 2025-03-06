<?php
include('../database.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch farmer ID
$farmer_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-card {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .table th, .table td {
            text-align: center;
        }
        .btn-action {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">Order Management</h1>

        <div class="order-card">
            <h3>Manage Your Orders</h3>
            <p class="text-muted">View and manage your incoming orders below.</p>
            
            <!-- Orders Table -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Crop Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch orders specific to the logged-in farmer
                    $query = "SELECT * FROM orders WHERE farmer_id = ? ORDER BY order_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $farmer_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Display orders
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['order_id']}</td>
                                <td>{$row['customer_name']}</td>
                                <td>{$row['crop_name']}</td>
                                <td>{$row['quantity']}</td>
                                <td><span class='badge bg-" . ($row['status'] == 'Pending' ? 'warning' : 'success') . "'>{$row['status']}</span></td>
                                <td>{$row['order_date']}</td>
                                <td>
                                  
                                    <a href='update_order_status.php?id={$row['order_id']}' class='btn btn-warning btn-sm btn-action'>Update Status</a>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
