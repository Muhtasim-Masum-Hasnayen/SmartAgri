<?php
session_start();
include 'database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - SmartAgri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - SmartAgri</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f7f8fa;
            color: #333;
            padding-top: 70px; /* Add padding to prevent content from overlapping with the fixed header */
        
        }

        header {
    background-color: #4CAF50;
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed; /* Make the header fixed */
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000; /* Ensure it stays on top of other elements */
}



        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-left: 20px;
        }

        .sidebar {
            width: 250px;
            background-color: #1f2937;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .sidebar a {
            color: #b0bec5;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: #4b5563;
            color: white;
        }

        .dashboard-feed {
            margin-left: 270px;
            padding: 2rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0d6efd;
        }

        ./* General Card Styles */
.feed-section {
    background: linear-gradient(145deg, #ffffff, #f3f3f3);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.feed-section:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.feed-section h2 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Orders List */
.orders-list .order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: background 0.3s, transform 0.3s;
}

.orders-list .order-item:hover {
    background: #f3f3f3;
    transform: translateY(-3px);
}

.orders-list .order-info {
    flex: 1;
    color: #555;
}

.orders-list .order-info h4 {
    font-size: 1.2rem;
    color: #2c3e50;
}

.orders-list .status-pending {
    color: #e67e22;
    font-weight: bold;
}

.orders-list .status-completed {
    color: #27ae60;
    font-weight: bold;
}

.orders-list .status-cancelled {
    color: #e74c3c;
    font-weight: bold;
}

/* Alerts */
.alert-item {
    display: flex;
    align-items: center;
    background: #fffbf2;
    border: 1px solid #ffebcd;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.alert-item .alert-icon {
    font-size: 1.5rem;
    margin-right: 10px;
    color: #ffcc00;
}

/* Trends */
.trend-item {
    background: linear-gradient(135deg, #f4f4f4, #e8e8e8);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 15px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.trend-item h4 {
    font-size: 1.2rem;
    color: #4CAF50;
    margin-bottom: 10px;
}

.trend-item p {
    color: #666;
    font-size: 1rem;
}

/* Buttons (if applicable) */
.button-primary {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s;
}

.button-primary:hover {
    background-color: #45a049;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .feed-section {
        padding: 15px;
    }

    .orders-list .order-item {
        flex-direction: column;
        align-items: flex-start;
    }
}


    </style>

    <!-- Add this script in the head -->
    <script>
        // Define selectCrop in the window object to make it globally available
        window.selectCrop = function(productId, cropName) {
            console.log('SelectCrop called:', productId, cropName); // Debug line
            const productIdElement = document.getElementById('product_id');
            const selectedCropElement = document.getElementById('selected-crop');
            
            if (productIdElement && selectedCropElement) {
                productIdElement.value = productId;
                selectedCropElement.textContent = cropName;
            }
        }

        // Error handling
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.log('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError: ' + error);
            return false;
        };
    </script>
</head>
<body>
<header>
        <h1>Farmer Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
        </div>
    </header>

    <div class="sidebar">
        <h2>Navigation</h2>
        <a href="crop_management.php"><i class="fas fa-seedling"></i> Crop/Product Management</a>
        <a href="Buy.php"><i class="fas fa-shopping-cart"></i> Buy from Suppliers</a>
        <a href="addNewProduct.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="farmer/order_management.php"><i class="fas fa-clipboard-list"></i> Order Management</a>
        <a href="farmer/inventory_management.php"><i class="fas fa-boxes"></i> Inventory Management</a>
        <a href="farmer/financial_overview.php"><i class="fas fa-wallet"></i> Financial Overview</a>
        <a href="analytics_report.php"><i class="fas fa-chart-bar"></i> Analytics and Reports</a>
        
    </div>


