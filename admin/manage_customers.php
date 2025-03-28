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
        <h1>অ্যাডমিন প্যানেল</h1>
    </div>
</header>
<div class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="fas fa-home"></i> ড্যাশবোর্ড
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../analytics/analytics.php">
                    <i class="fas fa-chart-bar"></i> বিশ্লেষণ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./performance.php">
                    <i class="fas fa-chart-bar"></i>কর্মক্ষমতা
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_farmers.php">
                    <i class="fas fa-users"></i> কৃষকদের পরিচালনা করুন
                </a>
            </li>
            <li class="nav-item">
                            <a class="nav-link" href="manage_suppliers.php">
                                <i class="fas fa-users"></i> সরবরাহকারীদের পরিচালনা করুন
                            </a>
                        </li>
                        <li class="nav-item">
                                                    <a class="nav-link" href="manage_products.php">
                                                        <i class="fas fa-users"></i> পণ্য পরিচালনা করুন
                                                    </a>
                                                </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i>  গ্রাহকদের পরিচালনা করুন
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> লগআউট
                </a>
            </li>
        </ul>
    </div>

<div class="container">
    <h2 style="text-align: center;">গ্রাহক তালিকা
    </h2>

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
                <th style="text-align: center;">গ্রাহক আইডি</th>
                <th style="text-align: center;">নাম</th>
                <th style="text-align: center;">ইমেইল</th>
                <th style="text-align: center;">ফোন</th>
                <th style="text-align: center;">মুছে ফেলুন</th>
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
