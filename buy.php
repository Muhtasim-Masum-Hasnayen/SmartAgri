<?php
session_start();
include 'database.php'; // Include database connection





// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Function to get cart total
function getCartTotal($conn) {
    $total = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $supply_id = $item['supply_id'];
            $stmt = $conn->prepare("SELECT price FROM supplies WHERE supply_id = ?");
            $stmt->bind_param("i", $supply_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $total += $row['price'] * $item['quantity'];
            }
        }
    }
    return $total;
}





// In your PHP cart operations, update the count immediately after each operation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $supply_id = $_POST['supply_id'];
        $new_quantity = $_POST['quantity'];
        
        if ($new_quantity > 0) {
            $_SESSION['cart'][$supply_id]['quantity'] = $new_quantity;
            echo "<script>updateCartCount(" . count($_SESSION['cart']) . ");</script>";
        }
    } elseif (isset($_POST['remove_item'])) {
        $supply_id = $_POST['supply_id'];
        unset($_SESSION['cart'][$supply_id]);
        echo "<script>updateCartCount(" . count($_SESSION['cart']) . ");</script>";
    } elseif (isset($_POST['confirm_order'])) {
        $_SESSION['cart'] = [];
        $_SESSION['success'] = "Order confirmed successfully!";
        echo "<script>updateCartCount(0);</script>";
    } elseif (isset($_POST['add_to_cart'])) {
        // Your existing add to cart code...
        echo "<script>updateCartCount(" . count($_SESSION['cart']) . ");</script>";
    }
}





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

    <link rel="stylesheet" type="text/css" href="css/buy.css">

   
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
                        <td>
                            <?= htmlspecialchars($row['quantity']) . ' ' . ($row['quantity_type'] == 'Per-Piece' ? 'Pieces' : ($row['quantity_type'] == 'Per-Kg' ? 'KG' : $row['quantity_type'])); ?>
                        </td>

                        <td>TK.<?= htmlspecialchars($row['price']); ?></td>
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


<!-- Cart Button -->
<button class="cart-button" onclick="toggleCart()">
    🛒 Cart 
    <span class="cart-count">
        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
    </span>
</button>

<!-- Cart Container -->
<div class="cart-container" id="cartContainer">
    <button class="close-cart" onclick="toggleCart()">×</button>
    <h2>Shopping Cart</h2>
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <?php foreach ($_SESSION['cart'] as $supply_id => $item): 
            $stmt = $conn->prepare("SELECT supply_name, price FROM supplies WHERE supply_id = ?");
            $stmt->bind_param("i", $supply_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $supply = $result->fetch_assoc();
        ?>
            <div class="cart-item">
                <h4><?= htmlspecialchars($supply['supply_name']) ?></h4>
                <p>Price: $<?= htmlspecialchars($supply['price']) ?></p>
                <div class="cart-controls">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="supply_id" value="<?= $supply_id ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width: 60px;">
                        <input type="submit" name="update_quantity" value="Update" class="btn">
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="supply_id" value="<?= $supply_id ?>">
                        <input type="submit" name="remove_item" value="Remove" class="remove-item">
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="cart-total">
            Total: Tk.<?= number_format(getCartTotal($conn), 2) ?>
        </div>
        
        <form method="POST">
            <input type="submit" name="confirm_order" value="Confirm Order" class="confirm-order">
        </form>
    <?php else: ?>
        <p>Your cart is empty</p>
    <?php endif; ?>
</div>




<!-- Add this JavaScript at the bottom of your file -->
<script>
function toggleCart() {
    const cartContainer = document.getElementById('cartContainer');
    cartContainer.classList.toggle('active');
}

// Close cart when clicking outside
document.addEventListener('click', function(event) {
    const cartContainer = document.getElementById('cartContainer');
    const cartButton = document.querySelector('.cart-button');
    
    if (!cartContainer.contains(event.target) && 
        !cartButton.contains(event.target) && 
        cartContainer.classList.contains('active')) {
        cartContainer.classList.remove('active');
    }
});

// Prevent closing when clicking inside cart
document.querySelector('.cart-container').addEventListener('click', function(event) {
    event.stopPropagation();
});

// Update cart count directly
function updateCartCount(count) {
    const cartCount = document.getElementById('cartCount');
    cartCount.textContent = count;
}
</script>






</body>
</html>
