<?php
session_start();
include('../database.php');

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
$price_max = $_GET['price_max'] ?? 900000;
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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - AgriBuzz</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4; /* Light earth tone background */
            color: #333;
            line-height: 1.0;
        }

        .container {
            width: 70%;
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #8bc34a, #4caf50); /* Nature green gradient */
            color: white;
            padding: 40px 60px;
            text-align: center;
            border-bottom: 5px solid #388e3c;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 3px 3px 5px rgba(0, 0, 0, 0.3);
            font-family: 'Georgia', serif;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            margin-left: 30px;
            font-weight: 600;
            text-transform: uppercase;
            transition: color 0.3s ease;
        }

        header a:hover {
            color: #d4af37; /* Gold accent on hover */
        }

        /* Form and Table Container */
        .form-container, .table-container {
            background: #ffffff; /* White background for forms and tables */
            border-radius: 10px;
            width : 60%;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-container:hover, .table-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        /* Form Elements */
        h2 {
            margin-top: 0;
            font-size: 26px;
            color: #4caf50; /* Fresh green accent */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            font-family: 'Georgia', serif;
        }

        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600;
            font-size: 16px;
            color: #333;
        }

        form input[type="text"], form input[type="number"], form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #ccc; /* Soft border */
            border-radius: 8px;
            background: #f0f0f0; /* Soft grey background for inputs */
            color: #333;
            font-size: 16px;
            transition: border 0.3s ease, background 0.3s ease;
        }

        form input[type="text"]:focus, form input[type="number"]:focus, form select:focus {
            border: 2px solid #4caf50; /* Green border on focus */
            outline: none;
            background: #e8f5e9; /* Light green background on focus */
        }

        form input[type="submit"] {
            background: #4caf50; /* Fresh green button */
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            width: 100%;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        form input[type="submit"]:hover {
            background: #388e3c; /* Darker green on hover */
            transform: scale(1.05);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: #fafafa; /* Light background for table */
        }

        table thead th {
            background: #8bc34a; /* Light green header */
            color: #fff;
            text-align: left;
            padding: 16px 20px;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 1px;
            border-bottom: 3px solid #388e3c; /* Green bottom border */
        }

        table tbody td {
            border: 1px solid #ccc; /* Soft border for table cells */
            padding: 15px 20px;
            font-size: 14px;
            color: #333;
            background: #f9f9f9; /* Soft background for table rows */
            transition: background 0.3s ease, transform 0.3s ease;
        }

        table tbody td:hover {
            background: #e8f5e9; /* Light green background on hover */
            transform: scale(1.02);
        }

        table td img {
            max-width: 120px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        table td img:hover {
            transform: scale(1.1);
        }

        table td input[type="submit"] {
            background: #ff5722; /* Nature-inspired red button for delete */
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        table td input[type="submit"]:hover {
            background: #d32f2f; /* Darker red on hover */
            transform: scale(1.05);
        }

        /* Success and Error Messages */
        .success {
            color: #4caf50; /* Green for success */
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            animation: bounceIn 1s;
        }

        .error {
            color: #f44336; /* Red for error */
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            animation: bounceIn 1s;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }

        /* Sidebar Styles */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100%;
    width: 250px;
    background: #388e3c;
    padding-top: 80px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    padding: 0;
    margin: 0;
}

.sidebar-menu a {
    display: block;
    padding: 15px 25px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    transition: all 0.3s ease;
}

.sidebar-menu a:hover {
    background: #2e7d32;
    padding-left: 35px;
}

.sidebar-menu a.active {
    background: #2e7d32;
    border-left: 4px solid #81c784;
}

/* Adjust main content to accommodate sidebar */
.container {
    margin-left: 250px;
    padding: 20px;
}

/* Adjust header to accommodate sidebar */
header {
    margin-left: 250px;
    padding: 20px;
    background: #4caf50;
    color: white;
    text-align: center;
    position: relative;
}


/* Add these to your existing sidebar styles */
.sidebar-menu .logout-btn {
    color: #fff;
    padding: 15px 25px;
    text-decoration: none;
    display: block;
    font-size: 16px;
    transition: all 0.3s ease;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 10px;
}

.sidebar-menu .logout-btn:hover {
    background: #d32f2f;
    padding-left: 35px;
}





    </style>
</head>
<body>
<div class="sidebar">
    <ul class="sidebar-menu">
<li><a href="../supplier.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="supplier_orders.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'supplier_orders.php') ? 'class="active"' : ''; ?>>Order Management</a></li>
                <li><a href="add_new_supply.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'add_new_supply.php') ? 'class="active"' : ''; ?>>Add New Supply</a></li>
                <li><a href="my_supplies.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'my_supplies.php') ? 'class="active"' : ''; ?>>My Supplies</a></li>


        <li><a href="logout.php" class="logout-btn">Logout</a></li>
    </ul>
</div>


    <header>
        <h1>Add New Supply - SmartAgri</h1>

    </header>

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

                <label for="quantity_type">Quantity Type:</label>
                <select name="quantity_type" required>
                    <option value="Per-Piece" selected>Per-Piece</option>
                    <option value="Per-Kg">Per-Kg</option>
                </select>


                <label for="price">Price:</label>
                <input type="text" name="price" required>

                <label for="supply_image">Image:</label>
                <input type="file" name="supply_image" accept="image/*">

                <input type="submit" name="add_supply" value="Add Supply">
            </form>
        </div>



</body>
</html>