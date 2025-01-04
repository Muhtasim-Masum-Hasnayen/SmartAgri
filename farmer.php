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
            display: flex;
            flex-direction: column;
            background-color: #f4f4f4;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            width: 80%;
        }
        .content-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <div>SmartAgri</div>
        <nav class="header-nav">
            <a href="#" onclick="loadContent('farmer/farmers_market.php')">Farmer's Market</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div style="display: flex;">
        <div class="sidebar">
            <h2>Navigation</h2>
            <a href="#" onclick="loadContent('crop_management.php')">Crop/Product Management</a> 
            <a href="#" onclick="loadContent('farmer/order_management.php')">Order Management</a>
            <a href="#" onclick="loadContent('farmer/inventory_management.php')">Inventory Management</a>
            <a href="#" onclick="loadContent('farmer/labor_hiring.php')">Labor Hiring</a>
            <a href="#" onclick="loadContent('farmer/financial_overview.php')">Financial Overview</a>
            <a href="#" onclick="loadContent('farmer/analytics_reports.php')">Analytics and Reports</a>
            <a href="#" onclick="loadContent('farmer/my_account.php')">My Account</a>
        </div>
        <div class="main-content">
            <div class="content-container" id="dynamicContent">
                <h2>Welcome to the Farmer Dashboard</h2>
                <p>Select an option from the sidebar or navigation bar to get started.</p>
            </div>
        </div>
    </div>

    <script>
        // Dynamic content loader
        function loadContent(page) {
            const contentContainer = document.getElementById('dynamicContent');
            fetch(page)
                .then(response => response.text())
                .then(html => {
                    contentContainer.innerHTML = html;
                })
                .catch(err => {
                    contentContainer.innerHTML = '<p>Error loading page.</p>';
                });
        }
    </script>
</body>
</html>