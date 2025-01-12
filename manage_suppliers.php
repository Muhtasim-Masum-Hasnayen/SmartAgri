<?php
session_start();
include 'database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'Admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Initialize messages
$error = '';
$success_message = '';

// Fetch all supplies
$supplies_result = $conn->query("SELECT s.*, u.name AS supplier_name FROM supplies s JOIN users u ON s.supplier_id = u.user_id");


// Fetch top 5 suppliers by revenue
$top_suppliers_query = "SELECT
                            u.name AS supplier_name,
                            SUM(s.quantity) AS total_supplies,
                            SUM(s.price * s.quantity) AS total_revenue
                        FROM
                            supplies s
                        JOIN
                            users u ON s.supplier_id = u.user_id
                        GROUP BY
                            s.supplier_id
                        ORDER BY
                            total_revenue DESC
                        LIMIT 5";
$top_suppliers_result = $conn->query($top_suppliers_query);

// Fetch suppliers with inconsistent pricing (Price Fluctuations > 50)
$inconsistent_pricing_query = "SELECT
                                    u.name AS supplier_name,
                                    MAX(s.price) AS max_price,
                                    MIN(s.price) AS min_price,
                                    MAX(s.price) - MIN(s.price) AS price_difference
                                FROM
                                    supplies s
                                JOIN
                                    users u ON s.supplier_id = u.user_id
                                GROUP BY
                                    s.supplier_id
                                HAVING
                                    price_difference > 50
                                ORDER BY
                                    price_difference DESC";
$inconsistent_pricing_result = $conn->query($inconsistent_pricing_query);

// Handle Add Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $supplier_name = htmlspecialchars($_POST['supplier_name']);
    $supplier_email = htmlspecialchars($_POST['supplier_email']);
    $supplier_phone = htmlspecialchars($_POST['supplier_phone']);

    // Insert new supplier into the database
    $sql = "INSERT INTO suppliers (name, email, phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $supplier_name, $supplier_email, $supplier_phone);

    if ($stmt->execute()) {
        $success_message = "Supplier added successfully!";
    } else {
        $error = "Failed to add supplier.";
    }
}

// Handle Edit Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_supplier'])) {
    $supplier_id = htmlspecialchars($_POST['supplier_id']);
    $supplier_name = htmlspecialchars($_POST['supplier_name']);
    $supplier_email = htmlspecialchars($_POST['supplier_email']);
    $supplier_phone = htmlspecialchars($_POST['supplier_phone']);

    // Update supplier details in the database
    $sql = "UPDATE suppliers SET name = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $supplier_name, $supplier_email, $supplier_phone, $supplier_id);

    if ($stmt->execute()) {
        $success_message = "Supplier updated successfully!";
    } else {
        $error = "Failed to update supplier.";
    }
}

// Handle Delete Supplier
if (isset($_GET['delete_supplier']) && is_numeric($_GET['delete_supplier'])) {
    $supplier_id = $_GET['delete_supplier'];

    $sql = "DELETE FROM suppliers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        $success_message = "Supplier deleted successfully!";
    } else {
        $error = "Failed to delete supplier.";
    }
}

// Fetch all suppliers
$suppliers_result = $conn->query("SELECT * FROM suppliers");



// Handle advanced filtering
$filtered_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_supplies'])) {
    $min_price = htmlspecialchars($_POST['min_price']);
    $max_price = htmlspecialchars($_POST['max_price']);
    $min_quantity = htmlspecialchars($_POST['min_quantity']);
    $max_quantity = htmlspecialchars($_POST['max_quantity']);
    $supplier_name = '%' . htmlspecialchars($_POST['supplier_name']) . '%';

    $sql = "SELECT
                s.supply_id,
                u.name AS supplier_name,
                s.quantity,
                s.price
            FROM
                supplies s
            JOIN
                users u ON s.supplier_id = u.user_id
            WHERE
                s.price BETWEEN ? AND ?
            AND
                s.quantity BETWEEN ? AND ?
            AND
                u.name LIKE ?
            ORDER BY
                s.price ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddiis", $min_price, $max_price, $min_quantity, $max_quantity, $supplier_name);
    $stmt->execute();
    $filtered_result = $stmt->get_result();
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>





/* Active link background */
.sidebar .nav-link.active {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for active state */
    color: #ffffff;
}



/* Main Content */
.main-content {
    margin-left: 0;
    padding: 20px;
    background-color: #ffffff;
    transition: margin-left 0.3s ease-in-out;
}

/* Sidebar hover triggers main content to shift */
.sidebar:hover + .main-content {
    margin-left: 260px; /* Shifts main content to make space for sidebar */
}

