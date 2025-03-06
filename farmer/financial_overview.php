<?php

session_start();

include('../database.php');

$farmer_id = $_SESSION['user_id'];

// 1. Overall Sales Summary with monthly comparison
$sales_summary_query = "
    SELECT 
        YEAR(o.order_date) as year,
        MONTH(o.order_date) as month,
        SUM(o.total_amount) as total_sales,
        COUNT(DISTINCT o.order_id) as total_orders,
        AVG(o.total_amount) as average_order_value,
        (SELECT SUM(total_amount) 
         FROM orders 
         WHERE farmer_id = ? 
         AND status = 'Delivered'
         AND YEAR(order_date) = YEAR(o.order_date) 
         AND MONTH(order_date) = MONTH(o.order_date) - 1) as previous_month_sales,
        COUNT(DISTINCT fc.product_id) as products_sold
    FROM orders o
    JOIN farmer_crops fc ON o.product_id = fc.product_id
    JOIN products p ON fc.product_id = p.id
    WHERE o.farmer_id = ?
    AND o.status = 'Delivered'
    GROUP BY YEAR(o.order_date), MONTH(o.order_date)
    ORDER BY year DESC, month DESC
    LIMIT 12";

// Prepare and execute the query
$stmt = $conn->prepare($sales_summary_query);
$stmt->bind_param("ii", $farmer_id, $farmer_id);
$stmt->execute();
$sales_result = $stmt->get_result();


// 2. Top Selling Crops
$top_products_query = "
  SELECT 
        p.name,
        COUNT(o.order_id) as total_orders,
        SUM(o.total_amount) as total_revenue,
        SUM(o.quantity) as total_quantity_sold,
        AVG(fc.price) as average_price,
        (SUM(o.total_amount) / (
            SELECT SUM(total_amount) 
            FROM orders 
            WHERE farmer_id = ? 
            AND status = 'Delivered'
        ) * 100) as revenue_percentage
    FROM orders o
    JOIN farmer_crops fc ON o.product_id = fc.product_id
    JOIN products p ON fc.product_id = p.id
    WHERE o.farmer_id = ?
    AND o.status = 'Delivered'
    GROUP BY p.id, p.name
    ORDER BY total_revenue DESC
    LIMIT 5";

