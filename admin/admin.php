<?php
session_start();
include '../database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'Admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if not authenticated
    exit();
}

// Fetch the count of different user roles
$query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = $conn->query($query);

// Prepare data for the chart
$roles = [];
$counts = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = ucfirst($row['role']);  // Capitalize first letter
    $counts[] = $row['count'];
}

// Fetch order data by date and status
$query = "SELECT DATE(order_date) as date,
                 SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                 SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                 SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                 SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
          FROM orders
          GROUP BY DATE(order_date)
          ORDER BY date DESC";
$result = $conn->query($query);

// Prepare data for the chart
$dates = [];
$completed = [];
$pending = [];
$processing = [];
$shipped = [];
$delivered = [];
$cancelled = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
    $completed[] = $row['completed'];
    $pending[] = $row['pending'];
    $processing[] = $row['processing'];
    $shipped[] = $row['shipped'];
    $delivered[] = $row['delivered'];
    $cancelled[] = $row['cancelled'];
}

// Pass the data to JavaScript
echo "<script>console.log('Roles: " . json_encode($roles) . "');</script>";
echo "<script>console.log('Counts: " . json_encode($counts) . "');</script>";
echo "<script>console.log('Dates: " . json_encode($dates) . "');</script>";
echo "<script>console.log('Completed: " . json_encode($completed) . "');</script>";
echo "<script>console.log('Pending: " . json_encode($pending) . "');</script>";
echo "<script>console.log('Processing: " . json_encode($processing) . "');</script>";
echo "<script>console.log('Shipped: " . json_encode($shipped) . "');</script>";
echo "<script>console.log('Delivered: " . json_encode($delivered) . "');</script>";
echo "<script>console.log('Cancelled: " . json_encode($cancelled) . "');</script>";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Datalabels Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;        /* Centers the chart vertically */
        }

        /* Customize legend and chart text colors */
        .chart-legend {
            font-size: 18px;
            font-weight: bold;
            color: #FFEE58;
            text-align: center;
            margin-bottom: 25px;
        }

        /* Gradient for pie chart slices */
        .gradient-blue { background: linear-gradient(135deg, #007bff, #0056b3); }
        .gradient-green { background: linear-gradient(135deg, #28a745, #218838); }
        .gradient-yellow { background: linear-gradient(135deg, #ffc107, #ff9800); }
        .gradient-red { background: linear-gradient(135deg, #dc3545, #c82333); }
        .gradient-gray { background: linear-gradient(135deg, #6c757d, #495057); }

        /* Additional stylish effects */
        h2 {
            font-size: 38px;
            font-weight: bold;
            color: #8218838;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: -210px;
            height: 100vh;
            width: 260px;
            background: linear-gradient(to bottom, #3e4e60, #4b5c6b);
            padding-top: 40px;
            transition: left 0.3s ease-in-out;
        }
        .sidebar:hover {
            left: 0;
        }
        .sidebar .nav-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            padding: 15px 20px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease, padding-left 0.3s ease;
        }
        .sidebar .nav-link i {
            margin-left: 10px;
            order: 1;
        }
        .sidebar .nav-link.active {
            background: linear-gradient(to right, #007bff, #0056b3);
            color: #ffffff;
        }
        .main-content {
            margin-left: 0;
            padding: 20px;
            background-color: #ffffff;
            transition: margin-left 0.3s ease-in-out;
        }
        .sidebar:hover + .main-content {
            margin-left: 260px;
        }
        header {
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
            padding: 15px 30px;
            text-align: center;
            border-bottom: 3px solid #1e7e34;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        header a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-right: 20px;
        }
        header a:hover {
            text-decoration: underline;
        }
        #userChart {
            max-width: 100%;
            height: 400px !important;
        }
        #orderCompletionChart {
            width: 100%;
            height: 400px; /* Ensure height is specified */
        }

    </style>
</head>
<body>
<header>
    <h1>Admin Dashboard - SmartAgri</h1>
    <a href="../logout.php" class="button">Logout</a>
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
                <i class="fas fa-box"></i> Manage Products
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



<!-- User Role Distribution -->
<div class="container">
    <h2 class="text-center">User Distribution</h2>
    <canvas id="userChart"></canvas>
</div>

<script>
    // Get the chart data from PHP
    const roles = <?= json_encode($roles) ?>;
    const counts = <?= json_encode($counts) ?>;

    // Calculate total count correctly
    const total = counts.reduce((sum, value) => sum + parseInt(value), 0);

    const ctx = document.getElementById('userChart').getContext('2d');
    const userChart = new Chart(ctx, {
        type: 'doughnut',  // Change to 'doughnut' for a 2D look
        data: {
            labels: roles,
            datasets: [{
                data: counts,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                datalabels: {
                    color: '#fff',
                    anchor: 'center',
                    align: 'center',
                    formatter: (value) => {
                        let percentage = ((parseInt(value) / total) * 100).toFixed(2);
                        return percentage + "%";
                    },
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>


<h2 class="text-left">.</h2>
<h2 class="text-left">.</h2>
<h2>.</h2>

<!-- Order Completion Rate -->
<div style="max-width: 80%; margin: 0 auto; padding: 20px;">
    <h2 class="text-center">Order Completion Rate</h2>
    <canvas id="orderCompletionChart"></canvas>
</div>

<script>
    const dates = <?= json_encode($dates) ?>;
    const completed = <?= json_encode($completed) ?>;
    const pending = <?= json_encode($pending) ?>;
    const processing = <?= json_encode($processing) ?>;
    const shipped = <?= json_encode($shipped) ?>;
    const delivered = <?= json_encode($delivered) ?>;
    const cancelled = <?= json_encode($cancelled) ?>;

    const ctx2 = document.getElementById('orderCompletionChart').getContext('2d');
    const orderCompletionChart = new Chart(ctx2, {
        type: 'bar', // Stacked bar chart
        data: {
            labels: dates, // X-axis: Dates
            datasets: [
                {
                    label: 'Completed',
                    data: completed, // Data for completed orders
                    backgroundColor: '#C5E1A5', // Green for completed
                },
                {
                    label: 'Pending',
                    data: pending, // Data for pending orders
                    backgroundColor: '#ffc107', // Yellow for pending
                },
                {
                    label: 'Processing',
                    data: processing, // Data for processing orders
                    backgroundColor: '#17a2b8', // Blue for processing
                },
                {
                    label: 'Shipped',
                    data: shipped, // Data for shipped orders
                    backgroundColor: '#0D47A1', // Light Blue for shipped
                },
                {
                    label: 'Delivered',
                    data: delivered, // Data for delivered orders
                    backgroundColor: '#28a745', // Dark Green for delivered
                },
                {
                    label: 'Cancelled',
                    data: cancelled, // Data for canceled orders
                    backgroundColor: '#C62828', // Red for canceled
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: true, // Stacked bars on the X-axis
                },
                y: {
                    stacked: true, // Stacked bars on the Y-axis
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
