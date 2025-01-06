<?php
session_start();
include '../database.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please login to remove items");
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    // Remove item from cart
    $stmt = $conn->prepare("DELETE ci FROM cart_items ci 
                           JOIN cart c ON ci.cart_id = c.id 
                           WHERE ci.id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $data['product_id'], $_SESSION['user_id']);
    $stmt->execute();

    // Get updated cart count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart_items ci 
                           JOIN cart c ON ci.cart_id = c.id 
                           WHERE c.user_id = ? AND c.status = 'active'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];

    echo json_encode([
        'success' => true,
        'cartCount' => $count
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
