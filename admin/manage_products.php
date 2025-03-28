<?php
session_start();
include '../database.php'; // Include the database connection file

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
            header('Location: manage_products.php');
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
    $product_id = $_POST['product_id']; // Retrieve the product_id from the form
    $product_name = htmlspecialchars($_POST['product_name']);
    $quantity_type = htmlspecialchars($_POST['quantity_type']);
    $image_path = $product_image; // Keep current image by default

    // Handle new image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = '../uploads/' . $image_name;

        // Ensure 'uploads/' directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image_tmp, $image_path)) {
            // Update product with new image
            $sql = "UPDATE products SET name = ?, image = ?, quantity_type = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $product_name, $image_path, $quantity_type, $product_id);
            $stmt->execute();

            // Redirect to refresh page
            header('Location: manage_products.php');
            exit();
        } else {
            $error = "Error uploading the image.";
        }
    } else {
        // No image upload, just update name and quantity type
        $sql = "UPDATE products SET name = ?, quantity_type = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $product_name, $quantity_type, $product_id);
        $stmt->execute();

        // Redirect to refresh page
        header('Location: manage_products.php');
        exit();
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
        header('Location: manage_products.php?success=Product deleted successfully.');
        exit();
    } else {
        // Redirect with error message
        header('Location: manage_products.php?error=Failed to delete product.');
        exit();
    }
}


// Fetch product requests
$query = "SELECT pr.id, pr.product_name, pr.product_image, pr.status,pr.quantity_type, f.name AS farmer_name
          FROM product_requests pr
          JOIN users f ON pr.farmer_id = f.user_id
          WHERE pr.status = 'Pending'";
 $result = $conn->query($query);

// Fetch top-selling products (based on the number of orders)
$top_selling_query = "
    SELECT p.id, p.name, p.image, p.quantity_type, COUNT(o.order_id) AS order_count
    FROM products p
    LEFT JOIN orders o ON p.id = o.product_id
    GROUP BY p.id
    ORDER BY order_count DESC
    LIMIT 5"; // Change the LIMIT as needed
$top_selling_result = $conn->query($top_selling_query);

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

/* Styling for the Top Selling Products section */
.section h2 {
    font-size: 22px;
    color: #333;
    font-weight: 600;
}

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

table tbody td img {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
}

    </style>
</head>
<body>


<div class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="fas fa-home"></i> ড্যাশবোর্ড
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../analytics/analytics.php">
                    <i class="fas fa-chart-bar"></i> বিশ্লেষণ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./performance.php">
                    <i class="fas fa-chart-bar"></i>কর্মক্ষমতা
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_farmers.php">
                    <i class="fas fa-users"></i> কৃষকদের পরিচালনা করুন
                </a>
            </li>
            <li class="nav-item">
                            <a class="nav-link" href="manage_suppliers.php">
                                <i class="fas fa-users"></i> সরবরাহকারীদের পরিচালনা করুন
                            </a>
                        </li>


                        <li class="nav-item">
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i> ্রাহকদের পরিচালনা করুন
                </a>
            </li>
                        <li class="nav-item">
                              <a class="nav-link" href="manage_products.php">
                                  <i class="fas fa-users"></i> পণ্য পরিচালনা করুন
                                           </a>
                                                </li>
            

            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> লগআউট
                </a>
            </li>
        </ul>
    </div>



    <header>
        <h1>পণ্য পরিচালনা করুন - স্মার্টকৃষি</h1>
        <a href="../logout.php" class="button">লগআউট</a>
    </header>








    <div class="container">
        <!-- Display Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['success']); ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>




<h2  style="text-align: center;" class="mt-4">পণ্যের অনুরোধ</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th style="text-align: center;">#</th>
            <th style="text-align: center;">পণ্যের নাম</th>
            <th style="text-align: center;">ছবি</th>
            <th style="text-align: center;">কৃষকের নাম</th>
            <th style="text-align: center;">পরিমাণের ধরণ</th>
            <th style="text-align: center;">অবস্থা</th>
            <th style="text-align: center;">ক্রিয়া</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td>
                        <img src="../<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" width="100">
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