/* Header with Gradient */
header {
    background: linear-gradient(to right, #28a745, #218838); /* Green gradient */
    color: white;
    padding: 15px 30px;
    text-align: center;
    border-bottom: 3px solid #1e7e34;
}

header h1 {
    margin: 0;
    font-size: 24px;
}

header a {
    color: white;
    text-decoration: none;
    font-size: 14px;
    margin-right: 20px;
}

header a:hover {
    text-decoration: underline;
}

/* Container Styling */
.container {
    width: 95%;
    max-width: 1300px;
    margin: 0 auto;
    padding: 30px;
}

/* Section Styling */
.section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    padding: 25px;
}

.section h2 {
    margin-top: 0;
    font-size: 22px;
    color: #333;
    font-weight: 600;
}

/* Table Styling with Gradient Header */
table {
    width: 90%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

table thead th {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for table header */
    color: #ffffff;
    text-align: left;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 600;
}

table tbody td {
    border: 1px solid #ddd;
    padding: 15px;
    font-size: 14px;
    color: #495057;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

table tbody td .button {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for button */
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

table tbody td .button:hover {
    background: linear-gradient(to right, #0056b3, #004085); /* Darker gradient on hover */
}

/* Form Styling */
form label {
    display: block;
    margin: 12px 0 6px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
}

form input[type="text"],
form input[type="number"],
form select,
form input[type="file"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    transition: border 0.3s ease;
}

form input[type="text"]:focus,
form input[type="number"]:focus,
form select:focus {
    border-color: #007bff;
    background-color: #ffffff;
}

form input[type="submit"] {
    background: linear-gradient(to right, #28a745, #218838); /* Green gradient for buttons */
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form input[type="submit"]:hover {
    background: linear-gradient(to right, #218838, #1e7e34); /* Darker green on hover */
}

/* Notifications */
.error {
    color: #dc3545;
    font-weight: bold;
    margin-top: 10px;
}

.success {
    color: #28a745;
    font-weight: bold;
    margin-top: 10px;
}

/* Stylish Back Button */
        .back-button {
            display: inline-block;
            background: linear-gradient(to right, #ff7e5f, #feb47b); /* Gradient for button */
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-button:hover {
            background: linear-gradient(to right, #feb47b, #ff7e5f); /* Reverse gradient on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }


    </style>


<body>
<div class="container mt-3">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="admin.php" class="back-button">&larr; Back to Admin Panel</a>
        </div>

         <!-- Top Suppliers Section -->
            <section>
                <h2>Top 5 Suppliers by Revenue</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Total Supplies</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($supplier = $top_suppliers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $supplier['supplier_name'] ?></td>
                            <td><?= $supplier['total_supplies'] ?></td>
                            <td>$<?= number_format($supplier['total_revenue'], 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <div class="container mt-3">
            <!-- Filter Form -->
            <section class="mb-4">
                <h2>Filter Supplies</h2>
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="min_price">Min Price</label>
                            <input type="number" step="0.01" name="min_price" id="min_price" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_price">Max Price</label>
                            <input type="number" step="0.01" name="max_price" id="max_price" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="min_quantity">Min Quantity</label>
                            <input type="number" name="min_quantity" id="min_quantity" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_quantity">Max Quantity</label>
                            <input type="number" name="max_quantity" id="max_quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supplier_name">Supplier Name</label>
                            <input type="text" name="supplier_name" id="supplier_name" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="filter_supplies" class="btn btn-primary">Apply Filter</button>
                </form>
            </section>

            <!-- Filtered Results -->
            <?php if ($filtered_result): ?>
                <section>
                    <h2>Filtered Supplies</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Supply ID</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($supply = $filtered_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $supply['supply_id'] ?></td>
                                    <td><?= $supply['supplier_name'] ?></td>
                                    <td><?= $supply['quantity'] ?></td>
                                    <td>$<?= number_format($supply['price'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>

            <!-- Inconsistent Pricing Section -->
                <section>
                    <h2>Suppliers with Inconsistent Pricing</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Max Price</th>
                                <th>Min Price</th>
                                <th>Price Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($supplier = $inconsistent_pricing_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $supplier['supplier_name'] ?></td>
                                <td>$<?= number_format($supplier['max_price'], 2) ?></td>
                                <td>$<?= number_format($supplier['min_price'], 2) ?></td>
                                <td>$<?= number_format($supplier['price_difference'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>

     <section>

     <h2>Manage Supplies</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Supply ID</th>
                                        <th>Supplier</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($supply = $supplies_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $supply['supply_id'] ?></td>

                                        <td><?= $supply['supplier_name'] ?></td>
                                        <td><?= $supply['quantity'] ?></td>
                                        <td>$<?= number_format($supply['price'], 2) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>