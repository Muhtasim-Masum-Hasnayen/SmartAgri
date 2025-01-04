<?php
include('database.php'); // Include database connection
session_start();

$farmer_id = $_SESSION['user_id'];

// Handle Adding Product for Sale
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $product_id = $_POST['product_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $photo_path = '';

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = $target_file;
        }
    }

    // Insert into farmer_products
    $sql = "INSERT INTO farmer_products (farmer_id, product_id, description, price, quantity, photo_path)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisdis", $farmer_id, $product_id, $description, $price, $quantity, $photo_path);
    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $error = "Error adding product.";
    }
}

// Fetch All Products from the products table
$sql = "SELECT * FROM products";
$all_products = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        img {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
    <script>
        // JavaScript for Searching Crops
        function searchCrop() {
            const searchInput = document.getElementById('crop-search').value.toLowerCase();
            const rows = document.querySelectorAll('#product-table tbody tr');
            
            rows.forEach(row => {
                const cropName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (cropName.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function selectCrop(productId, cropName) {
            document.getElementById('product_id').value = productId;
            document.getElementById('selected-crop').textContent = cropName;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Crop Management</h1>

        <!-- Search Box -->
        <div class="search-box">
            <label for="crop-search">Search Crop:</label>
            <input type="text" id="crop-search" onkeyup="searchCrop()" placeholder="Enter crop name">
        </div>

        <!-- Existing Products Table -->
        <table id="product-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Crop Name</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $all_products->fetch_assoc()): ?>
                    <tr>
                        <td><?= $product['id']; ?></td> <!-- product id -->
                        <td><?= htmlspecialchars($product['name']); ?></td> <!-- product name -->
                        <td>
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                No Photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="selectCrop(<?= $product['id']; ?>, '<?= htmlspecialchars($product['name']); ?>')">Select</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Add Product Form -->
        <div class="form-container">
            <h2>Add Crop for Sale</h2>
            <p><strong>Selected Crop:</strong> <span id="selected-crop">None</span></p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="product_id" value="">

                <label for="description">Description:</label>
                <textarea name="description" required></textarea>

                <label for="price">Price:</label>
                <input type="number" name="price" step="0.01" required>

                <label for="quantity">Quantity (in kg):</label>
                <input type="number" name="quantity" required>

                <label for="photo">Photo:</label>
                <input type="file" name="photo">

                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
    </div>
</body>
</html>
