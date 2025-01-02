<?php
session_start();
include 'database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'Admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if not authenticated
    exit();
}
// Initialize messages
$error = '';
$success_message = '';

// Fetch all users
$users_result = $conn->query("SELECT * FROM users");

// Fetch all products
$products_result = $conn->query("SELECT * FROM products");

// Fetch all supplies
$supplies_result = $conn->query("SELECT s.*, u.name AS supplier_name FROM supplies s JOIN users u ON s.supplier_id = u.user_id");

// Fetch all orders
$orders_result = $conn->query("SELECT o.*, p.name AS product_name, u.name AS customer_name 
                               FROM orders o 
                               JOIN products p ON o.product_id = p.id
                               JOIN users u ON o.customer_id = u.user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgriBuzz</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f8fc;
            color: #333;
        }
        header {
            background: #5cb85c;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-bottom: 2px solid #4cae4c;
        }
        header h1 {
            margin: 0;
        }
        header a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-left: 15px;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            padding: 20px;
        }
        .section h2 {
            margin-top: 0;
            font-size: 22px;
            color: #5cb85c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table thead th {
            background: #5cb85c;
            color: white;
            text-align: left;
            padding: 10px;
        }
        table tbody td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .button {
            background: #5cb85c;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .button:hover {
            background: #4cae4c;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard - SmartAgri</h1>
        <a href="logout.php" class="button">Logout</a>
    </header>

    <div class="container">
        <!-- Manage Users -->
        <div class="section">
            <h2>Manage Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']); ?></td>
                            <td><?= htmlspecialchars($user['name']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id']; ?>" class="button">Edit</a>
                                <a href="delete_user.php?id=<?= $user['user_id']; ?>" class="button" style="background: #d9534f;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Products -->
        <div class="section">
            <h2>Manage Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']); ?></td>
                            <td><?= htmlspecialchars($product['name']); ?></td>
                            <td>₹<?= htmlspecialchars($product['price']); ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $product['id']; ?>" class="button">Edit</a>
                                <a href="delete_product.php?id=<?= $product['id']; ?>" class="button" style="background: #d9534f;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Supplies -->
        <div class="section">
            <h2>Manage Supplies</h2>
            <table>
                <thead>
                    <tr>
                        <th>Supply ID</th>
                        <th>Supplier</th>
                        <th>Supply Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($supply = $supplies_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($supply['supply_id']); ?></td>
                            <td><?= htmlspecialchars($supply['supplier_name']); ?></td>
                            <td><?= htmlspecialchars($supply['supply_name']); ?></td>
                            <td><?= htmlspecialchars($supply['quantity']); ?></td>
                            <td>₹<?= htmlspecialchars($supply['price']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Orders -->
        <div class="section">
            <h2>Manage Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Quantity</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']); ?></td>
                            <td><?= htmlspecialchars($order['product_name']); ?></td>
                            <td><?= htmlspecialchars($order['customer_name']); ?></td>
                            <td><?= htmlspecialchars($order['quantity']); ?></td>
                            <td><?= htmlspecialchars($order['order_date']); ?></td>
                            <td><?= htmlspecialchars($order['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
