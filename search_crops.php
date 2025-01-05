<?php
include('database.php');
session_start();

file_put_contents('debug.txt', date('Y-m-d H:i:s') . ': Search request received with term: ' . 
    (isset($_POST['search']) ? $_POST['search'] : 'no term') . PHP_EOL, FILE_APPEND);



// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

// Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';
    
    try {
        // Debug
        error_log("Search term: " . $_POST['search']);
        
        $stmt = $conn->prepare("SELECT id, name, image FROM products WHERE name LIKE ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $search);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        // Debug
        error_log("Number of results: " . $result->num_rows);

        if ($result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($product['id']) . '</td>';
                echo '<td>' . htmlspecialchars($product['name']) . '</td>';
                echo '<td>';
                if (!empty($product['image'])) {
                    echo '<img src="' . htmlspecialchars($product['image']) . '" 
                              alt="' . htmlspecialchars($product['name']) . '"
                              class="img-thumbnail" style="max-width: 100px;">';
                } else {
                    echo 'No Photo';
                }
                echo '</td>';
                echo '<td>';
                echo '<button class="btn btn-primary btn-sm select-crop-btn" 
                        data-product-id="' . $product['id'] . '"
                        data-crop-name="' . htmlspecialchars($product['name'], ENT_QUOTES) . '">
                        Select
                      </button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" class="text-center">No crops found</td></tr>';
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Debug
        error_log("Error in search_crops.php: " . $e->getMessage());
        echo '<tr><td colspan="4" class="text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center">No search term provided</td></tr>';
}
?>
