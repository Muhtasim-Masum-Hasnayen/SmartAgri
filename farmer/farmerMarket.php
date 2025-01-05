<?php
// Start PHP block
include 'database.php'; // Include your database connection file

// SQL query to fetch data
$query = "SELECT product_name, price, description, farmer_name, contact FROM farmers_market";
$result = $conn->query($query); // Execute the query

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Market - SmartAgri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('R (2).jpg'); /* Path to the image */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        h1 {
            text-align: center;
            color: #fff;
            margin: 20px;
            background-color: rgba(0, 128, 0, 0.7);
            padding: 10px;
            border-radius: 10px;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            opacity: 0.9;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Farmers Market</h1>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Description</th>
                <th>Farmer</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']); ?></td>
                        <td><?= htmlspecialchars($row['price']); ?> USD</td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td><?= htmlspecialchars($row['farmer_name']); ?></td>
                        <td><?= htmlspecialchars($row['contact']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No products available at the moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
