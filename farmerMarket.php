<?php>
<!DOCTYPE html>
<html>
<head>
    <title>Farmers Market - AgriBuzz</title>
    <style>
    background-image: url('R (2).jpg'); /* Path to the image */
background-size: cover;
background-repeat: no-repeat;
background-attachment: fixed;

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
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
            <?php if ($result->num_rows > 0): ?>
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

 