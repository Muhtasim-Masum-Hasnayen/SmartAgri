<?php
session_start();
include '../database.php'; // Include database connection

// Fetch all suppliers
$stmt = $conn->prepare("SELECT supplier_id, name, email, phone_number, address FROM supplier join users s where supplier.supplier_id=s.user_id");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers - Admin Panel</title>
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
                <a class="nav-link" href="./performance.php">
                    <i class="fas fa-chart-bar"></i>performence
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
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i> Manage Customers
                </a>
            </li>
                        <li class="nav-item">
                              <a class="nav-link" href="manage_products.php">
                                  <i class="fas fa-users"></i> Manage Products
                                           </a>
                                                </li>
            

            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

<div class="container">
    <h2>Suppliers List</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Supplier ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['supplier_id']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['phone_number']); ?></td>
                    <td><?= htmlspecialchars($row['address']); ?></td>
                    <td>
                        <a href="delete_supplier.php?supplier_id=<?= $row['supplier_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
