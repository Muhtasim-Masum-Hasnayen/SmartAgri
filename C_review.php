<?php
session_start();
include('database.php');

// Check if the customer is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$customer_id = $_SESSION['user_id']; // Logged-in customer ID

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];
    $farmer_id = $_POST['farmer_id'];

    // Validate if the product is eligible for review
    $query = "
        SELECT COUNT(*) AS eligible
        FROM orders o
        LEFT JOIN product_reviews pr ON o.product_id = pr.product_id AND o.customer_id = pr.customer_id
        WHERE o.customer_id = ? AND o.product_id = ? AND o.status = 'Delivered' AND pr.id IS NULL
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['eligible'] > 0) {
        // Insert the review
        $stmt = $conn->prepare("
            INSERT INTO product_reviews (customer_id, farmer_id, product_id, rating, review)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiis", $customer_id, $farmer_id, $product_id, $rating, $review);
        if ($stmt->execute()) {
            $success_message = "Review submitted successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    } else {
        $error_message = "You cannot review this product.";
    }
}


// Fetch Eligible Products for Review
$query = "
    SELECT DISTINCT o.product_id,f.farmer_id, fc.name AS product_name, S.name AS farmer_name
    FROM orders o
    INNER JOIN farmer_crops fc ON o.product_id = fc.product_id
    INNER JOIN farmer f ON fc.farmer_id = f.farmer_id
    JOIN USERS S ON f.farmer_id = S.user_id
    LEFT JOIN product_reviews pr ON o.product_id = pr.product_id AND o.customer_id = pr.customer_id
    WHERE o.customer_id = ? AND o.status = 'Delivered' AND pr.id IS NULL
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$eligible_products = [];
while ($row = $result->fetch_assoc()) {
    $eligible_products[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Products</title>
   <!-- jQuery first -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Then Select2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .review-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="review-container">
    <h2>Review Your Products</h2>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="message success"><?= $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="message error"><?= $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="C_review.php">
        <div class="form-group">
            <label for="product_id">Select Product:</label>
            <select id="product_id" name="product_id" style="width: 100%;" required>
                <option value="" disabled selected>Select a product...</option>
                <?php foreach ($eligible_products as $product): ?>
                    <option value="<?= $product['product_id']; ?>" 
                            data-farmer-id="<?= $product['farmer_id']; ?>">
                        <?= $product['product_name']; ?> 
                        (Farmer: <?= $product['farmer_name']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <input type="hidden" id="farmer_id" name="farmer_id">

        <div class="form-group">
            <label for="rating">Rating (1-5):</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required>
        </div>

        <div class="form-group">
            <label for="review">Your Review:</label>
            <textarea id="review" name="review" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn">Submit Review</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Check if Select2 is loaded
        if (typeof $.fn.select2 === 'undefined') {
            console.error('Select2 is not loaded!');
            return;
        }

        // Initialize Select2
        $('#product_id').select2({
            placeholder: 'Select a product...'
        });

        // Update farmer_id hidden field when product is selected
        $('#product_id').on('change', function() {
            const farmerId = $(this).find(':selected').data('farmer-id');
            $('#farmer_id').val(farmerId);
        });
    });
</script>
</body>
</html>
