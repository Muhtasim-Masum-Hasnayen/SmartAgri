<?php
session_start();
include 'database.php'; // Include the database connection

try {
    // Check if the user is logged in and has the role 'Customer'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
        throw new Exception("Unauthorized access. Please log in as a Customer.");
    }

    // Fetch products from the database
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching products: " . $conn->error);
    }
} catch (Exception $e) {
    // Log the error and redirect to an error page or show a friendly error message
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
    <title>Customer Dashboard - AgriBuzz</title>
    <style>
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
    </style>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
    <h2>Available Crops</h2>

    <div class="product-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="uploads/<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                    <h3><?= htmlspecialchars($row['name']); ?></h3>
                    <p>Price: ₹<?= htmlspecialchars($row['price']); ?></p>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']); ?>">
                        <input type="submit" value="Add to Cart" class="add-to-cart">
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">No products available at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
