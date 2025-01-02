<?php
session_start(); // Start the session
include 'database.php'; // Include the database connection file

$error = ''; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number']; // Use phone number as username
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $sql = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['name']; // Assuming 'name' is the user's name in the database
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            $role = $user['role']; // Fetch the role from the database
            if ($role === 'Admin') {
                header('Location: admin.php');
            } elseif ($role === 'Farmer') {
                header('Location: farmer.php');
            } elseif ($role === 'Customer') {
                header('Location: customer.php');
            } elseif ($role === 'Investor') {
                header('Location: investor.php');
            }elseif ($role === 'Supplier') {
                header('Location: supplier.php');
            }
            elseif ($role === 'Labour') {
                header('Location: labour.php');
            } else {
                echo "Invalid role selected.";
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that phone number.";
    }
}
?>

<!-- HTML Form for User Login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgriBuzz</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-image: url('farmland-conversion.jpg'); /* Path to your image */
        background-size: cover; /* Cover the screen with the background image */
        background-position: center; /* Center the image */
        background-repeat: no-repeat; /* Prevent tiling */
        padding: 100px;
    }
    
    .form-container {
        max-width: 300px;
        margin: auto;
        background: rgba(255, 255, 255, 0.9); /* Adds a white background with slight transparency */
        padding: 30px;
        border-radius: 3px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
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
    .error {
        color: red;
        margin-bottom: 15px;
    }
    .register-link {
        margin-top: 15px;
        text-align: center;
    }
    .register-link a {
        color: #5cb85c;
        text-decoration: none;
    }
    .register-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="form-container">
    <h2>User Login</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
    
    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

</body>
</html>
