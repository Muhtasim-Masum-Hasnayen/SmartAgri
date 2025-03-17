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
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 400px;
            margin: 5% auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #555;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #4CAF50;
            box-shadow: 0px 0px 8px rgba(76, 175, 80, 0.5);
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s ease-in-out;
        }

        .form-group input[type="submit"]:hover {
            background-color: #388E3C;
        }

        .success {
            color: #4CAF50;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .error {
            color: #f44336;
            margin-bottom: 15px;
            font-weight: bold;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #fff;
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
                    <option value="customer">Customer</option>
                </select>
            </div>

            <div class="form-group">
                <input type="submit" value="Register">
            </div>
        </form>
    </div>

    <footer>
        &copy; <?= date('Y'); ?> Agricultural Management System. All rights reserved.
    </footer>
</body>
</html>
