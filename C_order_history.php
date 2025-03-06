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

// Fetch top-selling products
$query = "
    SELECT
        products.id AS product_id,
        products.name AS product_name,
        products.price AS product_price,
        products.image AS product_image,
        SUM(orders.quantity) AS total_sold
    FROM
        orders
    JOIN
        products ON orders.product_id = products.id
    GROUP BY
        products.id
    ORDER BY
        total_sold DESC
    LIMIT 10
";

$result = $conn->query($query);

// Initialize an array to store the results
$topSellingProducts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topSellingProducts[] = $row;
    }
}






?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Customer Dashboard - SmartAgri</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">




</head>
    <style>
/* General Styles */
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f1f1f1;
    color: #333;
    overflow-x: hidden;
    transition: background-color 0.3s ease;
}

h1, h2 {
    text-align: center;
    color: #000000;
    margin: 10;
    font-weight: 700;
    letter-spacing: 5px;
    text-transform: uppercase;
}

/* Header Styles */
header {
    background: linear-gradient(135deg, #5cb85c, #4cae4c, #2d8b2e);
    color: white;
    padding: 20px 30px;
    text-align: center;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    border-bottom: 3px solid #3d8f3d;
    position: sticky;
    top: 0;
    z-index: 999;
    transition: background 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

header h1 {
    margin: 0;
    font-size: 2.5rem;
    letter-spacing: 3px;
    transition: transform 0.3s ease;
}

/* Header Link Styles */
header a {
    color: #fff;
    text-decoration: none;
    font-size: 1.1rem;
    margin-left: 25px;
    transition: color 0.4s ease, transform 0.3s ease;
    letter-spacing: 1px;
}

header a:hover {
    color: #f1f1f1;
    transform: translateY(-3px);
}

/* Animations */
@keyframes headerAnimation {
    0% {
        transform: translateY(-30px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Apply animation to header */
header {
    animation: headerAnimation 0.8s ease-out;
}

header h1 {
    animation: headerAnimation 1.2s ease-out;
}

/* Hover Effects */
header:hover {
    background: linear-gradient(135deg, #4cae4c, #3d8f3d, #2d8b2e);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
}

header a:hover {
    color: #d1f9d1;
    transform: translateY(-5px);
}


/* Form Labels */
form label {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Form Inputs */
form input, form select, .form-control {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 2px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 1rem;
    background: linear-gradient(to bottom, #f9f9f9, #ffffff);
    transition: all 0.4s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

form input:focus, form select:focus, .form-control:focus {
    border-color: #6ab04c;
    outline: none;
    background: linear-gradient(to bottom, #eafaf1, #ffffff);
    transform: scale(1.02);
}

/* Submit Button */
.btn-primary {
    background: linear-gradient(135deg, #6ab04c, #48c78e);
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    text-transform: uppercase;
    transition: background 0.4s, transform 0.2s, box-shadow 0.3s;
    display: block;
    width: 100%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #48c78e, #6ab04c);
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.25);
}

.btn-primary:active {
    transform: translateY(1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

/* Alerts */
.alert {
    padding: 15px;
    margin: 20px auto;
    border-radius: 12px;
    max-width: 600px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.6s ease-out, glow 2s infinite ease-in-out;
    position: relative;
    overflow: hidden;
}

.alert-success {
    background: linear-gradient(135deg, #dff9fb, #6ab04c);
    color: #155724;
    border: none;
}

.alert-danger {
    background: linear-gradient(135deg, #fab1a0, #ff7675);
    color: #fff;
    border: none;
}

/* Alert Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
    }
    50% {
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
    }
}


    /* Cart Icon */
    .cart-icon {
        position: fixed;
        top: 20px;
        right: 30px;
        background: linear-gradient(135deg, #5cb85c, #4cae4c);
        background-size: 200% 200%; /* For gradient animation */
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
        animation: pulse 1.5s infinite ease-in-out, gradientShift 3s infinite ease-in-out; /* Adding gradient animation */
    }

    .cart-icon:before {
        content: '🛒';
        font-size: 2 em;
        animation: bounce 1.5s infinite; /* Bouncing icon animation */
    }

    /* Hover Effect */
    .cart-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }

    /* Pulsing Animation */
    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
    }

    /* Bouncing Animation */
    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    /* Gradient Shift Animation */
    @keyframes gradientShift {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }


    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
        padding: 20px;
        max-width: 1200px;
        margin: 100px auto;
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

    .top-selling-products {
        display: flex;
        margin-left: 280px;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }
    .product-card-item {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        width: calc(25% - 20px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .product-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
    }
    .add-to-cart-button {
        margin-top: 10px;
        padding: 8px 15px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .add-to-cart-button:hover {
        background-color: #218838;
    }


/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8); /* Darker overlay for a sleek look */
    z-index: 1000;
    backdrop-filter: blur(8px); /* Adds a blur effect for modern aesthetics */
}

/* Modal Content */
.modal-content {
    background: linear-gradient(135deg, #ff7eb3, #ff758c, #ff6a64); /* Gradient background for the modal */
    margin: 10% auto;
    padding: 25px;
    width: 90%;
    max-width: 500px;
    border-radius: 20px; /* Softer corners for a modern look */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); /* Deeper shadow for emphasis */
    animation: fadeIn 0.4s ease-out;
    position: relative; /* For positioning the close button */
    color: #fff; /* White text for contrast */
}

/* Modal Header */
.modal-content h2 {
    margin: 0 0 15px;
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff; /* White text to match gradient */
    text-align: center;
    text-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); /* Subtle text shadow for depth */
}

/* Close Button */
.close {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 1.8rem;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #ff6a64, #ff758c); /* Matching gradient */
    border: none;
    border-radius: 50%;
    padding: 5px 12px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.close:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #ff758c, #ff7eb3); /* Reversed gradient for hover */
    box-shadow: 0 5px 12px rgba(0, 0, 0, 0.4);
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Cart Sidebar */
.cart-sidebar {
    position: fixed;
    top: 0;
    right: -500px;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, #6a11cb, #2575fc); /* Gradient background */
    box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease;
    z-index: 1000;
    padding: 20px;
    color: #fff; /* Ensure text is visible on gradient */
}

.cart-sidebar.active {
    right: 0;
}

.cart-sidebar h2 {
    margin: 0;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.7); /* Semi-transparent white border */
    font-size: 1.5rem;
    color: #f1f1f1; /* Slightly lighter text color for contrast */
}

.cart-sidebar .cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1); /* Semi-transparent background for cart items */
    border-radius: 8px;
    color: #f1f1f1;
}

.cart-sidebar .cart-item:hover {
    background: rgba(255, 255, 255, 0.2); /* Subtle hover effect */
}

.cart-sidebar .cart-total {
    font-size: 1.3rem;
    text-align: right;
    margin-top: 20px;
    font-weight: bold;
    color: #ffffff;
}


    .order-history {
        max-width: 800px;
        margin: 20px auto;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1A73E8, #4285F4); /* Premium gradient */
        padding: 25px;
        border-radius: 15px; /* More pronounced rounding for modern aesthetics */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); /* Enhanced shadow for premium depth */
        color: #FFFFFF; /* White text for contrast on gradient */
        text-align: center; /* Center-align for uniformity */
    }

    .order-history h2 {
        font-size: 24px;
        margin-bottom: 20px;
        font-weight: 700;
        color: #FFD700; /* Gold accent for titles */
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 20px;
        background: #FFFFFF; /* Clean white for individual items */
        border: 1px solid #E0E0E0; /* Neutral border */
        border-radius: 12px; /* Smooth edges for elegance */
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); /* Soft shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth hover effect */
    }

    .order-item:hover {
        transform: translateY(-5px); /* Lifted effect */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
    }

    .order-details {
        max-width: 70%;
        text-align: left;
    }

    .order-item h4 {
        margin: 0 0 10px;
        font-size: 20px;
        font-weight: 600;
        color: #2C2C2C; /* Dark text for readability */
    }

    .order-item p {
        margin: 5px 0;
        font-size: 14px;
        color: #555; /* Neutral gray */
    }

    .order-image {
        max-width: 100px;
        max-height: 100px;
        border-radius: 12px; /* Rounded corners for softness */
        border: 2px solid #FFD700; /* Premium gold border */
        object-fit: cover;
        margin-left: 20px;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15); /* Shadow for emphasis */
    }

    .order-item .order-actions {
        display: flex;
        gap: 10px; /* Space between buttons */
    }

    .order-item .button {
        background: linear-gradient(135deg, #FFD700, #FFC300); /* Gold gradient buttons */
        color: #2C2C2C; /* Dark text for contrast */
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .order-item .button:hover {
        background: linear-gradient(135deg, #FFC300, #FFB400); /* Darker gold on hover */
        transform: scale(1.05); /* Slight scaling effect */
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

    /* Sidebar Styling */
    .sidebar {
        position: fixed;
        top: 50;
        left: -210px; /* Sidebar is hidden off-screen initially */
        height: 100vh;
        width: 260px;
        background: linear-gradient(to bottom, #3e4e60, #4b5c6b); /* Gradient from dark blue to grey */
        padding-top: 110px;
        transition: left 0.3s ease-in-out; /* Smooth transition for showing and hiding */
    }

    /* When sidebar is hovered, it slides into view */
    .sidebar:hover {
        left: 0; /* Moves the sidebar into view */
    }
    /* Sidebar link styling: text on the left, icon on the right */
    .sidebar .nav-link {
        display: flex;
        justify-content: space-between; /* Space between text and icon */
        align-items: center; /* Vertically center text and icon */
        color: #ffffff;
        padding: 15px 20px;
        text-decoration: none;
        font-size: 16px;
        transition: background-color 0.3s ease, padding-left 0.3s ease;
    }

    .sidebar .nav-link i {
        margin-left: 10px; /* Add space between the text and the icon */
        order: 1; /* Ensures icon is after the text */
    }



    /* Active link background */
    .sidebar .nav-link.active {
        background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for active state */
        color: #ffffff;
    }



    /* Main Content */
    .main-content {
        margin-left: 100;
        padding: 20px;
        background-color: #ffffff;
        transition: margin-left 0.3s ease-in-out;
    }

    /* Sidebar hover triggers main content to shift */
    .sidebar:hover + .main-content {
        margin-left: 260px; /* Shifts main content to make space for sidebar */
    }

    </style>
<body>


<!-- Sidebar -->
 <div class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="customer.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ana.php">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="C_market.php">
                    <i class="fas fa-users"></i> Market
                </a>
            </li>
            <li class="nav-item">
                            <a class="nav-link" href="C_top_selling_products.php">
                                <i class="fas fa-chart-bar"></i> Top Selling Products
                            </a>
                        </li>
                        <li class="nav-item">
                                                    <a class="nav-link" href="C_order_history.php">
                                                        <i class="fas fa-history"></i> Order History
                                                    </a>
                                                </li>
            <li class="nav-item">
                <a class="nav-link" href="C_purchase_history.php">
                    <i class="fas fa-history"></i> Purchase History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customer_profile.php">
                    <i class="fas fa-user"></i> customer Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
<header>
        <h1>Customer Order History - SmartAgri </h1>
        <a href="logout.php" class="button">Logout</a>
    </header>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>







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
