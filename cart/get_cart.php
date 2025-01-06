<?php
session_start();
include '../database.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please login to view cart");
    }

    // Get active cart items
    $query = "SELECT ci.*, fc.name, fc.quantity_type 
              FROM cart_items ci 
              JOIN cart c ON ci.cart_id = c.id 
              JOIN farmer_crops fc ON ci.product_id = fc.id 
              WHERE c.user_id = ? AND c.status = 'active'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';
    $total = 0;

    if ($result->num_rows > 0) {
        while ($item = $result->fetch_assoc()) {
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
            
            $html .= '<div class="cart-item">';
            $html .= '<div>';
            $html .= '<h4>' . htmlspecialchars($item['name']) . '</h4>';
            $html .= '<p>Quantity: ' . htmlspecialchars($item['quantity']) . ' ' . 
                    htmlspecialchars($item['quantity_type']) . '</p>';
            $html .= '<p>Price: TK. ' . htmlspecialchars($subtotal) . '</p>';
            $html .= '</div>';
            $html .= '<button onclick="removeFromCart(' . $item['id'] . 
                    ')" class="btn-danger">Remove</button>';
            $html .= '</div>';
        }
        
        $html .= '<div class="cart-total">Total: TK. ' . $total . '</div>';
    } else {
        $html = '<p>Your cart is empty</p>';
    }

    echo json_encode([
        'html' => $html,
        'count' => $result->num_rows
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
