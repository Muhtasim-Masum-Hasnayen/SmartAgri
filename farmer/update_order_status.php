<?php
include('../database.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
// First, get the current status of the order
$current_status_query = "SELECT status FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($current_status_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$current_status = $order['status'];


// Check if status is changing from pending to processing
if ($current_status === 'pending' && $new_status === 'Processing') {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Get order items
        $items_query = "SELECT product_id,farmer_id, quantity FROM orders WHERE order_id = ?";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        // Update quantity for each product in the order
        while ($item = $items_result->fetch_assoc()) {
            $update_products = "UPDATE farmer_crops SET quantity = quantity - ? WHERE product_id = ? AND farmer_id=?"; 
                               
            $stmt = $conn->prepare($update_products);
            $stmt->bind_param("iii", $item['quantity'], $item['product_id'],$item['farmer_id']);
            $stmt->execute();
            
            // Check if update was successful
            if ($stmt->affected_rows <= 0) {
                throw new Exception("Failed to update products quentity for product ID: " . $item['product_id']);
            }
        }



    // Update order status
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ? AND farmer_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sii", $status, $order_id, $farmer_id);
    
    $stmt->execute();


         
            // Commit transaction
            $conn->commit();
            
            // Redirect or show success message
            header("Location: order_management.php?success=1");
            exit();
        } catch (Exception $e) {
            // Rollback transaction if any error occurs
            $conn->rollback();
            $error_message = "Error updating order: " . $e->getMessage();
            // Handle the error (redirect with error message or display it)
        }


}else {
    // For other status changes, just update the status without affecting inventory
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    
    // Redirect or show success message
    header("Location: update_order_status.php?success=1");
    exit();
}

}






// Fetch order details
if (!isset($_GET['id'])) {
    header('Location: order_management.php');
    exit();
}

$order_id = $_GET['id'];
$query = "SELECT * FROM orders WHERE order_id = ? AND farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: order_management.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Update Order Status</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order #<?php echo $order['order_id']; ?></h5>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Pending" <?php echo ($order['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo ($order['status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="Shipped" <?php echo ($order['status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo ($order['status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo ($order['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <a href="order_management.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
