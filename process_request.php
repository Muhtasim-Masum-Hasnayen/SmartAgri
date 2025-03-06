<?php
include('database.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    // Update status based on action
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    $query = "UPDATE product_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("si", $status, $requestId);
        if ($stmt->execute()) {
            // If approved, insert the product into the products table
            if ($action === 'approve') {
                $selectQuery = "SELECT farmer_id, product_name,quantity_type, product_image FROM product_requests WHERE id = ?";
                $selectStmt = $conn->prepare($selectQuery);
                $selectStmt->bind_param("i", $requestId);
                $selectStmt->execute();
                $result = $selectStmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    // Insert approved product into products table
                    $insertQuery = "INSERT INTO products ( name, image,quantity_type) VALUES (?,?, ?)";
                    $insertStmt = $conn->prepare($insertQuery);
                    if ($insertStmt) {
                        $insertStmt->bind_param("sss", $row['product_name'], $row['product_image'], $row['quantity_type']);
                        if ($insertStmt->execute()) {
                            header('Location: admin.php?message=Product approved and added successfully');
                            exit();
                        } else {
                            echo "Error adding product to products table: " . $insertStmt->error;
                        }
                    } else {
                        echo "Error preparing insert statement: " . $conn->error;
                    }
                } else {
                    echo "Error fetching product request details: " . $selectStmt->error;
                }
            } else {
                // Redirect for rejected requests
                header('Location: admin.php?message=Request rejected successfully');
                exit();
            }
        } else {
            echo "Error updating request: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>
