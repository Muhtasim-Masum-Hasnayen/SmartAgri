<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Dashboard - Online Agriculture Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Investor Dashboard</h1>
    </header>
    <nav>
        <a href="home.html">Home</a>
        <a href="products.html">Products</a>
        <a href="investor.php">Investor</a>
        <a href="logout.html">Logout</a>
    </nav>
    <main>
        <h2>Invest in Farmers</h2>
        <p>View the list of farmers and invest in them to support their agricultural activities.</p>
        <div class="farmer-list">
            <?php
                // Connect to the database
                $conn = new mysqli('localhost', 'root', '', 'agriculture_management');

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch farmers' information
                $sql = "SELECT id, name, location, crop, amount_needed FROM farmers";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='farmer-card'>";
                        echo "<h3>" . $row["name"] . "</h3>";
                        echo "<p><strong>Location:</strong> " . $row["location"] . "</p>";
                        echo "<p><strong>Crop:</strong> " . $row["crop"] . "</p>";
                        echo "<p><strong>Amount Needed:</strong> $" . $row["amount_needed"] . "</p>";
                        echo "<form action='invest.php' method='post'>";
                        echo "<input type='hidden' name='farmer_id' value='" . $row["id"] . "'>";
                        echo "<label for='amount'>Amount to Invest: </label>";
                        echo "<input type='number' name='amount' min='1' required>";
                        echo "<button type='submit'>Invest</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No farmers found.</p>";
                }

                // Close connection
                $conn->close();
            ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 Online Agriculture Management System. All rights reserved.</p>
    </footer>
</body>
</html>
