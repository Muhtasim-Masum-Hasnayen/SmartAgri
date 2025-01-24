<?php
session_start();
include '../database.php'; // Include database connection

// Fetch all customers
$stmt = $conn->prepare("SELECT customer_id, users.name AS name, users.email AS email, users.phone_number AS phone_number FROM customer JOIN users ON customer.customer_id = users.user_id");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="../css/admin.css">
</head>
<body>
<header>
    <div class="admin-header">
        <h1>Admin Panel</h1>
    </div>
</header>

<!-- Sidebar -->
<div class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="admin.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../analytics/analytics.php">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_farmers.php">
                <i class="fas fa-users"></i> Manage Farmers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_suppliers.php">
                <i class="fas fa-users"></i> Manage Suppliers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_products.php">
                <i class="fas fa-users"></i> Manage Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_customers.php">
                <i class="fas fa-user-friends"></i> Manage Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

<div class="container">
    <h2>Customers List</h2>

    <!-- Display Success or Error Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="message success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Customers Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['customer_id']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['phone_number']); ?></td>
                    <td>
                        <a href="manage_customers.php?action=delete&customer_id=<?= $row['customer_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    // Prepare the delete statement
    $delete_stmt = $conn->prepare("DELETE FROM customer WHERE customer_id = ?");
    $delete_stmt->bind_param("i", $customer_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Customer deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete customer.";
    }

    // Redirect to refresh the page
    header("Location: manage_customers.php");
    exit;
}
?>

</body>
</html>
