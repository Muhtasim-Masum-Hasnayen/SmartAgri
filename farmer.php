<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - SmartAgri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar {
            width: 250px;
            background-color: #f4f4f4;
            padding: 20px;
            height: calc(100vh - 70px);
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background-color: #ddd;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>

    <!-- Add this script in the head -->
    <script>
        // Define selectCrop in the window object to make it globally available
        window.selectCrop = function(productId, cropName) {
            console.log('SelectCrop called:', productId, cropName); // Debug line
            const productIdElement = document.getElementById('product_id');
            const selectedCropElement = document.getElementById('selected-crop');
            
            if (productIdElement && selectedCropElement) {
                productIdElement.value = productId;
                selectedCropElement.textContent = cropName;
            }
        }

        // Error handling
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.log('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError: ' + error);
            return false;
        };
    </script>
</head>
<body>
    <header>
        <h1>Farmer Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
        </div>
    </header>

    <div style="display: flex;">
        <div class="sidebar">
        
    <h2>Navigation</h2>
    <a href="crop_management.php">Crop/Product Management</a>
    <a href="Buy.php">Buy from Suppliers</a>
    <a href="addNewProduct.php">Add New Product</a>
    <a href="farmer/order_management.php">Order Management</a>
    <a href="farmer/inventory_management.php">Inventory Management</a>
   
    <a href="farmer/financial_overview.php">Financial Overview</a>
    <a href="analytics_report.php">Analytics and Reports</a>
    <a href="farmer/my_account.php">My Account</a>
</div>

        <div class="main-content">
            <div class="content-container" id="dynamicContent">
                <h2>Welcome to the Farmer Dashboard</h2>
                <p>Select an option from the sidebar or navigation bar to get started.</p>
            </div>
        </div>
    </div>









</body>

</html>
