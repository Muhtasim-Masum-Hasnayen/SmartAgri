<?php
session_start();
include 'database.php';

    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
        header("Location: login.php");
        exit();
    }
    $customer_id=$_SESSION['user_id'];


// Cart Count
$stmt = $conn->prepare("SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['cart_count'];

// Recommendations
$stmt = $conn->prepare("
    SELECT DISTINCT fc.product_id, fc.name AS product_name, u.name AS farmer_name, fc.price
FROM farmer_crops fc
INNER JOIN farmer f ON fc.farmer_id = f.farmer_id
INNER JOIN users u ON f.farmer_id = u.user_id
LEFT JOIN orders o ON fc.product_id = o.product_id AND o.customer_id = ?
LEFT JOIN product_reviews pr ON fc.product_id = pr.product_id AND pr.customer_id = ?
WHERE fc.quantity > 0
  AND fc.product_id NOT IN (
      SELECT DISTINCT product_id
      FROM orders
      WHERE customer_id = ?
  )
  AND fc.product_id NOT IN (
      SELECT DISTINCT product_id
      FROM product_reviews
      WHERE customer_id = ?
  )
ORDER BY RAND()
LIMIT 5;

");
$stmt->bind_param("iiii", $customer_id, $customer_id,$customer_id,$customer_id);
$stmt->execute();
$recommended_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pending Reviews
$stmt = $conn->prepare("
    SELECT DISTINCT o.product_id, fc.name AS product_name, u.name AS farmer_name 
    FROM orders o
    INNER JOIN farmer_crops fc ON o.product_id = fc.product_id
    JOIN users u ON fc.farmer_id = u.user_id
    INNER JOIN farmer f ON fc.farmer_id = f.farmer_id
    LEFT JOIN product_reviews pr ON o.product_id = pr.product_id AND o.customer_id = pr.customer_id
    WHERE o.customer_id = ? AND o.status = 'Delivered' AND pr.id IS NULL
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$pending_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);






?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - SmartAgri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .header-right .customer-name {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .header-right .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .header-right .logout-btn:hover {
            background-color: #c82333;
        }

        .sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            height: calc(100% - 80px);
            width: 250px;
            background-color: #333;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav-link {
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #28a745;
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .content {
            margin-left: 270px;
            margin-top: 20px;
            padding: 20px;
        }

        h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #007bff;
        }

        .cart-summary, .pending-reviews, .product-grid {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        .cart-summary h3, .pending-reviews h3 {
            margin-bottom: 10px;
            font-size: 1.3em;
            color: #28a745;
        }

        .cart-summary p, .pending-reviews p {
            margin: 5px 0;
            color: #555;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fa;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-card h4 {
            font-size: 1.1em;
            margin-bottom: 10px;
            color: #333;
        }

        .product-card p {
            font-size: 0.9em;
            margin-bottom: 10px;
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 0.9em;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul li p {
            margin: 0;
            font-size: 0.9em;
            color: #333;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 210px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Customer Dashboard - SmartAgri</h1>
    <div class="header-right">
        <span class="customer-name">Welcome, John Doe</span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="sidebar">
    <ul>
        <li><a href="customer.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="C_market.php" class="nav-link"><i class="fas fa-store"></i> Market</a></li>
        <li><a href="C_review.php" class="nav-link"><i class="fas fa-star"></i> Review</a></li>
        <li><a href="C_top_selling_products.php" class="nav-link"><i class="fas fa-chart-line"></i> Top Selling</a></li>
        <li><a href="C_order_history.php" class="nav-link"><i class="fas fa-history"></i> Order History</a></li>
        <li><a href="C_purchase_history.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Purchase History</a></li>
        <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <div class="cart-summary">
        <h3>Your Cart</h3>
        <p>You have <strong><?= $cart_count; ?></strong> items in your cart.</p>
        <a href="C_market.php?action=view_cart" class="btn">View Cart</a>

    </div>

    <h3>Products You May Like</h3>
    <div class="product-grid">
        <?php if (empty($recommended_products)): ?>
            <p>No recommendations available. Keep exploring!</p>
        <?php else: ?>
            <?php foreach ($recommended_products as $product): ?>
                <div class="product-card">
                    <?php
                        // Check and correct image path
                        if (!empty($product['image']) && file_exists($product['image'])) {
                            $image_path = $product['uploads/image'];
                        } else {
                            $image_path = 'default.jpg'; // Use a placeholder if image is missing
                        }
                    ?>
                    <img src="<?= htmlspecialchars($image_path); ?>"
                         alt="<?= htmlspecialchars($product['product_name']); ?>"
                         style="max-width: 100%; height: auto;">

                    <h4><?= htmlspecialchars($product['product_name']); ?></h4>
                    <p>By: <?= htmlspecialchars($product['farmer_name']); ?></p>
                    <p>Price: $<?= htmlspecialchars($product['price']); ?></p>
                    <a href="C_market.php?product_id=<?= $product['product_id'] ?>" class="btn">View Product</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>




    <div class="pending-reviews">
        <h3>Pending Reviews</h3>
        <?php if (empty($pending_reviews)): ?>
            <p>No pending reviews. Keep shopping!</p>
        <?php else: ?>
            <ul>
                <?php foreach ($pending_reviews as $review): ?>
                    <li>
                        <p><strong><?= $review['product_name']; ?></strong> by <?= $review['farmer_name']; ?></p>
                        <a href="review.php?product_id=<?= $review['product_id']; ?>" class="btn">Write a Review</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

</body>
</html>


