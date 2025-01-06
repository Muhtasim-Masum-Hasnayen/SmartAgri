<?php
session_start();
include '../database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please login to add items to cart");
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    // Start transaction
    $conn->begin_transaction();

    // Get or create active cart for user
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND status = 'active'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        $cart_id = $cart['id'];
    } else {
        // Create new cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $cart_id = $conn->insert_id;
    }

    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cart_id, $data['product_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing item
        $item = $result->fetch_assoc();
        $new_quantity = $item['quantity'] + $data['quantity'];
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ddi", $new_quantity, $data['price'], $item['id']);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidd", $cart_id, $data['product_id'], $data['quantity'], $data['price']);
    }
    
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();

    // Get cart count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart',
        'cartCount' => $count
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
