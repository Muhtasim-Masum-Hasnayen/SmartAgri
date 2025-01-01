<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AgriBuzz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            background-color: #333;
            overflow: hidden;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            text-align: center;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Your Farmer Dashboard</h1>
        <p>Manage your farming activities efficiently!</p>
    </header>
    <nav>
        <a href="farmer_dashboard.php">Dashboard</a>
        <a href="f_manageCrop.php">Manage Crops</a>
        <a href="market_prices.php">Market Prices</a>
        <a href="resources.php">Resources</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="container">
        <h2>Farmer Overview</h2>
        <div class="card">
            <h3>Your Crops</h3>
            <p>View and manage your crops.</p>
            <a href="manage_crops.php"><button>Manage Crops</button></a>
        </div>
        <div class="card">
            <h3>Market Prices</h3>
            <p>Check the latest market prices for your crops.</p>
            <a href="market_prices.php"><button>View Prices</button></a>
        </div>
        <div class="card">
            <h3>Resources</h3>
            <p>Access farming resources and guides.</p>
            <a href="resources.php"><button>View Resources</button></a>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 AgriBuzz. All rights reserved.</p>
    </footer>
</body>
</html>