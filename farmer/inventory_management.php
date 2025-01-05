<?php
include('../database.php');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];
$messages = [];

// Handle Add Inventory
if (isset($_POST['add_inventory'])) {
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];

    if (empty($product_name) || empty($quantity)) {
        $messages[] = ["type" => "danger", "text" => "All fields are required."];
    } else {
        $stmt = $conn->prepare("INSERT INTO inventory (farmer_id, product_name, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $farmer_id, $product_name, $quantity);

        if ($stmt->execute()) {
            $messages[] = ["type" => "success", "text" => "Product added to inventory!"];
        } else {
            $messages[] = ["type" => "danger", "text" => "Failed to add product: " . $stmt->error];
        }

        $stmt->close();
    }
}

// Fetch Farmer's Inventory
$stmt = $conn->prepare("SELECT id, product_name, quantity FROM inventory WHERE farmer_id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$inventory = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Inventory Management</h1>

        <!-- Display Messages -->
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $message['text']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <!-- Add Inventory Form -->
        <form method="POST" class="mb-4">
            <h2>Add Inventory</h2>
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" id="product_name" name="product_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
            </div>
            <button type="submit" name="add_inventory" class="btn btn-primary">Add Product</button>
        </form>

        <!-- Inventory Table -->
        <h2>Your Inventory</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inventory->num_rows > 0): ?>
                    <?php while ($item = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">No inventory found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
