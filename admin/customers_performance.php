<?php
session_start();
include('../database.php');

// top custom with all performance
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
ORDER BY c.name ASC;

";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Customer Insights</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard - Customer Insights</h1>
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
                        <th>Total Suppliers</th>
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
                            <td><?= htmlspecialchars($row['total_suppliers']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php
$conn->close();
?>
