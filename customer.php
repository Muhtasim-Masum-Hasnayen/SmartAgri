<?php
session_start();
include 'database.php';

try {
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
        header("Location: login.php");
        exit();
    }

  // Fetch available products - this should be at the start of your try block
  $productStmt = $conn->prepare("
  SELECT fc.*, fc.farmer_id,p.image as product_image 
  FROM farmer_crops fc 
  LEFT JOIN products p ON fc.product_id = p.id
  WHERE fc.quantity > 0
");

if (!$productStmt->execute()) {
    throw new Exception("Error fetching products: " . $conn->error);
}

$products = $productStmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $userId = $_SESSION['user_id'];
    
    try {
        // Fetch cart items grouped by farmer
        $stmt = $conn->prepare("
            SELECT c.*, fc.name as crop_name, fc.farmer_id, fc.price,
                   u.name as customer_name
            FROM cart c
            JOIN farmer_crops fc ON c.product_id = fc.product_id
            JOIN users u ON c.user_id = u.user_id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $cartItems = $stmt->get_result();

        if ($cartItems->num_rows > 0) {
            $conn->begin_transaction();
            try {
                $orderQuery = $conn->prepare("
                    INSERT INTO orders (
                        farmer_id, 
                        customer_id, 
                        product_id, 
                        customer_name, 
                        crop_name, 
                        quantity, 
                        total_amount, 
                        status, 
                        order_date
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");

                $orders = [];
                while ($item = $cartItems->fetch_assoc()) {
                    $farmerId = $item['farmer_id'];
                    $orders[$farmerId][] = $item;
                }

                foreach ($orders as $farmerId => $items) {
                    foreach ($items as $item) {
                        $totalAmount = $item['price'] * $item['quantity'];
                        $orderQuery->bind_param(
                            "iiissid",
                            $farmerId,
                            $userId,
                            $item['product_id'],
                            $item['customer_name'],
                            $item['crop_name'],
                            $item['quantity'],
                            $totalAmount
                        );
                        $orderQuery->execute();
                    }
                }

                // Clear cart
                $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $clearCart->bind_param("i", $userId);
                $clearCart->execute();

                $conn->commit();
                $_SESSION['message'] = "Orders placed successfully!";
                header("Location: customer.php");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = "Error placing orders: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Your cart is empty.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing orders: " . $e->getMessage();
    }
}


  // Handle removing items from cart
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product removed from cart successfully!";
    } else {
        $_SESSION['error'] = "Error removing product from cart.";
    }
}



    // Handle adding items to cart
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $productId = $_POST['product_id'];
        $userId = $_SESSION['user_id'];
        $farmer_id=$_POST['farmer_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;




        // Check if product already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity if product exists
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $userId, $productId);
        } else {
            // Insert new cart item

            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id,farmer_id, quantity) VALUES (?, ?,?, ?)");
            $stmt->bind_param("iiii", $userId, $productId,$farmer_id, $quantity);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added to cart successfully!";
        } else {
            $_SESSION['error'] = "Error adding product to cart.";
        }
    }

   
    // Fetch cart items
    $cartStmt = $conn->prepare("
        SELECT c.*, fc.name, fc.price, fc.quantity_type, fc.image
        FROM cart c
        JOIN farmer_crops fc ON c.product_id = fc.product_id
        WHERE c.user_id = ?
    ");
    $cartStmt->bind_param("i", $_SESSION['user_id']);
    $cartStmt->execute();
    $cartItems = $cartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate cart total
    $cartTotal = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "An error occurred while processing your request.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $productId = $_POST['product_id'];
    $userId = $_SESSION['user_id'];
    $action = $_POST['update_quantity'];

    if ($action === 'increase') {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
    } elseif ($action === 'decrease') {
        $stmt = $conn->prepare("UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE user_id = ? AND product_id = ?");
    }

    $stmt->bind_param("ii", $userId, $productId);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Cart updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating cart.";
    }

    header("Location: customer.php");
    exit();
}

