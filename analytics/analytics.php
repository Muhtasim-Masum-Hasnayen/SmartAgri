<?php
session_start();
include('../database.php');


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Copy the same sidebar styles from admin.php -->
    <style>
        /* Copy the sidebar styles from admin.php */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            padding-top: 20px;
            background-color: #343a40;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .nav-link {
            color: #fff;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Copy the sidebar from admin.php -->
    <div class="sidebar">
        <!-- Same sidebar content as in admin.php -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../admin.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
            <!-- Rest of your navigation items -->
        </ul>
    </div>

    <!-- Analytics Content -->
    <div class="main-content">
        <h2>Analytics Dashboard</h2>
        
        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="<?php echo $_GET['start_date'] ?? date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>End Date:</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="<?php echo $_GET['end_date'] ?? date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Analytics Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Farmer Name</th>
                                <th>Total Orders</th>
                                <th>Total Revenue</th>
                                <th>Average Order Value</th>
                                <th>Completed Orders</th>
                                <th>Cancelled Orders</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $start_date = $_GET['start_date'] ?? date('Y-m-01');
                            $end_date = $_GET['end_date'] ?? date('Y-m-d');
                            
                           
                            $query = "SELECT 
                            u.name AS farmer_name,
                            COUNT(o.order_id) AS total_orders,
                            SUM(o.total_amount) AS total_revenue,
                            AVG(o.total_amount) AS average_order_value,
                            COUNT(CASE WHEN o.status = 'Delivered' THEN 1 END) AS completed_orders,
                            COUNT(CASE WHEN o.status = 'Cancelled' THEN 1 END) AS cancelled_orders
                          FROM farmer f
                          JOIN users u ON f.farmer_id = u.user_id
                          LEFT JOIN orders o ON f.farmer_id = o.farmer_id
                          WHERE o.order_date BETWEEN ? AND ?
                          GROUP BY f.farmer_id, u.name
                          ORDER BY total_revenue DESC";
                
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ss", $start_date, $end_date);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()) {
                                $completion_rate = ($row['total_orders'] > 0) 
                                    ? round(($row['completed_orders'] / $row['total_orders']) * 100, 2) 
                                    : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                    <td><?php echo $row['total_orders']; ?></td>
                                    <td>Taka:<?php echo number_format($row['total_revenue'], 2); ?></td>
                                    <td>Taka:<?php echo number_format($row['average_order_value'], 2); ?></td>
                                    <td><?php echo $row['completed_orders']; ?></td>
                                    <td><?php echo $row['cancelled_orders']; ?></td>
                                    <td><?php echo $completion_rate; ?>%</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if(link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
