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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Modified loadContent function
        function loadContent(page) {
            const contentContainer = document.getElementById('dynamicContent');
            fetch(page)
                .then(response => response.text())
                .then(html => {
                    contentContainer.innerHTML = html;
                    // Re-attach event handlers if needed
                    attachEventHandlers();
                })
                .catch(err => {
                    contentContainer.innerHTML = '<p>Error loading page.</p>';
                    console.error('Error loading content:', err);
                });
        }

        // Function to attach event handlers after dynamic content is loaded
        function attachEventHandlers() {
            // Add event listeners to all select buttons
            document.querySelectorAll('.select-crop-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const productId = this.getAttribute('data-product-id');
                    const cropName = this.getAttribute('data-crop-name');
                    window.selectCrop(productId, cropName);
                });
            });
            console.log('Content loaded and handlers attached');
        }
    </script>
</body>
</html>
