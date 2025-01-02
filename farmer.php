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
            background-color: white;
            overflow: hidden;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        nav a {
            color: #333;
            text-decoration: none;
            padding: 14px 20px;
            text-align: center;
            display: block;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .nav-logo {
            display: flex;
            align-items: center;
        }
        .nav-logo img {
            height: 40px;
            margin-right: 10px;
        }
        .cart-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cart-button:hover {
            background-color: #45a049;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            width: 200px;
        }
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .dropdown-content a:hover {
            background-color: #ddd;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .container {
            padding: 20px;
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
        <div class="nav-logo">
            <img src="path/to/agribuzz-logo.png" alt="AgriBuzz Logo">
            <span>AGRIBUZZ</span>
        </div>
        <a href="home.php">HOME</a>
        <a href="blogs.php">BLOGS</a>
        <a href="news.php">NEWS</a>
        <a href="farmers_kit.php">FARMER'S KIT</a>
        <a href="farmers_market.php">FARMER'S MARKET</a>
        <a href="hire.php">HIRE</a>
        <div class="dropdown">
            <a href="#">MY ACCOUNT</a>
            <div class="dropdown-content">
                <a href="farmer_panel.php">Farmer Panel</a>
                <a href="my_profile.php">My Profile</a>
                <a href="produce.php">Produce</a>
                <a href="farm_sales_report.php">Farm Sales Report</a>
                <a href="farm_kit_details.php">Farmer Kit's Details</a>
                <a href="hire_details.php">Hire Details</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <a href="contact.php">CONTACT</a>
        <button class="cart-button">CART (0)</button>
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
