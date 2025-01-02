<?php
session_start();
include 'database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'supplier'


// Initialize error message
$error = '';

// Handle adding new supply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supply'])) {
    $supply_name = $_POST['supply_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO supplies (supplier_id, supply_name, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isid", $_SESSION['user_id'], $supply_name, $quantity, $price);

    if ($stmt->execute()) {
        $success_message = "Supply added successfully!";
    } else {
        $error = "Failed to add supply: " . $stmt->error;
    }
}

// Fetch supplies for the logged-in supplier
$supplier_id = $_SESSION['user_id'];
$sql = "SELECT * FROM supplies WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$supplies_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - AgriBuzz</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?> (Supplier)</h1>
    <a href="logout.php">Logout</a>

    <h2>Add New Supply</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <form method="POST" action="supplier.php">
        <label for="supply_name">Supply Name:</label>
        <input type="text" name="supply_name" required>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" required>

        <label for="price">Price:</label>
        <input type="text" name="price" required>

        <input type="submit" name="add_supply" value="Add Supply">
    </form>

    <h2>Your Supplies</h2>
    <table>
        <thead>
            <tr>
                <th>Supply ID</th>
                <th>Supply Name</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($supply = $supplies_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $supply['supply_id']; ?></td>
                    <td><?php echo $supply['supply_name']; ?></td>
                    <td><?php echo $supply['quantity']; ?></td>
                    <td><?php echo $supply['price']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Your Orders</h2>
    <?php
    // Fetch orders related to this supplier
    $sql = "SELECT o.*, p.name FROM orders o JOIN products p ON o.product_id = p.id WHERE p.seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $orders_result = $stmt->get_result();
    ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Customer ID</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['product_name']; ?></td>
                    <td><?php echo $order['customer_id']; ?></td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>

</body>
</html>
