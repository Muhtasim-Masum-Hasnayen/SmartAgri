<?php
include('database.php'); // Include the database connection file
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch farmer ID
$farmer_id = $_SESSION['user_id'];

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = $_POST['product_name'];
    $quantityType = $_POST['quantity_type'];
    $image = $_FILES['product_image'];

    // Validate inputs
    if (!empty($productName) && !empty($quantityType) && !empty($image['name'])) {
        // Save the uploaded image
        $targetDir = "../uploads/";
        $imageName = basename($image['name']);
        $targetFilePath = $targetDir . time() . "_" . $imageName;

        // Ensure the uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
            // Insert product request into the database
            if ($conn) { // Check connection before using
                $query = "INSERT INTO product_requests (farmer_id, product_name, product_image, quantity_type, status) VALUES (?, ?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("isss", $farmer_id, $productName, $targetFilePath, $quantityType);
                    if ($stmt->execute()) {
                        $message = "Product request submitted successfully!";
                    } else {
                        $message = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing statement: " . $conn->error;
                }
            } else {
                $message = "Database connection error.";
            }
        } else {
            $message = "Failed to upload image.";
        }
    } else {
        $message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">Request to Add New Product</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="addNewProduct.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" name="product_name" id="product_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="product_image" class="form-label">Upload Product Image</label>
                <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="quantity_type" class="form-label">Quantity Type</label>
                <select name="quantity_type" id="quantity_type" class="form-control" required>
                    <option value="Per-KG">Per KG</option>
                    <option value="Per-Piece">Per Piece</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Submit Request</button>
        </form>

        <a href="farmer.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