// Fetch search term from request
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Fetch available products with search filter
$productStmt = $conn->prepare("
    SELECT fc.*, fc.farmer_id, p.image as product_image
    FROM farmer_crops fc
    LEFT JOIN products p ON fc.product_id = p.id
    WHERE fc.quantity > 0 AND fc.name LIKE ?
");
$productStmt->bind_param("s", $searchTerm);

if (!$productStmt->execute()) {
    throw new Exception("Error fetching products: " . $conn->error);
}

$products = $productStmt->get_result();



// Fetch the customer's past orders including product images
$orderHistoryStmt = $conn->prepare("
    SELECT
        o.order_id,
        o.crop_name,
        o.quantity,
        o.total_amount,
        o.status,
        o.order_date,
        fc.price,
        fc.quantity_type,
        fc.image
    FROM orders o
    LEFT JOIN farmer_crops fc ON o.product_id = fc.product_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$orderHistoryStmt->bind_param("i", $_SESSION['user_id']);
$orderHistoryStmt->execute();
$orderHistory = $orderHistoryStmt->get_result();





?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - SmartAgri</title>

    <style>
    /* General Styles */
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9f9f9;
        color: #333;
    }

    h1, h2 {
        text-align: center;
        color: #2c3e50;
        margin: 0;
        font-weight: 600;
    }

    /* Header Styles */
    header {
        background: linear-gradient(135deg, #5cb85c, #4cae4c);
        color: white;
        padding: 20px 15px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #3d8f3d;
    }

    header h1 {
        margin: 0;
        font-size: 2rem;
    }

    header a {
        color: #fff;
        text-decoration: none;
        font-size: 1rem;
        margin-left: 20px;
        transition: color 0.3s;
    }

    header a:hover {
        color: #f1f1f1;
    }

    /* Forms */
    form label {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
        color: #34495e;
    }

    form input, form select, .form-control {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 1rem;
        background-color: #fff;
        transition: border-color 0.3s ease-in-out;
    }

    form input:focus, form select:focus, .form-control:focus {
        border-color: #5cb85c;
        outline: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #28a745, #218838);
        color: #fff;
        border: none;
        padding: 12px 20px;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s, transform 0.2s;
        display: block;
        width: 100%;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        transform: translateY(-3px);
    }

    /* Alerts */
    .alert {
        padding: 15px;
        margin: 20px auto;
        border-radius: 8px;
        max-width: 600px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.5s ease-out;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Cart Icon */
    .cart-icon {
        position: fixed;
        top: 20px;
        right: 30px;
        background: linear-gradient(135deg, #5cb85c, #4cae4c);
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        font-size: 1rem;
        cursor: pointer;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        z-index: 1000;
    }

    .cart-icon:before {
        content: '🛒';
        font-size: 1.5em;
    }

    .cart-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
        padding: 20px;
        max-width: 1200px;
        margin: 20px auto;
    }

    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        text-align: center;
    }

    .product-card img {
        max-width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .product-card h3 {
        margin: 15px 0 5px;
        font-size: 1.2rem;
        color: #2c3e50;
    }

    .product-card p {
        font-size: 0.9rem;
        color: #7f8c8d;
        margin: 5px 0;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
    }

    .modal-content {
        background: #fff;
        margin: 10% auto;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content h2 {
        margin: 0 0 15px;
        font-size: 1.5rem;
        color: #34495e;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.5rem;
        cursor: pointer;
        color: #555;
    }

    /* Cart Sidebar */
    .cart-sidebar {
        position: fixed;
        top: 0;
        right: -500px;
        width: 400px;
        height: 70%;
        background: #fff;
        box-shadow: -4px 0 10px rgba(0, 0, 0, 0.1);
        transition: right 0.4s ease;
        z-index: 1000;
        padding: 20px;
    }

    .cart-sidebar.active {
        right: 0;
    }

    .cart-sidebar h2 {
        margin: 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #5cb85c;
        font-size: 1.5rem;
        color: #2c3e50;
    }

    .cart-sidebar .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 10px 0;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .cart-sidebar .cart-total {
        font-size: 1.3rem;
        text-align: right;
        margin-top: 20px;
        font-weight: bold;
    }

    .order-history {
        max-width: 800px;
        margin: 20px auto;
        font-family: Arial, sans-serif;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .order-details {
        max-width: 70%;
    }

    .order-item h4 {
        margin: 0 0 10px;
        font-size: 18px;
        color: #333;
    }

    .order-item p {
        margin: 5px 0;
        color: #555;
    }

    .order-image {
        max-width: 100px;
        max-height: 100px;
        border-radius: 5px;
        border: 1px solid #ddd;
        object-fit: cover;
        margin-left: 20px;
    }


    .search-bar {
        margin: 20px 0;
        text-align: center;
    }

    .search-input {
        width: 300px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    .search-button {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .search-button:hover {
        background-color: #45a049;
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




<!-- Cart Icon -->
<div class="cart-icon" onclick="toggleCart()">
    Cart <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
</div>
<div class="search-bar">
    <form method="GET" action="customer.php">
        <input
            type="text"
            name="search"
            placeholder="Search for crops or products..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            class="search-input"
        >
        <button type="submit" class="search-button">Search</button>
    </form>
</div>


<!-- Update the cart section with confirmation -->
<div class="cart-sidebar" id="cartSidebar">
    <h2>Shopping Cart</h2>
    <span class="close" onclick="toggleCart()">&times;</span>
    <div id="cartItems">
        <?php if (!empty($cartItems)): ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
    <div>
        <h4><?= htmlspecialchars($item['name']) ?></h4>
        <p>Price: TK. <?= htmlspecialchars($item['price']) ?></p>
        <div class="quantity-controls">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <input type="hidden" name="update_quantity" value="decrease">
                <button type="submit" class="btn-decrement">-</button>
            </form>
            <span><?= htmlspecialchars($item['quantity']) ?></span>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <input type="hidden" name="update_quantity" value="increase">
                <button type="submit" class="btn-increment">+</button>
            </form>
        </div>
        <p>Total: TK. <?= htmlspecialchars($item['price'] * $item['quantity']) ?></p>
    </div>
    <form method="POST">
        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
        <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
    </form>
</div>

            <?php endforeach; ?>
            <div class="cart-total">
                Total: TK. <?= htmlspecialchars($cartTotal) ?>
            </div>
            <form method="POST" class="place-order-form" onsubmit="return confirmOrder()">
                <button type="submit" name="place_order" class="btn-primary">Place Order</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty</p>
        <?php endif; ?>
    </div>
</div>



<!-- Add message display -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

        

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="productDetails"></div>
    </div>
</div>




<div class="product-grid">
    <?php if ($products->num_rows > 0): ?>
        <?php while ($row = $products->fetch_assoc()): ?>
            <div class="product-card" onclick="showProductDetails(<?= htmlspecialchars(json_encode($row)) ?>)">
                <div class="card">
                    <?php
                    $display_image = !empty($row['image']) ? $row['image'] : $row['product_image'];
                    ?>
                    <img src="<?= htmlspecialchars($display_image); ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($row['name']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($row['name']); ?></h3>
                        <p class="card-text">Price: TK. <?= htmlspecialchars($row['price']); ?> / <?= htmlspecialchars($row['quantity_type']); ?></p>
                        <p class="card-text">Available: <?= htmlspecialchars($row['quantity']); ?> <?= htmlspecialchars($row['quantity_type']); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center;">No products available at the moment.</p>
    <?php endif; ?>
</div>



    <script>
function toggleCart() {
    document.getElementById('cartSidebar').classList.toggle('active');
}
function confirmOrder() {
    return confirm('Are you sure you want to place this order?');
}


function showProductDetails(product) {


    const modal = document.getElementById('productModal');
    const details = document.getElementById('productDetails');


    
    details.innerHTML = `
        <h2>${product.name}</h2>
        <img src="${product.image || product.product_image}" alt="${product.name}" style="max-width: 200px;">
        <p>Price: TK. ${product.price}</p>
        <form method="POST">
            <input type="hidden" name="product_id" value="${product.product_id}">
             <input type="hidden" name="farmer_id" value="${product.farmer_id}">
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" 
                       name="quantity" 
                       min="1" 
                       value="1" 
                       required 
                       class="form-control">
            </div>
 


            <button type="submit" name="add_to_cart" class="btn-primary">Add to Cart</button>
        </form>
    `;
    
    modal.style.display = "block";
}


function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

function toggleCart() {
    document.getElementById('cartSidebar').classList.toggle('active');
}


// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        closeModal();
    }
}



// Add to your customer.js
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});

</script>


<h2 id="orderHistory">Your Order History</h2>

<?php if ($orderHistory->num_rows > 0): ?>
    <div class="order-history">
        <?php while ($order = $orderHistory->fetch_assoc()): ?>
            <div class="order-item">
                <div class="order-details">
                    <h4><?= htmlspecialchars($order['crop_name']) ?></h4>
                    <p>Quantity: <?= htmlspecialchars($order['quantity']) ?> <?= htmlspecialchars($order['quantity_type']) ?></p>
                    <p>Total Amount: TK. <?= htmlspecialchars($order['total_amount']) ?></p>
                    <p>Status: <?= htmlspecialchars(ucfirst($order['status'])) ?></p>
                    <p>Order Date: <?= htmlspecialchars(date("d-M-Y H:i:s", strtotime($order['order_date']))) ?></p>
                </div>
                <img
                    src="<?= htmlspecialchars($order['image']) ?>"
                    alt="<?= htmlspecialchars($order['crop_name']) ?>"
                    class="order-image"
                >
            </div>
            <hr>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You have not placed any orders yet.</p>
<?php endif; ?>



</body>
</html>
