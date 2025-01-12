<?php
session_start();
include 'database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'Supplier'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Supplier') {
    header("Location: login.php"); // Redirect to login page if not authenticated
    exit();
}

// Initialize messages
$error = '';
$success_message = '';

// Initialize filter variables
$price_min = $_GET['price_min'] ?? 0;
$price_max = $_GET['price_max'] ?? 1000;
$quantity_type = $_GET['quantity_type'] ?? '';

// Handle adding new supply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supply'])) {
    $supply_name = $_POST['supply_name'];
    $quantity = $_POST['quantity'];
    $quantity_type = $_POST['quantity_type'];
    $price = $_POST['price'];
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['supply_image']) && $_FILES['supply_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }
        $target_file = $target_dir . basename($_FILES["supply_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            // Move the file to the target directory
            if (move_uploaded_file($_FILES["supply_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Failed to upload the image.";
            }
        }
    }

    if (empty($error)) {
        try {
            $sql = "INSERT INTO supplies (supplier_id, supply_name, quantity, quantity_type, price, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isidss", $_SESSION['user_id'], $supply_name, $quantity, $quantity_type, $price, $image_path);
            if ($stmt->execute()) {
                $success_message = "Supply added successfully!";
            } else {
                $error = "Failed to add supply.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle supply deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_supply'])) {
    $supply_id = $_POST['supply_id'];

    try {
        $sql = "DELETE FROM supplies WHERE supply_id = ? AND supplier_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $supply_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success_message = "Supply deleted successfully!";
        } else {
            $error = "Failed to delete supply.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle supply editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_supply'])) {
    $supply_id = $_POST['supply_id'];
    $supply_name = $_POST['supply_name'];
    $quantity = $_POST['quantity'];
    $quantity_type = $_POST['quantity_type'];
    $price = $_POST['price'];
    $existing_image = $_POST['existing_image'];

    $image_path = $existing_image;

    // Handle image upload
    if (isset($_FILES['supply_image']) && $_FILES['supply_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["supply_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["supply_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Update database record
    try {
        $sql = "UPDATE supplies SET supply_name = ?, quantity = ?, quantity_type = ?, price = ?, image = ? WHERE supply_id = ? AND supplier_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisisii", $supply_name, $quantity, $quantity_type, $price, $image_path, $supply_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success_message = "Supply updated successfully!";
        } else {
            $error = "Failed to update supply.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch supplies for the logged-in supplier with filtering
$supplier_id = $_SESSION['user_id'];
try {
    $sql = "
    SELECT *
    FROM supplies
    WHERE supplier_id = ?
    AND price BETWEEN ? AND ?
    AND (quantity_type = ? OR ? = '')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $supplier_id, $price_min, $price_max, $quantity_type, $quantity_type);
    $stmt->execute();
    $supplies_result = $stmt->get_result();

    // Subquery to fetch the most expensive supply
    $sql_most_expensive = "
    SELECT supply_name, price
    FROM supplies
    WHERE supplier_id = ?
    AND price = (SELECT MAX(price) FROM supplies WHERE supplier_id = ?)";
    $stmt_expensive = $conn->prepare($sql_most_expensive);
    $stmt_expensive->bind_param("ii", $supplier_id, $supplier_id);
    $stmt_expensive->execute();
    $expensive_result = $stmt_expensive->get_result();
    $most_expensive = $expensive_result->fetch_assoc();
} catch (Exception $e) {
    $error = "Error fetching supplies: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - AgriBuzz</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f8fc;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        .form-container, .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            padding: 20px;
        }
        .form-container h2, .table-container h2 {
            margin-top: 0;
            font-size: 20px;
            color: #5cb85c;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
        }
        form input[type="text"], form input[type="number"], form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        form input[type="submit"] {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        form input[type="submit"]:hover {
            background: #4cae4c;
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
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Supplier Dashboard - AgriBuzz</h1>
        <a href="logout.php">Logout</a>
    </header>

    <div class="container">
        <!-- Filter Form -->
        <div class="form-container">
            <h2>Filter Supplies</h2>
            <form method="GET" action="supplier.php">
                <label for="price_min">Min Price:</label>
                <input type="number" name="price_min" value="<?= htmlspecialchars($price_min); ?>">

                <label for="price_max">Max Price:</label>
                <input type="number" name="price_max" value="<?= htmlspecialchars($price_max); ?>">

                <label for="quantity_type">Quantity Type:</label>
                <select name="quantity_type">
                    <option value="">All</option>
                    <option value="Per-Kg" <?= $quantity_type === 'Per-Kg' ? 'selected' : ''; ?>>Per-Kg</option>
                    <option value="Per-Piece" <?= $quantity_type === 'Per-Piece' ? 'selected' : ''; ?>>Per-Piece</option>
                </select>

                <input type="submit" value="Filter">
            </form>
        </div>

        <!-- Add New Supply Form -->
        <div class="form-container">
            <h2>Add New Supply</h2>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success"><?= htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <form method="POST" action="supplier.php" enctype="multipart/form-data">
                <label for="supply_name">Supply Name:</label>
                <input type="text" name="supply_name" required>

                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" required>

                <label for="price">Price:</label>
                <input type="text" name="price" required>

                <label for="supply_image">Image:</label>
                <input type="file" name="supply_image" accept="image/*">

                <input type="submit" name="add_supply" value="Add Supply">
            </form>
        </div>

        <!-- Most Expensive Supply -->
        <div class="table-container">
            <h2>Most Expensive Supply</h2>
            <?php if ($most_expensive): ?>
                <p><strong>Supply Name:</strong> <?= htmlspecialchars($most_expensive['supply_name']); ?></p>
                <p><strong>Price:</strong> <?= htmlspecialchars($most_expensive['price']); ?></p>
            <?php else: ?>
                <p>No supplies found.</p>
            <?php endif; ?>
        </div>

        <!-- Supplies Table -->
        <div class="table-container">
            <h2>Your Supplies</h2>
            <table>
                <thead>
                    <tr>
                        <th>Supply ID</th>
                        <th>Supply Name</th>
                        <th>Quantity</th>
                        <th>Quantity Type</th>
                        <th>Price</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($supply = $supplies_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($supply['supply_id']); ?></td>
                            <td><?= htmlspecialchars($supply['supply_name']); ?></td>
                            <td><?= htmlspecialchars($supply['quantity']); ?></td>
                            <td><?= htmlspecialchars($supply['quantity_type']); ?></td>
                            <td><?= htmlspecialchars($supply['price']); ?></td>
                            <td>
                                <?php if (!empty($supply['image'])): ?>
                                    <img src="<?= htmlspecialchars($supply['image']); ?>" alt="Supply Image" style="max-width: 300px;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td>
                               <!-- Edit Form -->
                               <form method="POST" action="supplier.php" enctype="multipart/form-data" style="display: inline-block;">
                                   <input type="hidden" name="supply_id" value="<?= $supply['supply_id']; ?>">
                                   <input type="hidden" name="existing_image" value="<?= $supply['image']; ?>">

                                   <!-- Supply Name -->
                                   <label>Name:
                                       <input type="text" name="supply_name" value="<?= htmlspecialchars($supply['supply_name']); ?>" required>
                                   </label>

                                   <!-- Quantity -->
                                   <label>Qty:
                                       <input type="number" name="quantity" value="<?= htmlspecialchars($supply['quantity']); ?>" required>
                                   </label>

                                   <!-- Quantity Type -->
                                   <label>Quantity Type:
                                       <select name="quantity_type" required>
                                           <option value="Per-Kg" <?= $supply['quantity_type'] === 'Per-Kg' ? 'selected' : ''; ?>>Per-Kg</option>
                                           <option value="Per-Piece" <?= $supply['quantity_type'] === 'Per-Piece' ? 'selected' : ''; ?>>Per-Piece</option>
                                       </select>
                                   </label>

                                   <!-- Price -->
                                   <label>Price:
                                       <input type="text" name="price" value="<?= htmlspecialchars($supply['price']); ?>" required>
                                   </label>

                                   <!-- Image Upload -->
                                   <label>Image:
                                       <input type="file" name="supply_image" accept="image/*">
                                   </label>

                                   <!-- Save Button -->
                                   <input type="submit" name="edit_supply" value="Save">
                               </form>

                                <!-- Delete Form -->
                                <form method="POST" action="supplier.php" style="display: inline-block;">
                                    <input type="hidden" name="supply_id" value="<?= $supply['supply_id']; ?>">
                                    <input type="submit" name="delete_supply" value="Delete" onclick="return confirm('Are you sure?')">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>