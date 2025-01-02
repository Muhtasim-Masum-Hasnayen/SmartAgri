<?php
include 'database.php'; // Include your database connection file

// Initialize variables to hold success and error messages
$success_message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $start_date = date('Y-m-d');

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email or phone number already exists
        $check_sql = "SELECT * FROM users WHERE email = ? OR phone_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $email, $phone_number);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email or Phone Number already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL statement
            $sql = "INSERT INTO users (name, email, phone_number, password, role, start_date) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $email, $phone_number, $hashed_password, $role, $start_date);

            if ($stmt->execute()) {
                // Registration successful, set success message
                $success_message = "Registration successful! You can now log in.";
                // Redirect to login page after a short delay
                header("refresh:3;url=login.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-image: url('reg_page_converted.jpg'); /* Reference the image file */
        background-size: cover; /* Make sure the image covers the entire background */
        background-repeat: no-repeat; /* Avoid repeating the image */
        background-position: center; /* Center the image */
        padding: 20px;
    }
    .form-container {
        max-width: 300px;
        margin: auto;
        background: lightgreen;
        padding: 20px;
        border-radius: 50px;
        box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.1);
    }
    .form-group {
        margin-bottom: 1px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group input,
    .form-group select {
        width: 90%;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 50px;
    }
    .form-group input[type="submit"] {
        background-color: #5cb85c;
        color: white;
        border: none;
        cursor: pointer;
    }
    .form-group input[type="submit"]:hover {
        background-color: #4cae4c;
    }
    .success {
        color: green;
        margin-bottom: 15px;
    }
    .error {
        color: red;
        margin-bottom: 15px;
    }
</style>
</head>
<body>

<div class="form-container">
    <h2>User Registration</h2>

    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>
        
        <div class="form-group">
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="farmer" selected>Farmer</option>
                <option value="supplier">Supplier</option>
                <option value="investor">Investor</option>
                <option value="Labour">Labour</option>
                <option value="customer">customer</option>
            </select>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Register">
        </div>
    </form>
</div>

</body>
</html>
