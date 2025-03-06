<?php
session_start();
include('../database.php');


// Ensure that the user is logged in as a supplier
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login page if not logged in
    exit();
}

$supplier_id = $_SESSION['user_id']; // Supplier's ID from session

// Handle the status change if the form is submitted
if (isset($_POST['change_status'])) {
    // Get the order ID and the new status from the form
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Update the status of the order
    $query = "UPDATE supplier_sales SET status = ? WHERE supplies_sale_id = ? AND supplier_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $new_status, $order_id, $supplier_id);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: supplier_orders.php?success=Status updated successfully");
        exit(); // Make sure to stop further execution
    } else {
        // Handle error
        echo "Error updating status: " . $stmt->error;
    }
}

// Query to fetch orders associated with the supplier
$query = "SELECT 
            o.supplies_sale_id,
            u.name AS farmer_name,
            o.total_price,
            o.sale_date,
            o.status
          FROM supplier_sales o
          JOIN farmer f ON o.farmer_id = f.farmer_id
          JOIN users u ON f.farmer_id = u.user_id
          WHERE o.supplier_id = ?"; 

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <style>
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 250px;
            background: #388e3c;
            padding-top: 80px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: #2e7d32;
            padding-left: 35px;
        }

        .sidebar-menu a.active {
            background: #2e7d32;
            border-left: 4px solid #81c784;
        }

        .sidebar-menu .logout-btn {
            color: #fff;
            padding: 15px 25px;
            text-decoration: none;
            display: block;
            font-size: 16px;
            transition: all 0.3s ease;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 10px;
        }

        .sidebar-menu .logout-btn:hover {
            background: #d32f2f;
            padding-left: 35px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        /* Button Styles */
        .btn-primary {
            background-color: #388e3c;
            border-color: #388e3c;
        }

        .btn-primary:hover {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }

        /* Status Select Styles */
        .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }

        /* Alert Styles */
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>

</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../supplier.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="supplier_orders.php" class="active"><i class="fas fa-box"></i> Order Management</a></li>
            <li><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h2><i class="fas fa-box"></i> Manage Orders</h2>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_GET['success']; ?>
            </div>
        <?php endif; ?>

        <!-- Orders Table -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Farmer Name</th>
                                <th>Total Amount</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['supplies_sale_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                <td>Taka: <?php echo number_format($row['total_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </td>
                                <td>
                                    <!-- Status change form -->
                                    <form method="POST" action="supplier_orders.php">
                                        <input type="hidden" name="order_id" value="<?php echo $row['supplies_sale_id']; ?>">
                                        <select name="status" class="form-select">
                                            <option value="Pending" <?php echo ($row['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo ($row['status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipping" <?php echo ($row['status'] == 'Shipping') ? 'selected' : ''; ?>>Shipping</option>
                                            <option value="Delivered" <?php echo ($row['status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo ($row['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="change_status" class="btn btn-primary mt-2">Change Status</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