<div class="dashboard-feed">
    <!-- Statistics Summary -->
    
    <div class="stats-cards">
        <div class="stat-card">
            <h3>Today's Sales</h3>
            <?php
            $today = date('Y-m-d');
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as total
                FROM orders 
                WHERE farmer_id = ? 
                AND DATE(order_date) = ?
                AND status = 'Delivered'
            ");
            $stmt->bind_param("is", $_SESSION['user_id'], $today);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            ?>
            <p class="stat-value">TK. <?= number_format($result['total'], 2) ?></p>
        </div>


 <!-- Monthly Sales Card -->
 <div class="stat-card">
            <h3>This Month's Sales</h3>
            <?php
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as total
                FROM orders 
                WHERE farmer_id = ? 
                AND DATE(order_date) BETWEEN ? AND ?
                AND status = 'Delivered'
            ");
            $stmt->bind_param("iss", $_SESSION['user_id'], $firstDayOfMonth, $lastDayOfMonth);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            ?>
            <p class="stat-value">TK. <?= number_format($result['total'], 2) ?></p>
        </div>








        <div class="stat-card">
            <h3>Active Listings</h3>
            <?php
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM farmer_crops 
                WHERE farmer_id = ? AND quantity > 0
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            ?>
            <p class="stat-value"><?= $result['count'] ?></p>
        </div>
   <!-- Pending Orders Card -->
   <div class="stat-card">
            <h3>Pending Orders</h3>
            <?php
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM orders 
                WHERE farmer_id = ? 
                AND status = 'Pending'
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            ?>
            <p class="stat-value"><?= $result['count'] ?></p>
        </div>
   
    </div>
 <!-- Recent Orders Section -->
 <div class="recent-orders feed-section">
        <h2>Recent Orders</h2>
        <?php
        $stmt = $conn->prepare("
            SELECT order_id, total_amount, status, order_date
                   
            FROM orders 
            WHERE farmer_id = ?
            ORDER BY order_date DESC
            LIMIT 5
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $recent_orders = $stmt->get_result();
        ?>
        <div class="orders-list">
            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                <div class="order-item">
                    <div class="order-info">
                        <h4>Order #<?= htmlspecialchars($order['order_id']) ?></h4>
                        <p>Amount: TK. <?= number_format($order['total_amount'], 2) ?></p>
                        <p>Status: <span class="status-<?= strtolower($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span></p>
                        
                        <p class="order-date">Ordered: <?= date('M d, Y H:i', strtotime($order['order_date'])) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>


    <!-- Low Stock Alerts -->
    <div class="low-stock-alerts feed-section">
        <h2>Low Stock Alerts</h2>
        <?php
        $stmt = $conn->prepare("
            SELECT name, quantity, quantity_type
            FROM farmer_crops
            WHERE farmer_id = ? AND quantity <= 5
            ORDER BY quantity ASC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $low_stock = $stmt->get_result();
        ?>
        <div class="alerts-list">
            <?php while ($item = $low_stock->fetch_assoc()): ?>
                <div class="alert-item">
                    <span class="alert-icon">⚠️</span>
                    <p><?= htmlspecialchars($item['name']) ?> - Only <?= htmlspecialchars($item['quantity']) ?> 
                       <?= htmlspecialchars($item['quantity_type']) ?> left</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Price Trends -->
    <div class="price-trends feed-section">
        <h2>Market Price Trends</h2>
        <div class="trends-list">
            <?php
            $stmt = $conn->prepare("
                SELECT fc1.name, 
                       AVG(fc2.price) as avg_price,
                       MAX(fc2.price) as max_price,
                       MIN(fc2.price) as min_price
                FROM farmer_crops fc1
                JOIN farmer_crops fc2 ON fc1.name = fc2.name
                WHERE fc1.farmer_id = ?
                GROUP BY fc1.name
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $trends = $stmt->get_result();
            ?>
            <?php while ($trend = $trends->fetch_assoc()): ?>
                <div class="trend-item">
                    <h4><?= htmlspecialchars($trend['name']) ?></h4>
                    <p>Average Price: TK. <?= number_format($trend['avg_price'], 2) ?></p>
                    <p>Range: TK. <?= number_format($trend['min_price'], 2) ?> - 
                       TK. <?= number_format($trend['max_price'], 2) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>




</body>

</html>