<!-- Top Selling Products Section -->
<div class="section">
    <h2 style="text-align: center;">সর্বাধিক বিক্রিত পণ্য</h2>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">পণ্য আইডি</th>
                <th style="text-align: center;">ছবি</th>
                <th style="text-align: center;">নাম</th>
                <th style="text-align: center;">পরিমাণের ধরণ</th>
                <th style="text-align: center;">বিক্রি হওয়া অর্ডার</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($top_selling_result->num_rows > 0): ?>
                <?php while ($row = $top_selling_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td>
                            <!-- Display Product Image -->
                            <img src="../<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['name']); ?>" width="100" height="100">
                        </td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['quantity_type']); ?></td>
                        <td><?= htmlspecialchars($row['order_count']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No top-selling products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<!-- Manage Products -->
<div class="section">
    <h2>পণ্য পরিচালনা করুন</h2>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">পণ্য আইডি</th>
                <th style="text-align: center;">ছবি</th>
                <th style="text-align: center;">নাম</th>
                <th style="text-align: center;">পরিমাণের ধরণ</th>

                <th style="text-align: center;">কর্ম</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']); ?></td>
                    <td>
                        <!-- Display Product Image -->
                        <img src="../<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" width="100" height="100">
                    </td>
                    <td><?= htmlspecialchars($product['name']); ?></td>
                    <td>
                        <!-- Display Quantity Type (per-piece or per-kg) -->
                        <?= htmlspecialchars($product['quantity_type']); ?>
                    </td>
                    <td>
                        <!-- Edit Product -->
                        <a href="manage_products.php?edit_product=<?= $product['id']; ?>" class="button">Edit</a>
                        <!-- Delete Product -->
                        <a href="manage_products.php?delete_product=<?= $product['id']; ?>" class="button" style="background: #d9534f;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add or Edit Product Form -->
<form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
    <h3><?= isset($_GET['edit_product']) ? 'Edit Product' : 'Add New Product'; ?></h3>

    <?php if (isset($error)): ?>
        <p class="error" style="color: red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php
    $product_name = '';
    $product_image = '';
    $quantity_type = 'Per-KG'; // Default value

    if (isset($_GET['edit_product'])) {
        $product_id = (int)$_GET['edit_product']; // Typecast to int for security
        $result = $conn->query("SELECT * FROM products WHERE id = $product_id");

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $product_name = htmlspecialchars($product['name']);
            $product_image = htmlspecialchars($product['image']);
            $quantity_type = htmlspecialchars($product['quantity_type']);
        } else {
            $error = "Product not found or invalid ID.";
        }
    }
    ?>

    <label for="product_name">Name:</label>
    <input type="text" id="product_name" name="product_name" value="<?= $product_name; ?>" required>

    <label for="product_image">Upload Picture (optional):</label>
    <input type="file" id="product_image" name="product_image" accept="image/*">
    <?php if ($product_image): ?>
        <p>Current Image: <img src="<?= htmlspecialchars($product_image); ?>" alt="Product Image" style="max-width: 100px; max-height: 100px;"></p>
    <?php endif; ?>

    <label for="quantity_type">Quantity Type:</label>
    <select id="quantity_type" name="quantity_type">
        <option value="Per-KG" <?= $quantity_type === 'Per-KG' ? 'selected' : ''; ?>>Per KG</option>
        <option value="Per-Piece" <?= $quantity_type === 'Per-Piece' ? 'selected' : ''; ?>>Per Piece</option>
    </select>

    <button type="submit" name="<?= isset($_GET['edit_product']) ? 'update_product' : 'add_product'; ?>" class="button"style="background: #00ff00;">
        <?= isset($_GET['edit_product']) ? 'Update Product' : 'Add Product'; ?>
    </button>

    <?php if (isset($_GET['edit_product'])): ?>
        <a href="manage_products.php" class="button" style="background: #ff0000;">Cancel</a>
    <?php endif; ?>
</form>



</body>
</html>
