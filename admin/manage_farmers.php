<?php
session_start();
include '../database.php'; // Include database connection

// Fetch all farmers
$stmt = $conn->prepare("SELECT farmer_id, users.name as name, users.email as email, users.phone_number as phone_number FROM farmer join users where farmer.farmer_id=users.user_id");
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farmers - Admin Panel</title>
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
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i> গ্রাহকদের পরিচালনা করুন
                </a>
            </li>
                        <li class="nav-item">
                              <a class="nav-link" href="manage_products.php">
                                  <i class="fas fa-users"></i> পণ্য পরিচালনা করুন
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
    <h2 style="text-align: center;">কৃষকদের তালিকা</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th style="text-align: center;">কৃষক আইডি</th>
                            <th style="text-align: center;">নাম</th>
                            <th style="text-align: center;">ইমেইল</th>
                            <th style="text-align: center;">ফোন</th>
                            <th style="text-align: center;">মুছে ফেলুন</th>
                
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['farmer_id']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['phone_number']); ?></td>
                    <td>
                        <a href="delete_farmer.php?farmer_id=<?= $row['farmer_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this farmer?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
