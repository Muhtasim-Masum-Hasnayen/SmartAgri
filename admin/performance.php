<?php
session_start();
include '../database.php'; // Include the database connection file

// Check if the user is logged in and has the role of 'Admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if not authenticated
    exit();
}


// top customer with all performance
// Query to fetch analytics data
$sql = "
   SELECT 
    c.customer_id,
    c.name AS customer_name,
    u_customer.email AS customer_email, -- Email for customer from users table
    COUNT(o.order_id) AS total_orders,
    IFNULL(SUM(o.total_amount), 0) AS total_spent,
    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS purchased_products,
    (
        SELECT f.farm_name 
        FROM farmer f 
        JOIN users u_farmer ON f.farmer_id = u_farmer.user_id -- Join farmer with users to get email
        WHERE f.farmer_id = (
            SELECT o.farmer_id 
            FROM orders o 
            WHERE o.customer_id = c.customer_id 
            GROUP BY o.farmer_id 
            ORDER BY COUNT(o.farmer_id) DESC 
            LIMIT 1
        )
    ) AS top_farmer,
    (
        SELECT u_farmer.email 
        FROM farmer f 
        JOIN users u_farmer ON f.farmer_id = u_farmer.user_id -- Fetch farmer's email
        WHERE f.farmer_id = (
            SELECT o.farmer_id 
            FROM orders o 
            WHERE o.customer_id = c.customer_id 
            GROUP BY o.farmer_id 
            ORDER BY COUNT(o.farmer_id) DESC 
            LIMIT 1
        )
    ) AS top_farmer_email, -- Email for the top farmer
    COUNT(DISTINCT r.id) AS total_reviews,
    IFNULL(AVG(r.rating), 0) AS average_rating,
    COUNT(DISTINCT s.supplier_id) AS total_suppliers
FROM 
    customer c
JOIN users u_customer ON c.customer_id = u_customer.user_id -- Join customer with users to get email
LEFT JOIN orders o ON c.customer_id = o.customer_id
LEFT JOIN products p ON o.product_id = p.id
LEFT JOIN product_reviews r ON c.customer_id = r.customer_id
LEFT JOIN supplier_sales ss ON c.customer_id = ss.farmer_id
LEFT JOIN supplies s ON ss.supplier_id = s.supplier_id
GROUP BY c.customer_id
ORDER BY c.name ASC
LIMIT 5; -- Restrict the results to the top 5
";


$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}





// top farmer top review revinue generated, completion of order
$sql1 = "
    SELECT 
        f.farmer_id,
        f.farm_name,
        u.email AS farmer_email,
        COUNT(DISTINCT o.order_id) AS total_orders,
        IFNULL(SUM(o.total_amount), 0) AS total_sales,
        AVG(r.rating) AS average_rating,
        COUNT(DISTINCT p.id) AS unique_products_sold,
        SUM(CASE WHEN o.status = 'Delivered' THEN 1 ELSE 0 END) / COUNT(o.order_id) * 100 AS timely_order_rate,
        SUM(CASE WHEN o.status = 'Cancelled' THEN 1 ELSE 0 END) / COUNT(o.order_id) * 100 AS cancelation_rate
    FROM 
        farmer f
    JOIN users u ON f.farmer_id = u.user_id
    LEFT JOIN orders o ON f.farmer_id = o.farmer_id
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN product_reviews r ON r.farmer_id = f.farmer_id
    GROUP BY 
        f.farmer_id, f.farm_name, u.email
    ORDER BY 
        total_sales DESC, 
        average_rating DESC, 
        timely_order_rate DESC, 
        cancelation_rate ASC, 
        unique_products_sold DESC
    LIMIT 5;
";

// Execute query
$result1 = $conn->query($sql1);











?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>


