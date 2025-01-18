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






// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = htmlspecialchars($_POST['product_name']);
    $quantity_type = htmlspecialchars($_POST['quantity_type']);
    $image_path = ''; // Initialize as empty

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = 'uploads/' .$image_name;

        // Ensure 'uploads/' directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image_tmp, 'uploads/' . $image_name)) {
            // Insert into database
            $sql = "INSERT INTO products (name, image, quantity_type) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $product_name, $image_path, $quantity_type);
            $stmt->execute();

            // Redirect to refresh page
            header('Location: admin.php');
            exit();
        } else {
            $error = "Error uploading the image to the server.";
        }
    } else {
        $error = "Please upload a valid image.";
    }
}

// Handle Update Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $product_name = htmlspecialchars($_POST['product_name']);
    $quantity_type = htmlspecialchars($_POST['quantity_type']);
    $image_path = $product_image; // Keep current image by default

    // Handle new image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = $image_name;

        // Move uploaded image to 'uploads/' folder
        if (move_uploaded_file($image_tmp, 'uploads/' . $image_path)) {
            // Update product in database
            $sql = "UPDATE products SET name = ?, image = ?, quantity_type = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $product_name, $image_path, $quantity_type, $product_id);
            $stmt->execute();

            // Redirect to refresh page
            header('Location: admin.php');
            exit();
        } else {
            $error = "Error uploading image.";
        }
    } else {
        // No image upload, just update name and quantity type
        $sql = "UPDATE products SET name = ?, quantity_type = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $product_name, $quantity_type, $product_id);
        $stmt->execute();

        // Redirect to refresh page
//             header('Location: admin.php');
//             exit();
    }
}

// Handle product deletion
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];

    // Prepare the DELETE query
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect with success message
        header('Location: admin.php?success=Product deleted successfully.');
        exit();
    } else {
        // Redirect with error message
        header('Location: admin.php?error=Failed to delete product.');
        exit();
    }
}





// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Prepare the DELETE query
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect with success message
        header('Location: admin.php?success=User deleted successfully.');
        exit();
    } else {
        // Redirect with error message
        header('Location: admin.php?error=Failed to delete user.');
        exit();
    }
}

// Fetch product requests
$query = "SELECT pr.id, pr.product_name, pr.product_image, pr.status,pr.quantity_type, f.name AS farmer_name
          FROM product_requests pr
          JOIN users f ON pr.farmer_id = f.user_id
          WHERE pr.status = 'Pending'";
 $result = $conn->query($query);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>


/* Sidebar Styling */
.sidebar {
    position: fixed;
    top: 0;
    left: -210px; /* Sidebar is hidden off-screen initially */
    height: 100vh;
    width: 260px;
    background: linear-gradient(to bottom, #3e4e60, #4b5c6b); /* Gradient from dark blue to grey */
    padding-top: 40px;
    transition: left 0.3s ease-in-out; /* Smooth transition for showing and hiding */
}

/* When sidebar is hovered, it slides into view */
.sidebar:hover {
    left: 0; /* Moves the sidebar into view */
}
/* Sidebar link styling: text on the left, icon on the right */
.sidebar .nav-link {
    display: flex;
    justify-content: space-between; /* Space between text and icon */
    align-items: center; /* Vertically center text and icon */
    color: #ffffff;
    padding: 15px 20px;
    text-decoration: none;
    font-size: 16px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

.sidebar .nav-link i {
    margin-left: 10px; /* Add space between the text and the icon */
    order: 1; /* Ensures icon is after the text */
}



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
    width: 100%;
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


    </style>
</head>
<body>


 <!-- Sidebar -->
 <div class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analytics/analytics.php">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_farmers.php">
                    <i class="fas fa-users"></i> Manage Farmers
                </a>
            </li>
            <li class="nav-item">
                            <a class="nav-link" href="manage_suppliers.php">
                                <i class="fas fa-users"></i> Manage Suppliers
                            </a>
                        </li>
                        <li class="nav-item">
                                                    <a class="nav-link" href="manage_products.php">
                                                        <i class="fas fa-users"></i> Manage Products
                                                    </a>
                                                </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i> Manage Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_orders.php">
                    <i class="fas fa-shopping-cart"></i> Manage Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>


    <header>
        <h1>Admin Dashboard - SmartAgri</h1>
        <a href="logout.php" class="button">Logout</a>
    </header>








    <div class="container">
        <!-- Display Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['success']); ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>



        <!-- Manage Users -->
        <div class="section">
            <h2>Manage Users</h2>

            <?php
            // Fetch grouped users by role and limit to last 15 users
            $query = "
                SELECT *
                FROM users
                ORDER BY role, created_at DESC
                LIMIT 15
            ";
            $users_result = $conn->query($query);

            // Group users by role
            $users_by_role = [];
            while ($user = $users_result->fetch_assoc()) {
                $users_by_role[$user['role']][] = $user;
            }
            ?>

            <?php foreach ($users_by_role as $role => $users): ?>
                <h3><?= htmlspecialchars(ucfirst($role)); ?> Users</h3>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_id']); ?></td>
                                <td><?= htmlspecialchars($user['name']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <!-- Delete User -->
                                    <a href="admin.php?delete_user=<?= $user['user_id']; ?>" class="button" style="background: #d9534f;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
</body>
</html>