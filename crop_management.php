<?php
session_start();
include('database.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
        // Debug: Print received data
        error_log("Received POST data: " . print_r($_POST, true));
        
        // Validate inputs
        if (empty($_POST['product_id']) || empty($_POST['name']) || 
        empty($_POST['description']) || empty($_POST['price']) || 
        empty($_POST['quantity']) || empty($_POST['quantity_type'])) {
        throw new Exception("All fields are required");
    }

    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $farmer_id = $_SESSION['user_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $quantity_type = $_POST['quantity_type'];
    $status = 'available';
        // Handle file upload if present
        $image_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO farmer_crops 
        (farmer_id, product_id, name, description, price, quantity, quantity_type, image, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iissdisss", $farmer_id, $product_id, $name, 
        $description, $price, $quantity, $quantity_type, $image_path, $status);
    
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Return success response for AJAX
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
        exit;

    } catch (Exception $e) {
        error_log("Error adding product: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
       body {
            background: #f9f9f9;
            font-family: 'Roboto', sans-serif;
        }

        .header {
            background: linear-gradient(45deg, #3b8d99, #6b6b83);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .form-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #3b8d99;
            border: none;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #6b6b83;
        }

        .table {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(45deg, #3b8d99, #6b6b83);
            color: white;
        }

        .table td, .table th {
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f1f1f1;
            cursor: pointer;
        }

        .badge-available {
            background: #3b8d99;
            color: white;
            font-size: 0.9rem;
        }

        .badge-sold-out {
            background: #d9534f;
            color: white;
            font-size: 0.9rem;
        }

        .modal-header {
            background: linear-gradient(45deg, #3b8d99, #6b6b83);
            color: white;
        }

        .modal-content {
            border-radius: 10px;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin: 20px auto;
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="header">
        <h1><i class="fas fa-seedling"></i> Crop Management</h1>
        <p>Manage your crops and inventory with ease</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Product added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>




    <!-- Search Box -->
    <div class="mb-4">
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="input-group">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search crops..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Crop Name</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = '%' . $_GET['search'] . '%';
                        $stmt = $conn->prepare("SELECT id, name, image, quantity_type FROM products WHERE name LIKE ?");
                        $stmt->bind_param("s", $search);
                    } else {
                        $stmt = $conn->prepare("SELECT id, name, image, quantity_type FROM products");
                    }
                    

                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($product = $result->fetch_assoc()) {
                            ?>
                            <!-- In your table where you list products -->
                            <tr>
                            <tr>
    <td><?= htmlspecialchars($product['id'] ?? ''); ?></td>
    <td><?= htmlspecialchars($product['name'] ?? ''); ?></td>
    <td>
        <img src="<?= htmlspecialchars($product['image'] ?? ''); ?>" 
             alt="Image of <?= htmlspecialchars($product['name'] ?? ''); ?>" 
             width="100" height="100">
    </td>
    <td>
        <button type="button" 
                class="btn btn-primary" 
                onclick="selectCrop(
                    '<?= $product['id'] ?? ''; ?>', 
                    '<?= htmlspecialchars($product['name'] ?? ''); ?>', 
                    '<?= htmlspecialchars($product['quantity_type'] ?? ''); ?>'
                )">
            Select
        </button>
    </td>
</tr>

        </button>
    </td>
</tr>


                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" class="text-center">No crops found</td></tr>';
                    }

                    $stmt->close();
                } catch (Exception $e) {
                    echo '<tr><td colspan="4" class="text-center text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>


<!-- Farmer's Added Crops Section -->
<h3 class="mt-5">My Crop List</h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Crop Name</th>
                    <th>Quantity </th>
                    <th>Price </th>
                   
                   
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch farmer's crops from farmers_crops table
                $farmer_id = $_SESSION['user_id']; // Assuming you have farmer_id in session
                $query = "SELECT fc.*,fc.id, fc.name,fc.image,fc.quantity,fc.quantity_type 
                         FROM farmer_crops fc 
                      
                         WHERE fc.farmer_id = ?
                         ORDER BY fc.id DESC";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $farmer_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                           
                            <td><?php echo htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['quantity_type']); ?></td>
                            <td>taka:<?php echo htmlspecialchars($row['price']); ?></td>
                            
                            
  
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7" class="text-center">No crops added yet</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>





    
<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="addProductForm" class="needs-validation" novalidate>
                    <input type="hidden" id="product_id" name="product_id">
                    <input type="hidden" id="quantity_type" name="quantity_type">
                    <input type="hidden" name="add_product" value="1">
                    
                    <!-- Selected Crop Display -->
                    <div class="mb-3">
                        <label class="form-label">Selected Crop:</label>
                        <div class="form-control-plaintext" id="selected-crop">None</div>
                    </div>

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">
                            Please provide a product name.
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                        <div class="invalid-feedback">
                            Please provide a description.
                        </div>
                    </div>

                    <!-- Price Field -->
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required min="0">
                            <span class="input-group-text quantity-type-label"></span>
                            <div class="invalid-feedback">
                                Please provide a valid price.
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Field -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                            <span class="input-group-text quantity-type-label"></span>
                            <div class="invalid-feedback">
                                Please provide a valid quantity.
                            </div>
                        </div>
                    </div>

                    <!-- Photo Field -->
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Function to open add product modal
function openAddProductModal() {
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();
}


    // Function to handle crop selection
    function selectCrop(productId, cropName, quantityType) {
    console.log('selectCrop called with :', productId, cropName, quantityType);
    
    // Set the product ID and quantity type in the hidden inputs
    document.getElementById('product_id').value = productId;
    document.getElementById('quantity_type').value = quantityType;

    // Update the selected crop name display
    document.getElementById('selected-crop').textContent = cropName;

    // Set the name field with the selected crop name
    document.getElementById('name').value = cropName;

// Update quantity type labels
const quantityTypeLabels = document.getElementsByClassName('quantity-type-label');
const priceDisplayText = quantityType === 'Per-KG' ? '/kg' : '/piece';
const quantityDisplayText = quantityType === 'Per-KG' ? 'kg' : 'pieces';

   // Convert HTMLCollection to Array and update each label
Array.from(quantityTypeLabels).forEach((label, index) => {
    // First label is for price, second is for quantity
    if (index === 0) {
        label.textContent = priceDisplayText;
    } else {
        label.textContent = quantityDisplayText;
    }
});

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();
}


    // Form submission handling
    document.addEventListener('DOMContentLoaded', function() {
        const addProductForm = document.getElementById('addProductForm');
        
        if (addProductForm) {
            addProductForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate form
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }
                
                const formData = new FormData(this);
                
                // Show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.innerHTML = 'Adding...';
                submitButton.disabled = true;

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;

                    if (data.status === 'success') {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                        modal.hide();
                        
                        // Reset form
                        addProductForm.reset();
                        document.getElementById('selected-crop').textContent = 'None';
                        
                        // Show success message
                        alert(data.message);
                        
                        // Refresh the page
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                    alert('An error occurred. Please try again.');
                });
            });
        }
    });

    // Debug logging
    console.log('Script loaded');
    console.log('Modal element:', document.getElementById('addProductModal'));
    console.log('Form element:', document.getElementById('addProductForm'));
</script>
</body>
</html>