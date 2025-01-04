<?php
include('database.php'); // Include your database connection file

$query = isset($_GET['query']) ? $_GET['query'] : '';
$response = [];

if (!empty($query)) {
    // Make the query case-insensitive by converting both the name and search term to lowercase
    $sql = "SELECT id, name FROM products WHERE LOWER(name) LIKE LOWER(?)";
    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $query . '%'; // Prepare search term with wildcards
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row; // Add each matching product to the response
    }
}

echo json_encode($response); // Return the response as JSON
?>