/* Sidebar Styling */
.sidebar {
    position: fixed;
    top: 0;
    left: -210px; /* Sidebar is hidden off-screen initially */
    height: 100vh;
    width: 260px;
    background: linear-gradient(to bottom, #3e4e60, #4b5c6b); /* Gradient from dark blue to grey */
    padding-top: 40px;
    transition: left 0.3s ease-in-out; /* Smooth transition for showing and hiding */
}

/* When sidebar is hovered, it slides into view */
.sidebar:hover {
    left: 0; /* Moves the sidebar into view */
}
/* Sidebar link styling: text on the left, icon on the right */
.sidebar .nav-link {
    display: flex;
    justify-content: space-between; /* Space between text and icon */
    align-items: center; /* Vertically center text and icon */
    color: #ffffff;
    padding: 15px 20px;
    text-decoration: none;
    font-size: 16px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

.sidebar .nav-link i {
    margin-left: 10px; /* Add space between the text and the icon */
    order: 1; /* Ensures icon is after the text */
}



/* Active link background */
.sidebar .nav-link.active {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for active state */
    color: #ffffff;
}



/* Main Content */
.main-content {
    margin-left: 0;
    padding: 20px;
    background-color: #ffffff;
    transition: margin-left 0.3s ease-in-out;
}

/* Sidebar hover triggers main content to shift */
.sidebar:hover + .main-content {
    margin-left: 260px; /* Shifts main content to make space for sidebar */
}

/* Header with Gradient */
header {
    background: linear-gradient(to right, #28a745, #218838); /* Green gradient */
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

/* Container Styling */
.container {
    width: 95%;
    max-width: 1300px;
    margin: 0 auto;
    padding: 30px;
}

/* Section Styling */
.section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    padding: 25px;
}

.section h2 {
    margin-top: 0;
    font-size: 22px;
    color: #333;
    font-weight: 600;
}

/* Table Styling with Gradient Header */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

table thead th {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for table header */
    color: #ffffff;
    text-align: left;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 600;
}

table tbody td {
    border: 1px solid #ddd;
    padding: 15px;
    font-size: 14px;
    color: #495057;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

table tbody td .button {
    background: linear-gradient(to right, #007bff, #0056b3); /* Gradient for button */
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

table tbody td .button:hover {
    background: linear-gradient(to right, #0056b3, #004085); /* Darker gradient on hover */
}

/* Form Styling */
form label {
    display: block;
    margin: 12px 0 6px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
}

form input[type="text"],
form input[type="number"],
form select,
form input[type="file"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    transition: border 0.3s ease;
}

form input[type="text"]:focus,
form input[type="number"]:focus,
form select:focus {
    border-color: #007bff;
    background-color: #ffffff;
}

form input[type="submit"] {
    background: linear-gradient(to right, #28a745, #218838); /* Green gradient for buttons */
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form input[type="submit"]:hover {
    background: linear-gradient(to right, #218838, #1e7e34); /* Darker green on hover */
}

/* Notifications */
.error {
    color: #dc3545;
    font-weight: bold;
    margin-top: 10px;
}

.success {
    color: #28a745;
    font-weight: bold;
    margin-top: 10px;
}


    </style>
</head>
<body>

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
                <a class="nav-link" href="./customers_performance.php">
                    <i class="fas fa-chart-bar"></i>Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_farmers.php">
                    <i class="fas fa-users"></i> Farmers
                </a>
            </li>
            <li class="nav-item">
                            <a class="nav-link" href="manage_suppliers.php">
                                <i class="fas fa-users"></i> Manage Suppliers
                            </a>
                        </li>
                        <li class="nav-item">
                                                    <a class="nav-link" href="manage_products.php">
                                                        <i class="fas fa-users"></i> Manage Products
                                                    </a>
                                                </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_customers.php">
                    <i class="fas fa-user-friends"></i> Manage Customers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>


    <header>
        <h1>Admin Dashboard - SmartAgri</h1>
        <a href="logout.php" class="button">Logout</a>
    </header>


    <main>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Total Orders</th>
                        <th>Total Spent (TK)</th>
                        <th>Purchased Products</th>
                      
                        <th>Total Reviews</th>
                        <th>Average Rating</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['customer_id']); ?></td>
                            <td><?= htmlspecialchars($row['customer_name']); ?></td>
                            <td><?= htmlspecialchars($row['customer_email']); ?></td>
                            <td><?= htmlspecialchars($row['total_orders']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['total_spent'], 2)); ?></td>
                            <td><?= htmlspecialchars($row['purchased_products']); ?></td>
                         
                            <td><?= htmlspecialchars($row['total_reviews']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['average_rating'], 1)); ?></td>
                            
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <main>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Farmer ID</th>
                        <th>Farm Name</th>
                        <th>Email</th>
                        <th>Total Orders</th>
                        <th>Total Sales (TK)</th>
                        <th>Average Rating</th>
                        <th>Unique Products Sold</th>
                        <th>Timely Order Rate (%)</th>
                        <th>Cancellation Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result1->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['farmer_id']); ?></td>
                            <td><?= htmlspecialchars($row['farm_name']); ?></td>
                            <td><?= htmlspecialchars($row['farmer_email']); ?></td>
                            <td><?= htmlspecialchars($row['total_orders']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['total_sales'], 2)); ?></td>
                            <td class="highlight"><?= htmlspecialchars(number_format($row['average_rating'], 1)); ?></td>
                            <td><?= htmlspecialchars($row['unique_products_sold']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['timely_order_rate'], 2)); ?></td>
                            <td><?= htmlspecialchars(number_format($row['cancelation_rate'], 2)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>



            
           
</body>
</html>