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
   // $sql = "SELECT * FROM farmer_crops";
    //$result = $conn->query($sql);


// Update your SELECT query to join with products table
$query = "SELECT fc.*, p.image as product_image 
          FROM farmer_crops fc 
          LEFT JOIN products p ON fc.product_id = p.id 
          WHERE fc.status = 'available' 
          ORDER BY fc.created_at DESC";
$result = $conn->query($query);





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
     /* Body and General Styles */
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

/* Header Styles */
header {
    background: #5cb85c;
    color: white;
    padding: 10px 20px;
    text-align: center;
    border-bottom: 2px solid #4cae4c;
}

header h1 {
    margin: 0;
    color: white;
}

header a {
    color: white;
    text-decoration: none;
    font-size: 14px;
    margin-left: 15px;
}

/* Button Styles */
.button {
    background-color: #f44336;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
    text-align: center;
    margin-right: 0;
}

/* Product Grid and Cards */
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
    transition: transform 0.2s;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-5px);
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

.card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background-color: #f8f9fa;
}

/* Stylish Cart Icon Button */
.cart-icon {
    position: fixed;
    top: 20px;
    right: calc(30px + 1cm); /* Moved 5 cm to the left */
    background-color: #5cb85c;
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    border: 2px solid rgba(255,255,255,0.3);
}

.cart-icon:before {
    content: '🛒'; /* Shopping cart emoji */
    font-size: 1.2em;
}

.cart-icon:hover {
    background-color: #4cae4c;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.25);
}

.cart-count {
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 4px 8px;
    font-size: 12px;
    position: absolute;
    top: -8px;
    right: -8px;
    border: 2px solid white;
    font-weight: bold;
    min-width: 10px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    animation: pulse 1.5s infinite;
}

/* Animation for cart count */
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* Optional: Add animation when cart updates */
.cart-icon.shake {
    animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% {
        transform: translate3d(-1px, 0, 0);
    }
    20%, 80% {
        transform: translate3d(2px, 0, 0);
    }
    30%, 50%, 70% {
        transform: translate3d(-2px, 0, 0);
    }
    40%, 60% {
        transform: translate3d(2px, 0, 0);
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1001;
}

.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border-radius: 5px;
    width: 70%;
    max-width: 500px;
    position: relative;
}

.close {
    position: absolute;
    right: 10px;
    top: 5px;
    font-size: 24px;
    cursor: pointer;
}

/* Cart Sidebar Styles */
.cart-sidebar {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100%;
    background-color: white;
    box-shadow: -2px 0 5px rgba(0,0,0,0.2);
    transition: right 0.3s ease;
    z-index: 1002;
    padding: 20px;
    overflow-y: auto;
}

.cart-sidebar.active {
    right: 0;
}

.cart-sidebar .cart-item {
    border-bottom: 1px solid #ddd;
    padding: 10px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-sidebar .cart-total {
    margin-top: 20px;
    font-size: 1.2em;
    font-weight: bold;
    text-align: right;
}

/* Cart Sidebar Header */
.cart-sidebar h2 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #5cb85c;
}

/* Cart Item Styles in Sidebar */
.cart-sidebar .cart-item h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.cart-sidebar .cart-item p {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
}

/* Cart Sidebar Button Styles */
.cart-sidebar .btn-danger {
    padding: 5px 10px;
    font-size: 0.8rem;
    background-color: #d9534f;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.cart-sidebar .btn-danger:hover {
    background-color: #c9302c;
}

/* Modal Product Details */
#productDetails img {
    max-width: 100%;
    height: auto;
    margin: 10px 0;
}

#productDetails h2 {
    color: #333;
    margin-bottom: 15px;
}

#productDetails p {
    margin-bottom: 10px;
    color: #666;
}

#productDetails .form-group {
    margin-bottom: 15px;
}

#productDetails .form-control {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#productDetails .btn-primary {
    width: 100%;
    padding: 10px;
    background-color: #5cb85c;
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}

#productDetails .btn-primary:hover {
    background-color: #4cae4c;
}

/* Confirm Button Styles */
.confirm-btn {
    background-color: #5cb85c;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    display: inline-block;
    margin-top: 20px;
}

.confirm-btn:hover {
    background-color: #4cae4c;
}

/* Remove Button Styles */
.remove-btn {
    background-color: #d9534f;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 4px;
}

.remove-btn:hover {
    background-color: #c9302c;
}



/* Additional Utility Styles */
.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 15px;
}

.mb-3 {
    margin-bottom: 15px;
}

/* Form Control Styles */
.form-control {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

/* Button Primary Styles */
.btn-primary {
    background-color: #5cb85c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary:hover {
    background-color: #4cae4c;
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

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <h2>Shopping Cart</h2>
    <span class="close" onclick="toggleCart()">&times;</span>
    <div id="cartItems">
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="cart-item">
                    <div>
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p>Quantity: <?= htmlspecialchars($item['quantity']) ?> <?= htmlspecialchars($item['quantity_type']) ?></p>
                        <p>Price: TK. <?= htmlspecialchars($item['price'] * $item['quantity']) ?></p>
                    </div>
                    <button onclick="removeFromCart(<?= $item['id'] ?>)" class="btn-danger">Remove</button>
                </div>
            <?php endforeach; ?>
            <div class="cart-total">
                Total: TK. <?= array_sum(array_map(function($item) { 
                    return $item['price'] * $item['quantity']; 
                }, $_SESSION['cart'])) ?>
            </div>
        <?php else: ?>
            <p>Your cart is empty</p>
        <?php endif; ?>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="productDetails"></div>
    </div>
</div>



<div class="product-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
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

function showProductDetails(product) {
    const modal = document.getElementById('productModal');
    const details = document.getElementById('productDetails');
    

    
    details.innerHTML = `
        <h2>${product.name}</h2>
        <img src="${product.image || product.product_image}" alt="${product.name}" style="max-width: 200px;">
        <p>Price: TK. ${product.price} / ${product.quantity_type}</p>
        <p>Available: ${product.quantity} ${product.quantity_type}</p>
        <form onsubmit="return addToCart(event, ${JSON.stringify(product)})">
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" 
                       class="form-control" 
                       id="modalQuantity" 
                       min="1" 
                       max="${product.quantity}" 
                       value="1" 
                       required>
            </div>
            <button type="submit" class="btn-primary mt-3">Add to Cart</button>
        </form>
    `;
    
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

function toggleCart() {
    document.getElementById('cartSidebar').classList.toggle('active');
}

function addToCart(event, product) {
    event.preventDefault();
    const quantity = parseInt(document.getElementById('modalQuantity').value);
    
    fetch('cart/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: product.id,
            quantity: quantity,
            name: product.name,
            price: product.price,
            quantity_type: product.quantity_type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCart();
            closeModal();
            const cartIcon = document.querySelector('.cart-icon');
            cartIcon.classList.add('shake');
            setTimeout(() => {
                cartIcon.classList.remove('shake');
            }, 820);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to cart');
    });
    
    return false;
}

function removeFromCart(productId) {
    fetch('cart/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCart();
        }
    });
}

function updateCart() {
    fetch('cart/get_cart.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cartItems').innerHTML = data.html;
            document.querySelector('.cart-count').textContent = data.count;
        });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>






</body>
</html>
