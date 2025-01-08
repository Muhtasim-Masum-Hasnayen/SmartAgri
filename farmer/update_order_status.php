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
    $status = $_POST['status'];
    
    // Update order status
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ? AND farmer_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sii", $status, $order_id, $farmer_id);
    
    if ($stmt->execute()) {
        header("Location: view_order.php?id=" . $order_id);
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
                <form method="POST" action="update_order_status.php">
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
                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
