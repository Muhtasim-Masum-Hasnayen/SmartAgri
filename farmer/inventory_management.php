<?php
session_start();
include('../database.php');

$farmer_id = $_SESSION['user_id']; // Farmer ID from session

// Handle AJAX request for fetching inventory data
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'fetch_inventory') {
        header('Content-Type: application/json');

        try {
            $query = "SELECT 
                        fc.name, 
                        fi.remaining_stock, 
                        fc.quantity_type, 
                        MAX(fi.transaction_date) as last_updated 
                      FROM farmers_inventory fi 
                      JOIN farmer_crops fc ON fi.product_id = fc.product_id 
                      WHERE fi.farmer_id = ? 
                      GROUP BY fi.product_id";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $farmer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $inventory = [];
            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
            }

            echo json_encode($inventory);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    // Handle product search
    if ($_GET['action'] === 'search_products' && isset($_GET['search'])) {
        $search = $_GET['search'];
        $query = "SELECT id, name, quantity_type FROM products WHERE name LIKE ?";
        $stmt = $conn->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'quantity_type' => $row['quantity_type']
            ];
        }

        echo json_encode($products);
        exit;
    }
}

// Handle form submission for adding inventory transactions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = $_POST['product_id'];
        $quantity_change = $_POST['quantity_change'];
        $reason = $_POST['reason'];
        $notes = $_POST['notes'];

        // Get remaining stock
        $stmt = $conn->prepare("SELECT quantity FROM farmer_crops WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if ($product === null) {
            throw new Exception("Product not found in inventory");
        }

        $remaining_stock = $product['quantity'] + $quantity_change;

        if ($remaining_stock < 0) {
            throw new Exception("Cannot reduce inventory below 0");
        }

        // Insert transaction
        $stmt = $conn->prepare("
            INSERT INTO farmers_inventory (farmer_id, product_id, quantity_change, reason, remaining_stock, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisdss", $farmer_id, $product_id, $quantity_change, $reason, $remaining_stock, $notes);
        $stmt->execute();

        // Update farmer_crops table
        $stmt = $conn->prepare("UPDATE farmer_crops SET quantity = ? WHERE product_id = ?");
        $stmt->bind_param("di", $remaining_stock, $product_id);
        $stmt->execute();

        echo "Inventory transaction added successfully!";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #343a40;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
        }

        .summary-item {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .summary-item h3 {
            margin: 0;
            font-size: 1.2em;
            color: #495057;
        }

        .summary-item p {
            margin: 10px 0 0;
            font-size: 1.5em;
            font-weight: bold;
            color: #28a745;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background: #343a40;
            color: #fff;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            font-size: 1em;
        }

        table tbody tr:hover {
            background: #f1f1f1;
        }

        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .select2-container--default .select2-selection--single {
            height: 40px;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 1em;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        header {
    background-color: #4CAF50;
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed; /* Make the header fixed */
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000; /* Ensure it stays on top of other elements */
}



        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-left: 20px;
        }

        .sidebar {
            width: 250px;
            background-color: #1f2937;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .sidebar a {
            color: #b0bec5;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: #4b5563;
            color: white;
        }

    </style>
</head>
<body>
<div class="sidebar">
        <h2>Navigation</h2>
        <a href="../farmer.php"><i class="fas fa-seedling"></i> Dashboard</a>
        <a href="../crop_management.php"><i class="fas fa-seedling"></i> Crop/Product Management</a>
        <a href="../Buy.php"><i class="fas fa-shopping-cart"></i> Buy from Suppliers</a>
        <a href="../addNewProduct.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="order_management.php"><i class="fas fa-clipboard-list"></i> Order Management</a>
        <a href="inventory_management.php"><i class="fas fa-boxes"></i> Inventory Management</a>
        <a href="financial_overview.php"><i class="fas fa-wallet"></i> Financial Overview</a>
        <a href="../analytics_report.php"><i class="fas fa-chart-bar"></i> Analytics and Reports</a>
        
    </div>
    <div class="container">
        <h2>Farmers Inventory Management</h2>
        <div class="summary">
            <div class="summary-item">
                <h3>Total Products</h3>
                <p id="total-products">0</p>
            </div>
            <div class="summary-item">
                <h3>Total Stock</h3>
                <p id="total-stock">0</p>
            </div>
            <div class="summary-item">
                <h3>Low Stock (<10)</h3>
                <p id="low-stock">0</p>
            </div>
        </div>

        <div class="form-container">
            <form id="inventory-form">
                <label for="product_id">Product:</label>
                <select id="product_id" name="product_id" required></select>

                <label for="quantity_change">Quantity Change:</label>
                <input type="number" id="quantity_change" name="quantity_change" required>

                <label for="reason">Reason:</label>
                <select id="reason" name="reason" required>
                    <option value="Sale">Sale</option>
                    <option value="Return">Return</option>
                    <option value="Spoilage">Spoilage</option>
                    <option value="Restock">Restock</option>
                </select>

                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="3"></textarea>

                <button type="submit" class="btn">Add Transaction</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Remaining Stock</th>
                    <th>Quantity Type</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody id="inventory-body"></tbody>
        </table>
    </div>

    <script>
        function loadInventory() {
            $.get('inventory_management.php?action=fetch_inventory', function(data) {
                const tbody = $('#inventory-body');
                tbody.empty();

                let totalProducts = 0;
                let totalStock = 0;
                let lowStock = 0;

                data.forEach(item => {
                    tbody.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.remaining_stock}</td>
                            <td>${item.quantity_type}</td>
                            <td>${item.last_updated}</td>
                        </tr>
                    `);
                    totalProducts++;
                    totalStock += parseFloat(item.remaining_stock);
                    if (item.remaining_stock < 10) lowStock++;
                });

                $('#total-products').text(totalProducts);
                $('#total-stock').text(totalStock);
                $('#low-stock').text(lowStock);
            });
        }

        $(document).ready(function() {
            $('#product_id').select2({
                placeholder: 'Search for a product...',
                ajax: {
                    url: 'inventory_management.php?action=search_products',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ search: params.term }),
                    processResults: data => ({
                        results: data.map(item => ({
                            id: item.id,
                            text: `${item.name} (${item.quantity_type})`
                        }))
                    }),
                },
            });

            loadInventory();

            $('#inventory-form').submit(function(e) {
                e.preventDefault();
                $.post('inventory_management.php', $(this).serialize(), function(response) {
                    alert(response);
                    loadInventory();
                    $('#inventory-form')[0].reset();
                    $('#product_id').val(null).trigger('change');
                });
            });
        });
    </script>
</body>
</html>
