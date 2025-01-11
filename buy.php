<?php
session_start();
include 'database.php'; // Include database connection

// Fetch all supplies
try {
    $sql = "SELECT supply_id, supply_name, quantity, quantity_type, price, image FROM supplies";
    $result = $conn->query($sql);
} catch (Exception $e) {
    die("Error fetching supplies: " . $e->getMessage());
}

// Handle add to cart functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $supply_id = $_POST['supply_id'];
    $quantity = $_POST['quantity'];

    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be greater than 0.";
    } else {
        // Add item to cart in session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if item already exists in the cart
        if (isset($_SESSION['cart'][$supply_id])) {
            $_SESSION['cart'][$supply_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$supply_id] = [
                'supply_id' => $supply_id,
                'quantity' => $quantity
            ];
        }

        $_SESSION['success'] = "Item added to cart successfully!";
    }
}

// Display messages
function displayMessage()
{
    if (!empty($_SESSION['success'])) {
        echo "<div class='success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
    }
    if (!empty($_SESSION['error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Supplies - AgriSmart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
        }
        header h1 {
            margin: 0;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #5cb85c;
            color: white;
        }
        img {
            max-width: 100px;
            height: auto;
        }
        form input[type="number"] {
            width: 60px;
            padding: 5px;
            margin-right: 10px;
        }
        form input[type="submit"] {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        form input[type="submit"]:hover {
            background: #4cae4c;
        }
    </style>
</head>
<body>
    <header>
        <h1>Buy Supplies - AgriBuzz</h1>
    </header>

    <div class="container">
        <?php displayMessage(); ?>

        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Supply Name</th>
                    <th>Quantity Available</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']); ?>" alt="Supply Image">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['supply_name']); ?></td>
                        <td><?= htmlspecialchars($row['quantity'] . ' ' . $row['quantity_type']); ?></td>
                        <td>$<?= htmlspecialchars($row['price']); ?></td>
                        <td>
                            <form method="POST" action="buy.php">
                                <input type="hidden" name="supply_id" value="<?= htmlspecialchars($row['supply_id']); ?>">
                                <input type="number" name="quantity" min="1" placeholder="Qty" required>
                                <input type="submit" name="add_to_cart" value="Add to Cart">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
