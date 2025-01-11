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

/* Sidebar styling */
.sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            padding-top: 20px;
            background-color: #343a40;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .nav-link {
            color: #fff;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .active {
            background-color: #0d6efd;
        }










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



<h2 class="mt-4">Product Requests</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Image</th>
            <th>Farmer Name</th>
            <th>Quantity Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" width="100">
                    </td>
                    <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity_type']); ?></td>

                    <td><span class="badge bg-warning"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <form method="POST" action="process_request.php" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No pending requests</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Manage Products -->
<div class="section">
    <h2>Manage Products</h2>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Quantity Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']); ?></td>
                    <td>
                        <!-- Display Product Image -->
                        <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" width="100" height="100">
                    </td>
                    <td><?= htmlspecialchars($product['name']); ?></td>
                    <td>
                        <!-- Display Quantity Type (per-piece or per-kg) -->
                        <?= htmlspecialchars($product['quantity_type']); ?>
                    </td>
                    <td>
                        <!-- Edit Product -->
                        <a href="admin.php?edit_product=<?= $product['id']; ?>" class="button">Edit</a>
                        <!-- Delete Product -->
                        <a href="admin.php?delete_product=<?= $product['id']; ?>" class="button" style="background: #d9534f;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add or Edit Product Form -->
    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
        <h3><?= isset($_GET['edit_product']) ? 'Edit Product' : 'Add New Product'; ?></h3>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error; ?></p>
        <?php endif; ?>

        <!-- If Editing, Populate Fields with Existing Product Data -->
        <?php
        $product_name = '';
        $product_image = '';
        $quantity_type = 'Per-KG'; // Default value

        if (isset($_GET['edit_product'])) {
            $product_id = $_GET['edit_product'];
            $result = $conn->query("SELECT * FROM products WHERE id = $product_id");
            $product = $result->fetch_assoc();
            $product_name = htmlspecialchars($product['name']);
            $product_image = htmlspecialchars($product['image']);
            $quantity_type = htmlspecialchars($product['quantity_type']);
        }
        ?>

        <label for="product_name">Name:</label>
        <input type="text" id="product_name" name="product_name" value="<?= $product_name; ?>" required>

        <label for="product_image">Upload Picture (optional)</label>
        <input type="file" id="product_image" name="product_image" accept="image/*">

        <label for="quantity_type">Quantity Type:</label>
        <select id="quantity_type" name="quantity_type">

            <option value="Per-KG" <?= $quantity_type == 'Per-KG' ? 'selected' : ''; ?>>Per KG</option>
            <option value="Per-Piece" <?= $quantity_type == 'Per-Piece' ? 'selected' : ''; ?>>Per Piece</option>
        </select>

        <button type="submit" name="<?= isset($_GET['edit_product']) ? 'update_product' : 'add_product'; ?>" class="button"><?= isset($_GET['edit_product']) ? 'Update Product' : 'Add Product'; ?></button>
        <?php if (isset($_GET['edit_product'])): ?>
            <a href="admin.php" class="button" style="background: #f0ad4e;">Cancel</a>
        <?php endif; ?>
    </form>

</div>

                    <!-- Supplies Section -->
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
    </div>
</body>
</html>