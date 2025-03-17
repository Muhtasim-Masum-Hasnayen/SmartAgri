<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('database.php');  // Adjust path as needed

// Check if farmer is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$farmer_id = $_SESSION['user_id'];
// Analytics Functions
function getProductPerformanceReport($farmer_id) {
    global $conn;
     //Calculates the total quantity sold for each product in the last 30 days from delivered orders.
    $query = "WITH ProductSales AS (
        SELECT 
            o.product_id,
            SUM(o.quantity) as total_sold
        FROM orders o
        WHERE o.order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        AND o.status = 'Delivered'
        GROUP BY o.product_id
    )
    SELECT 
        p.*,
        COALESCE(ps.total_sold, 0) as monthly_sales,
        p.price * COALESCE(ps.total_sold, 0) as monthly_revenue
    FROM farmer_crops p
    LEFT JOIN ProductSales ps ON p.product_id = ps.product_id
    WHERE p.farmer_id = ?
    ORDER BY monthly_sales DESC";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $farmer_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        error_log("Error in getProductPerformanceReport: " . $e->getMessage());
        return false;
    }
}

function getDetailedSalesAnalysis($farmer_id) {
    global $conn;
    // This query retrieves product sales performance for the past 30 days, including total orders,
    // units sold, revenue, average order size, and current stock, grouped by product 
    // and sorted by total revenue in descending order.
    $query = "SELECT 
        p.name,
        COUNT(DISTINCT o.order_id) as total_orders,
        SUM(o.quantity) as total_units_sold,
        SUM(o.quantity * p.price) as total_revenue,
        AVG(o.quantity) as avg_order_size,
        p.quantity as current_stock
    FROM farmer_crops p
    LEFT JOIN orders o ON p.product_id = o.product_id
    AND o.order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    WHERE p.farmer_id = ?
    GROUP BY p.product_id, p.name, p.quantity
    ORDER BY total_revenue DESC";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $farmer_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        error_log("Error in getDetailedSalesAnalysis: " . $e->getMessage());
        return false;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Farmer Dashboard</title>
    <link rel="stylesheet" href="./css/analytics_report.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-container {
    position: relative;
    height: 400px;
    width: 100%;
    margin: 20px 0;
}
.analytics-container {
    padding: 20px;
}

.analytics-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    padding: 25px;
    transition: transform 0.2s ease;
}

.analytics-card:hover {
    transform: translateY(-5px);
}

.analytics-header {
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 20px;
    padding-bottom: 15px;
}

.analytics-header h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(145deg, #ffffff, #f5f7fa);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-card .stat-value {
    color: #2c3e50;
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
}

.stat-card .stat-label {
    color: #7f8c8d;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.chart-container {
    position: relative;
    height: 350px;
    margin: 20px 0;
    padding: 15px;
    background: white;
    border-radius: 10px;
}

.table-responsive {
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden;
}

.analytics-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.analytics-table th {
    background-color: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
}

.analytics-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #dee2e6;
    color: #2c3e50;
}

.analytics-table tr:hover {
    background-color: #f8f9fa;
}

.print-button {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.print-button:hover {
    background-color: #2980b9;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 8px;
}

.trend-up {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.trend-down {
    background-color: #ffebee;
    color: #c62828;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .analytics-card {
        padding: 15px;
    }
    
    .chart-container {
        height: 300px;
    }
}

@media print {
    .print-button {
        display: none;
    }
}

</style>



</head>
<body>
    <div class="analytics-container">
        <!-- Page Header -->
        <div class="analytics-header d-flex justify-content-between align-items-center mb-4">
            <h2>Analytics & Reports</h2>
            <button class="print-button" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <?php
            // Calculate total revenue
            $total_revenue_query = "SELECT SUM(total_amount) as total_revenue 
                                  FROM orders o 
                                  WHERE o.farmer_id = ? 
                                  AND o.order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                                  AND o.status = 'Delivered'";


            $stmt = $conn->prepare($total_revenue_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $total_revenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

            // Calculate total orders
            $total_orders_query = "SELECT COUNT(DISTINCT o.order_id) as total_orders 
                                 FROM orders o  
                                 WHERE o.farmer_id = ?
                                 AND o.status='Delivered'"
                                ;
            $stmt = $conn->prepare($total_orders_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $total_orders = $stmt->get_result()->fetch_assoc()['total_orders'] ?? 0;

            // Calculate total products
            $total_products_query = "SELECT COUNT(distinct f.product_id) as total_products 
                                   FROM farmer_crops f 
                                   WHERE farmer_id = ?";
            $stmt = $conn->prepare($total_products_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $total_products = $stmt->get_result()->fetch_assoc()['total_products'] ?? 0;
            ?>
            
            <div class="stat-card">
                <div class="stat-label">Total Revenue (30 Days)</div>
                <div class="stat-value">Tk.<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Products</div>
                <div class="stat-value"><?php echo number_format($total_products); ?></div>
            </div>
        </div>

        <!-- Product Performance -->
        <div class="analytics-card">
            <div class="analytics-header">
                <h3>Product Performance (Last 30 Days)</h3>
            </div>
            <div class="table-responsive">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Price</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = getProductPerformanceReport($_SESSION['user_id']);
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                echo "<td>'Tk." . number_format($row['price'], 2) . "</td>";
                                echo "<td>" . htmlspecialchars($row['monthly_sales']) . "</td>";
                                echo "<td>Tk." . number_format($row['monthly_revenue'], 2) . "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>




    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
