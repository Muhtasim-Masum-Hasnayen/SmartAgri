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
                header('Location: admin/admin.php');
            } elseif ($role === 'Farmer') {
                header('Location: farmer.php');
            } elseif ($role === 'Customer') {
                header('Location: customer.php');
            } elseif ($role === 'Investor') {
                header('Location: investor.php');
            } elseif ($role === 'Supplier') {
                header('Location: supplier.php');
            } elseif ($role === 'Labour') {
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
    <title>Login - SmartAgri</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to bottom right, #e3f2fd, #c8e6c9); /* Professional gradient background */
        padding: 100px;
        margin: 0;
    }

    .form-container {
        max-width: 400px;
        margin: auto;
        background: linear-gradient(to right, #4caf50, #8bc34a); /* Initial gradient */
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        animation: bounceAnimation 1s ease-in-out infinite, gradientAnimation 5s ease infinite; /* Apply both animations */
    }

    /* Bounce animation */
    @keyframes bounceAnimation {
        0% {
            transform: translateY(0);
        }
        20% {
            transform: translateY(-10px);
        }
        40% {
            transform: translateY(0);
        }
        60% {
            transform: translateY(-5px);
        }
        80% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(0);
        }
    }


    }

    .form-container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: white; /* Text color to contrast with the gradient */
    }

    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #33;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #cc;
        border-radius: 5px;
    }
    .form-group input[type="submit"] {
        background-color: #8BC34A;
        color: white;
        border: 1px;
        cursor: pointer;
        transition: background-color 0.6s;
    }
    .form-group input[type="submit"]:hover {
        background-color: #45a049;
    }
    .error {
        color: red;
        margin-bottom: 15px;
        text-align: center;
    }
    .register-link {
        margin-top: 15px;
        text-align: center;
    }
    .register-link a {
        color: #ffffff;
        text-decoration: none;
    }
    .register-link a:hover {
        text-decoration: underline;
    }
    .back-link {
        text-align: center;
        margin-top: 20px;
    }
    .back-link a {
        color: #ffffff;
        text-decoration: none;
    }
    .back-link a:hover {
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

    <!-- Back to Dashboard Link -->
    <div class="back-link">
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</div>

</body>
</html>
