


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Dashboard - Online Agriculture Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Labour Dashboard</h1>
    </header>
    <nav>
        <a href="home.html">Home</a>
        <a href="products.html">Products</a>
        <a href="labour.php">Labour</a>
        <a href="logout.php">Logout</a>
    </nav>
    <main>
        <h2>Job Opportunities</h2>
        <p>View the available farming jobs and apply for the ones you are interested in.</p>
        <div class="job-list">
            <?php
                // Connect to the database
                $conn = new mysqli('localhost', 'root', '', 'agriculture_management');

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch job advertisements
                $sql = "SELECT j.id, f.name AS farmer_name, j.location, j.price_per_day, j.description
                        FROM jobs j
                        JOIN farmers f ON j.farmer_id = f.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='job-card'>";
                        echo "<h3>Farmer: " . $row["farmer_name"] . "</h3>";
                        echo "<p><strong>Location:</strong> " . $row["location"] . "</p>";
                        echo "<p><strong>Price per Day:</strong> $" . $row["price_per_day"] . "</p>";
                        echo "<p><strong>Description:</strong> " . $row["description"] . "</p>";
                        echo "<form action='apply.php' method='post'>";
                        echo "<input type='hidden' name='job_id' value='" . $row["id"] . "'>";
                        echo "<button type='submit'>Apply</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No job advertisements found.</p>";
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
