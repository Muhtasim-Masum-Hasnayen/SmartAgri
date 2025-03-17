<?php
session_start();
include 'database.php'; // Include database connection

function validateQuantity($quantity) {
    return filter_var($quantity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
}

function fetchSupplyDetails($conn, $supply_id) {
    $stmt = $conn->prepare("
        SELECT supply_name, price, quantity AS available_quantity, supplier_id
        FROM supplies WHERE supply_id = ?
    ");
    $stmt->bind_param("i", $supply_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateStock($conn, $supply_id, $quantity) {
    $stmt = $conn->prepare("UPDATE supplies SET quantity = quantity - ? WHERE supply_id = ?");
    $stmt->bind_param("ii", $quantity, $supply_id);
    return $stmt->execute();
}

function displayMessage() {
    if (!empty($_SESSION['success'])) {
        echo "<div class='success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
    }
    if (!empty($_SESSION['error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
}

function getCartTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

function logError($message) {
    error_log($message);
    $_SESSION['error'] = "An unexpected error occurred.";
}

// Handle Cart Actions
function handleCartActions($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_to_cart'])) {
            $supply_id = $_POST['supply_id'];
            $quantity = validateQuantity($_POST['quantity']);

            if (!$quantity) {
                $_SESSION['error'] = "Invalid quantity.";
                return;
            }

            $product = fetchSupplyDetails($conn, $supply_id);
            if ($product && $quantity <= $product['available_quantity']) {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                $_SESSION['cart'][$supply_id] = [
                    'supply_id' => $supply_id,
                    'supply_name' => $product['supply_name'],
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'supplier_id' => $product['supplier_id'],
                    'subtotal' => $quantity * $product['price']
                ];

                $_SESSION['success'] = "Item added to cart successfully!";
            } else {
                $_SESSION['error'] = "Product not available or insufficient stock.";
            }
        }

        if (isset($_POST['update_quantity'])) {
            $supply_id = $_POST['supply_id'];
            $quantity = validateQuantity($_POST['quantity']);
            if ($quantity && isset($_SESSION['cart'][$supply_id])) {
                $_SESSION['cart'][$supply_id]['quantity'] = $quantity;
                $_SESSION['cart'][$supply_id]['subtotal'] = $quantity * $_SESSION['cart'][$supply_id]['price'];
                $_SESSION['success'] = "Quantity updated.";
            } else {
                $_SESSION['error'] = "Invalid quantity.";
            }
        }

        if (isset($_POST['remove_item'])) {
            $supply_id = $_POST['supply_id'];
            unset($_SESSION['cart'][$supply_id]);
            $_SESSION['success'] = "Item removed from cart.";
        }
    }
}

handleCartActions($conn);

// Confirm Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $farmer_id = $_SESSION['user_id'];

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Your cart is empty.";
        return;
    }

   
        $conn->begin_transaction();

foreach ($_SESSION['cart'] as $item) {
    $total_price = $item['price'] * $item['quantity'];
    $sale_date = date("Y-m-d H:i:s"); // Get the current timestamp
    $status = 'Pending'; // You can customize this value based on your logic





    $stmt = $conn->prepare("
        INSERT INTO supplier_sales (supplier_id, farmer_id, product_id, quantity, price, total_price, sale_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo "Prepare failed: " . $conn->error;
        $conn->rollback();
        exit;
    }

    $stmt->bind_param(
        "iidddsss",
        $item['supplier_id'],  // Supplier ID
        $farmer_id,           // Farmer ID
        $item['supply_id'],   // Product ID (adjust the name if necessary)
        $item['quantity'],    // Quantity
        $item['price'],       // Unit price
        $total_price,         // Total price
        $sale_date,           // Sale date (current timestamp)
        $status               // Sale status
    );

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo "Execute failed: " . $stmt->error;
        $conn->rollback();
        exit;
    }
}

$conn->commit();
        $_SESSION['cart'] = [];
        $_SESSION['success'] = "Order confirmed successfully!";
   
}

// Fetch Supplies
try {
    $stmt = $conn->prepare("SELECT supply_id, supplier_id, supply_name, quantity, quantity_type, price, image FROM supplies");
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    die("Error fetching supplies: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Supplies - AgriBuzz</title>
    <link rel="stylesheet" type="text/css" href="css/buy.css">


    <style>::after

header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-left: 20px;
        }

        .sidebar {
            width: 250px;
            background-color: #1f2937;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .sidebar a {
            color: #b0bec5;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: #4b5563;
            color: white;
        }
</style>

</head>
<body>
<header>
    <h1>সরবরাহ থেকে কিনুন</h1>
</header>

<div class="sidebar">
        <h2>Navigation</h2>
        <a href="crop_management.php"><i class="fas fa-seedling"></i> Crop/Product Management</a>
        <a href="Buy.php"><i class="fas fa-shopping-cart"></i> Buy from Suppliers</a>
        <a href="addNewProduct.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="farmer/order_management.php"><i class="fas fa-clipboard-list"></i> Order Management</a>
        <a href="farmer/inventory_management.php"><i class="fas fa-boxes"></i> Inventory Management</a>
        <a href="farmer/financial_overview.php"><i class="fas fa-wallet"></i> Financial Overview</a>
        <a href="analytics_report.php"><i class="fas fa-chart-bar"></i> Analytics and Reports</a>
        
    </div>



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
                    <td><?= htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['quantity_type']); ?></td>
                    <td>TK.<?= htmlspecialchars($row['price']); ?></td>
                    <td>
                        <form method="POST" action="buy.php">
                            <input type="hidden" name="supply_id" value="<?= htmlspecialchars($row['supply_id']); ?>">
                            <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($row['supplier_id']); ?>">
                            <input type="number" name="quantity" min="1" required>
                            <input type="hidden" name="price" value="<?= htmlspecialchars($row['price']); ?>">
                            <input type="submit" name="add_to_cart" value="Add to Cart">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<button class="cart-button" onclick="toggleCart()">🛒 Cart <span class="cart-count"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></button>

<div class="cart-container" id="cartContainer">
    <button class="close-cart" onclick="toggleCart()">×</button>
    <h2>Shopping Cart</h2>
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <?php foreach ($_SESSION['cart'] as $item): ?>
            <div class="cart-item">
                <h4><?= htmlspecialchars($item['supply_name']); ?></h4>
                <p>Price: TK.<?= htmlspecialchars($item['price']); ?></p>
                <form method="POST">
                    <input type="hidden" name="supply_id" value="<?= $item['supply_id']; ?>">
                    <input type="hidden" name="supplier_id" value="<?= $item['supplier_id']; ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                    <input type="submit" name="update_quantity" value="Update">
                    <input type="submit" name="remove_item" value="Remove">
                </form>
            </div>
        <?php endforeach; ?>
        <div class="cart-total">Total: TK.<?= number_format(getCartTotal($_SESSION['cart']), 2); ?></div>
        <form method="POST">
            <input type="submit" name="confirm_order" value="Confirm Order">
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

<script>
function toggleCart() {
    document.getElementById('cartContainer').classList.toggle('active');
}
</script>
</body>
</html>