//Monthly Financial Summary: Revenue, Expenses, Net Profit, and Purchase Insights for the Last 12 Months"
    

    $financial_query = $conn->prepare("
    SELECT 
        YEAR(s.sale_date) as year,
        MONTH(s.sale_date) as month,
        SUM(s.total_price) as supply_expenses,
        (SELECT SUM(total_price) 
         FROM orders 
         WHERE farmer_id = ? 
         AND status = 'Delivered'
         AND YEAR(order_date) = YEAR(s.sale_date) 
         AND MONTH(order_date) = MONTH(s.sale_date)) as monthly_revenue,
        (SELECT SUM(total_price) 
         FROM orders 
         WHERE farmer_id = ? 
         AND status = 'Delivered'
         AND YEAR(order_date) = YEAR(s.sale_date) 
         AND MONTH(order_date) = MONTH(s.sale_date)) - 
        SUM(s.total_price) as net_profit,
        COUNT(DISTINCT s.supplies_sale_id) as total_purchases,
        AVG(s.total_price) as average_purchase_amount
    FROM supplier_sales s
    WHERE s.farmer_id = ?
    AND s.status = 'Delivered'
    GROUP BY YEAR(s.sale_date), MONTH(s.sale_date)
    ORDER BY year DESC, month DESC
    LIMIT 12");

// Prepare and execute queries
$stmt = $conn->prepare($sales_summary_query);
$stmt->bind_param("ii", $farmer_id, $farmer_id);
$stmt->execute();
$sales_result = $stmt->get_result();

$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("ii", $farmer_id, $farmer_id);
$stmt->execute();
$crops_result = $stmt->get_result();

$financial_query->bind_param("iii", $farmer_id, $farmer_id, $farmer_id);
$financial_query->execute();
$financial_result = $financial_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Overview - AgriBuzz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Main Layout */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 250px;
            background: #388e3c;
            padding-top: 80px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: #2e7d32;
            padding-left: 35px;
        }

        .sidebar-menu a.active {
            background: #2e7d32;
            border-left: 4px solid #81c784;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }

        .bg-success {
            background-color: #388e3c !important;
        }

        /* Summary Cards */
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            margin: 10px 0;
            font-weight: 600;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table td, .table th {
            padding: 15px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Progress Bar */
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }

        .progress-bar {
            background-color: #388e3c;
        }

        /* Text Colors */
        .text-success {
            color: #388e3c !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        /* Icons */
        .fas {
            margin-right: 8px;
        }

        /* Responsive Table */
        .table-responsive {
            border-radius: 10px;
            overflow-x: auto;
        }

        /* Custom Badge Styles */
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Animation for Numbers */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animated-number {
            animation: countUp 0.5s ease-out forwards;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding-top: 20px;
            }
            .main-content {
                margin-left: 0;
            }
            .card-deck {
                flex-direction: column;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #388e3c;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #2e7d32;
        }
    </style>
</head>


<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../farmer.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="financial_overview.php" class="active"><i class="fas fa-chart-line"></i> Financial Overview</a></li>
            <li><a href="../crop_management.php"><i class="fas fa-seedling"></i> Crop Management</a></li>
            <li><a href="order_management.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body bg-success text-white">
                <h2><i class="fas fa-chart-line"></i> Financial Overview</h2>
                <p class="mb-0">Track your financial performance and analyze your business growth</p>
            </div>
        </div>





<!-- HTML for Financial Overview Section -->
<div class="container-fluid">
    <!-- Sales Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Monthly Sales Overview</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Total Sales</th>
                                    <th>Orders</th>
                                    <th>Avg Order Value</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $sales_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date("F Y", mktime(0, 0, 0, $row['month'], 1, $row['year'])); ?></td>
                                        <td>৳<?php echo number_format($row['total_sales'], 2); ?></td>
                                        <td><?php echo $row['total_orders']; ?></td>
                                        <td>৳<?php echo number_format($row['average_order_value'], 2); ?></td>
                                        <td>
                                            <?php
                                            $growth = $row['previous_month_sales'] ? 
                                                (($row['total_sales'] - $row['previous_month_sales']) / $row['previous_month_sales'] * 100) : 0;
                                            $growth_class = $growth >= 0 ? 'text-success' : 'text-danger';
                                            $growth_icon = $growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                            ?>
                                            <span class="<?php echo $growth_class; ?>">
                                                <i class="fas <?php echo $growth_icon; ?>"></i>
                                                <?php echo abs(number_format($growth, 1)); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Crops and Expense Analysis -->
    <div class="row">
        <!-- Top Selling Crops -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-crown"></i> Top Performing Crops</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Crop</th>
                                    <th>Revenue</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $crops_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>৳<?php echo number_format($row['total_revenue'], 2); ?></td>
                                        <td><?php echo number_format($row['total_quantity_sold']); ?> units</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $row['revenue_percentage']; ?>%">
                                                    <?php echo number_format($row['revenue_percentage'], 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

       
<!-- Financial Overview Section -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Financial Overview
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <?php
                        $total_revenue = 0;
                        $total_expenses = 0;
                        $total_profit = 0;
                        
                        // Calculate totals
                        if($financial_result->num_rows > 0) {
                            $financial_result->data_seek(0);
                            while($row = $financial_result->fetch_assoc()) {
                                $total_revenue += $row['monthly_revenue'] ?? 0;
                                $total_expenses += $row['supply_expenses'];
                                $total_profit += $row['net_profit'];
                            }
                            $financial_result->data_seek(0); // Reset pointer
                        }
                        ?>
                        <!-- Total Revenue Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Total Revenue (12 Months)</h6>
                                    <h3 class="text-success">৳<?php echo number_format($total_revenue, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <!-- Total Expenses Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Total Expenses (12 Months)</h6>
                                    <h3 class="text-danger">৳<?php echo number_format($total_expenses, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <!-- Net Profit Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Net Profit (12 Months)</h6>
                                    <h3 class="<?php echo $total_profit >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        ৳<?php echo number_format($total_profit, 2); ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Breakdown Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Revenue</th>
                                    <th>Expenses</th>
                                    <th>Net Profit</th>
                                    <th>Purchases</th>
                                    <th>Avg Purchase</th>
                                    <th>Profit Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $financial_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date("F Y", mktime(0, 0, 0, $row['month'], 1, $row['year'])); ?></td>
                                        <td class="text-success">
                                            ৳<?php echo number_format($row['monthly_revenue'] ?? 0, 2); ?>
                                        </td>
                                        <td class="text-danger">
                                            ৳<?php echo number_format($row['supply_expenses'], 2); ?>
                                        </td>
                                        <td class="<?php echo $row['net_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            ৳<?php echo number_format($row['net_profit'], 2); ?>
                                        </td>
                                        <td><?php echo $row['total_purchases']; ?></td>
                                        <td>৳<?php echo number_format($row['average_purchase_amount'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $profit_margin = $row['monthly_revenue'] ? 
                                                ($row['net_profit'] / $row['monthly_revenue'] * 100) : 0;
                                            $margin_class = $profit_margin >= 0 ? 'text-success' : 'text-danger';
                                            ?>
                                            <span class="<?php echo $margin_class; ?>">
                                                <?php echo number_format($profit_margin, 1); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>