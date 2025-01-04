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

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = htmlspecialchars($_POST['product_name']);
    $product_price = htmlspecialchars($_POST['product_price']);

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = 'uploads/' . $image_name; // Path where the image will be stored

        // Move the uploaded image to the desired location
        move_uploaded_file($image_tmp, $image_path);

        // Insert the product into the database
        $sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $product_name, $product_price, $image_path);
        $stmt->execute();

        // Redirect or display a success message
        header('Location: admin.php'); // Redirect to refresh the page and show the new product
        exit();
    } else {
        $error = "Error uploading image.";
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
        <!-- Display Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['success']); ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

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
                                <!-- Delete User -->
                                <a href="admin.php?delete_user=<?= $user['user_id']; ?>" class="button" style="background: #d9534f;">Delete</a>
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
                            <td>Tk. <?= htmlspecialchars($product['price']); ?></td>
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


           <?php
           // Handle product addition
           if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
               $product_name = htmlspecialchars($_POST['product_name']);
               $product_price = htmlspecialchars($_POST['product_price']);
               $image_path = ''; // Initialize as empty

               // Check if the image was uploaded
               if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                   $image_tmp = $_FILES['product_image']['tmp_name'];
                   $image_name = basename($_FILES['product_image']['name']); // Extract just the file name
                   $image_path = $image_name; // Save only the file name in the database

                   // Ensure the 'uploads/' directory exists
                   if (!is_dir('uploads')) {
                       mkdir('uploads', 0777, true);
                   }

                   // Move the uploaded file to the 'uploads/' directory
                   if (move_uploaded_file($image_tmp, 'uploads/' . $image_name)) {
                       // Insert the product into the database with the image name only
                       $sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
                       $stmt = $conn->prepare($sql);
                       $stmt->bind_param("sss", $product_name, $product_price, $image_path);
                       $stmt->execute();

                       // Redirect to refresh the page and show the new product list
                       header('Location: admin.php');
                       exit();
                   } else {
                       $error = "Error uploading the image to the server.";
                   }
               } else {
                   $error = "Please upload a valid image.";
               }
           }
           ?>







            <!-- Add Product Form -->
            <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                <h3>Add New Product</h3>
                <?php if (isset($error)): ?>
                    <p class="error"><?= $error; ?></p>
                <?php endif; ?>
                <label for="product_name">Name:</label>
                <input type="text" id="product_name" name="product_name" required>

                <label for="product_price">Price:</label>
                <input type="number" id="product_price" name="product_price" required>

                <label for="product_image">Upload Picture (optional)</label>
                <input type="file" id="product_image" name="product_image" accept="image/*">
                <button type="submit" name="add_product" class="button">Add Product</button>
            </form>


        </div>

        <?php
        // Handle product edit
        // Handle product edit
        if (isset($_GET['edit_product'])) {
            $product_id = $_GET['edit_product'];

            // Fetch the product details for the edit form
            $result = $conn->query("SELECT * FROM products WHERE id = $product_id");
            $product = $result->fetch_assoc();

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
                $product_name = htmlspecialchars($_POST['product_name']);
                $product_price = htmlspecialchars($_POST['product_price']);
                $image_path = $product['image']; // Keep the current image by default

                // Check if the image was uploaded
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                    $image_tmp = $_FILES['product_image']['tmp_name'];
                    $image_name = basename($_FILES['product_image']['name']); // Just the file name, not full path
                    $image_path = $image_name; // Store only the image name

                    // Move the uploaded image to the desired location in the 'uploads/' directory
                    if (move_uploaded_file($image_tmp, 'uploads/' . $image_path)) {
                        // Update the product details in the database
                        $sql = "UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssi", $product_name, $product_price, $image_path, $product_id);
                        $stmt->execute();

                        // Redirect to refresh the page and show the updated product list
                        header('Location: admin.php');
                        exit();
                    } else {
                        $error = "Error uploading image.";
                    }
                } else {
                    // If no image uploaded, update product without changing the image
                    $sql = "UPDATE products SET name = ?, price = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $product_name, $product_price, $product_id);
                    $stmt->execute();

                    // Redirect to refresh the page and show the updated product list
                    header('Location: admin.php');
                    exit();
                }
            }
        }

        ?>

        <!-- Edit Product Form -->
        <?php if (isset($_GET['edit_product'])): ?>
            <div class="section">
                <h2>Edit Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product['name']); ?>" required>

                    <label for="product_price">Price</label>
                    <input type="number" id="product_price" name="product_price" value="<?= htmlspecialchars($product['price']); ?>" required>

                    <label for="product_image">Upload Picture (optional)</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*">

                    <button type="submit" name="update_product" class="button">Update Product</button>
                    <a href="admin.php" class="button" style="background: #f0ad4e;">Cancel</a>
                </form>
            </div>
        <?php endif; ?>



    </div>
</body>
</html>
