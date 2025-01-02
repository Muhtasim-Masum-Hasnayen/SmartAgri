<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - SmartAgri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-nav {
            display: flex;
        }
        .header-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 4px;
        }
        .header-nav a:hover {
            background-color: #45a049;
        }
        .sidebar {
            width: 20%;
            background-color: #4CAF50;
            color: white;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-top: 0;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .sidebar a:hover {
            background-color: #45a049;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .nav-logo div {
            background-color: white;
            color: #4CAF50;
            font-size: 20px;
            font-weight: bold;
            padding: 10px;
            border-radius: 8px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .chart-container {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-logo">
            <div>SmartAgri</div>
        </div>
        <div class="header-nav">
            <a href="farmers_kit.php">Farmer's Kit</a>
            <a href="farmers_market.php">Farmer's Market</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>
    <div style="display: flex;">
        <div class="sidebar">
            <h2>Navigation</h2>
            <a href="farmer/crop_management.php">Crop/Product Management</a>
            <a href="farmer/order_management.php">Order Management</a>
            <a href="farmer/inventory_management.php">Inventory Management</a>
            <a href="farmer/financial_overview.php">Financial Overview</a>
            <a href="farmer/weather_updates.php">Agricultural Support</a>
            <a href="farmer/analytics_report.php">Analytics and Reports</a>
            <a href="farmer/supplier_interection.php">Supplier Interaction</a>
            <a href="farmar/laborhiring.php">Labor Hiring</a>
            <a href="farmer/cart.php">Cart</a>
            <a href="farmer/myprofile.php">My Account</a>
        </div>
        <div class="main-content">
            <h2>Analysis and Reports</h2>
            <div class="chart-container">
                <h3>Profit and Expenses</h3>
                <canvas id="profitExpensesChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('profitExpensesChart').getContext('2d');
        const profitExpensesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May'],
                datasets: [
                    {
                        label: 'Profit',
                        data: [500, 700, 800, 600, 900],
                        borderColor: 'green',
                        tension: 0.4,
                        fill: false,
                    },
                    {
                        label: 'Expenses',
                        data: [400, 600, 700, 500, 800],
                        borderColor: 'red',
                        tension: 0.4,
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Profit vs. Expenses Over Time',
                    },
                },
            },
        });
    </script>
</body>
</html>
