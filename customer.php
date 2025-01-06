<?php
session_start();
include 'database.php'; // Include the database connection


try {
    // Check if the user is logged in and has the role 'Customer'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
        throw new Exception("Unauthorized access. Please log in as a Customer.");
    }

    // Initialize the cart in the session if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle adding items to the cart
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $productId = $_POST['product_id'];

        // Fetch product details
        $stmt = $conn->prepare("SELECT * FROM farmer_crops WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            // Add product to the cart
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price']
            ];
        }
    }

    // Handle removing items from the cart
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
        $productId = $_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
    }

    // Fetch products from the database
    $sql = "SELECT * FROM farmer_crops";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching products: " . $conn->error);
    }
} catch (Exception $e) {
    // Log the error and display a friendly message
    error_log($e->getMessage());
    echo "<p style='color: red; text-align: center;'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - SmartAgri</title>

    <style>
     .button {
                background-color: #f44336;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 4px;
                display: inline-block;
                text-align: center;
                margin-right:0;
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
                        text-color: white;
                    }
                    header a {
                        color: white;
                        text-decoration: none;
                        font-size: 14px;
                        margin-left: 15px;
                    }


        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }


        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .product-card {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-card h3 {
            margin: 10px 0;
            font-size: 18px;
        }
        .product-card p {
            font-size: 16px;
            color: #555;
        }
        .add-to-cart {
            margin-top: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .add-to-cart:hover {
            background-color: #4cae4c;
        }
        .cart-container {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 8px;
        }
        .cart-container h2 {
            text-align: center;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .cart-total {
            text-align: left;
            font-size: 18px;
            font-weight: bold;
        }
        .remove-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .confirm-btn {
            background-color: #5cb85c;  /* Green color */
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            display: inline-block;
        }



        .confirm-btn {
            margin-top: 20px; /* Adjust spacing from the content above */
        }

        .confirm-btn:hover {
            background-color: #4cae4c; /* Darker green on hover */
        }

    </style>
</head>
<body>
<header>
        <h1>Customer Dashboard - SmartAgri </h1>
        <a href="logout.php" class="button">Logout</a>
    </header>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
    <h2>Available Crops</h2>

    <div class="product-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="uploads/<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                    <h3><?= htmlspecialchars($row['name']); ?></h3>
                    <p>Price: TK. <?= htmlspecialchars($row['price']); ?></p>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']); ?>">
                        <input type="submit" name="add_to_cart" value="Add to Cart" class="add-to-cart">
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">No products available at the moment.</p>
        <?php endif; ?>
    </div>

    <!-- Cart Section -->
    <div class="cart-container">
        <h2>Your Cart</h2>
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php
            $totalPrice = 0;
            foreach ($_SESSION['cart'] as $item):
                $totalPrice += $item['price'];
            ?>
                <div class="cart-item">
                    <span><?= htmlspecialchars($item['name']); ?> - TK. <?= htmlspecialchars($item['price']); ?></span>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']); ?>">
                        <input type="submit" name="remove_from_cart" value="Remove" class="remove-btn">
                    </form>
                </div>
            <?php endforeach; ?>
            <div class="cart-total">Total: TK. <?= htmlspecialchars($totalPrice); ?></div>
            <form method="POST">
                <button type="submit" name="confirm_order" class="confirm-btn">Confirm Order</button>

            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>
