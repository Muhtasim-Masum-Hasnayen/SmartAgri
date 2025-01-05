<?php
include('database.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize messages array
$messages = [];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if (isset($_POST['add_product'])) {
    // Validate required fields
    if (empty($_POST['product_id'])) {
        $messages[] = ["type" => "danger", "text" => "Please select a crop first."];
    } elseif (empty($_POST['description'])) {
        $messages[] = ["type" => "danger", "text" => "Please provide a description."];
    } elseif (empty($_POST['price'])) {
        $messages[] = ["type" => "danger", "text" => "Please provide a price."];
    } elseif (empty($_POST['quantity'])) {
        $messages[] = ["type" => "danger", "text" => "Please provide a quantity."];
    } else {
        $product_id = $_POST['product_id'];
        $farmer_id = $_SESSION['user_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $messages[] = ["type" => "danger", "text" => "Failed to upload image."];
                }
            } else {
                $messages[] = ["type" => "danger", "text" => "File is not an image."];
            }
        }
        
        try {
            // Insert into farmer_crops table
            $stmt = $conn->prepare("INSERT INTO farmer_crops (farmer_id, product_id, description, price, quantity, image, status) VALUES (?, ?, ?, ?, ?, ?, 'available')");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("iisdis", $farmer_id, $product_id, $description, $price, $quantity, $image_path);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Crop added successfully!";
                header("Location: farmer.php");
                exit();
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $messages[] = ["type" => "danger", "text" => "Error: " . $e->getMessage()];
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Display success message from session if it exists
if (isset($_SESSION['success_message'])) {
    $messages[] = ["type" => "success", "text" => $_SESSION['success_message']];
    unset($_SESSION['success_message']);
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
        .input-group {
            max-width: 500px;
            margin: 0 auto;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Crop Management</h1>

        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $message['text']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <!-- Search Box -->
        <div class="mb-4">
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
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
                            $stmt = $conn->prepare("SELECT id, name, image FROM products WHERE name LIKE ?");
                            $stmt->bind_param("s", $search);
                        } else {
                            $stmt = $conn->prepare("SELECT id, name, image FROM products");
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($product = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="img-thumbnail" style="max-width: 100px;">
                                        <?php else: ?>
                                            No Photo
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm select-crop-btn" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-crop-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>">
                                            Select
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
                        echo '<tr><td colspan="4" class="text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add Product Form -->
        <div class="form-container mt-4">
            <h2>Add Product for Sale</h2>
            <p><strong>Selected Crop:</strong> <span id="selected-crop">None</span></p>
            
            <form method="POST" action="" enctype="multipart/form-data" id="product-form" class="needs-validation" novalidate>
                <input type="hidden" name="product_id" id="product_id" value="">
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea name="description" id="description" class="form-control" required></textarea>
                    <div class="invalid-feedback">Please provide a description.</div>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
                    <div class="invalid-feedback">Please provide a valid price.</div>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity (in kg):</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                    <div class="invalid-feedback">Please provide a valid quantity.</div>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Photo:</label>
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                    <img id="preview" class="preview-image">
                </div>

                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attach select button listeners
        document.querySelectorAll('.select-crop-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const cropName = this.getAttribute('data-crop-name');
                
                // Update the selected crop display
                document.getElementById('product_id').value = productId;
                document.getElementById('selected-crop').textContent = cropName;
                
                // Scroll to the form
                document.querySelector('.form-container').scrollIntoView({ 
                    behavior: 'smooth' 
                });
            });
        });

        // Image preview
        document.getElementById('photo').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        const form = document.getElementById('product-form');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Check if a crop is selected
            const productId = document.getElementById('product_id').value;
            if (!productId) {
                event.preventDefault();
                alert('Please select a crop first!');
                return false;
            }
            
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html>
