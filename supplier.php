[Supplier Registration Page (To add a supplier to the platform)]
<?php
// Database connection

session_start();
include 'database.php'; // Include the database connection


// Handle form submission to register a supplier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    $sql = "INSERT INTO Suppliers (name, email, phone_number, address) VALUES ('$name', '$email', '$phone_number', '$address')";

    if ($conn->query($sql) === TRUE) {
        echo "Supplier registered successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Registration</title>
</head>
<body>
    <h2>Supplier Registration</h2>
    <form method="POST" action="">
        Name: <input type="text" name="name" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Phone Number: <input type="text" name="phone_number" required><br><br>
        Address: <textarea name="address" required></textarea><br><br>
        <input type="submit" value="Register Supplier">
    </form>
</body>
</html>


[Add Product Page (To add fertilizers or machines)]
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to add a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'];
    $product_name = $_POST['product_name'];
    $product_type = $_POST['product_type'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO Products (supplier_id, product_name, product_type, description, price, quantity) 
            VALUES ('$supplier_id', '$product_name', '$product_type', '$description', '$price', '$quantity')";

    if ($conn->query($sql) === TRUE) {
        echo "Product added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
</head>
<body>
    <h2>Add Product</h2>
    <form method="POST" action="">
        Supplier ID: <input type="number" name="supplier_id" required><br><br>
        Product Name: <input type="text" name="product_name" required><br><br>
        Product Type: 
        <select name="product_type" required>
            <option value="Fertilizer">Fertilizer</option>
            <option value="Machine">Machine</option>
        </select><br><br>
        Description: <textarea name="description" required></textarea><br><br>
        Price: <input type="number" name="price" step="0.01" required><br><br>
        Quantity: <input type="number" name="quantity" required><br><br>
        <input type="submit" value="Add Product">
    </form>
</body>
</html>
