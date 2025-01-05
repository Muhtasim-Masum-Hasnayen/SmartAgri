<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartAgri - Farming Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f8ff; /* Light blue background */
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            background-color: #333;
            overflow: hidden;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            text-align: center;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .hero {
            background: url('R (2).jpeg') no-repeat center center/cover;
            height: 400px;
            color: black;
            text-align: center;
            /*display: flex;*/
            justify-content: center;
            align-items: center;
        }
        .hero h1 {
            font-size: 48px;
            background-color: rgba(255, 255, 255, 0.8); /* Light background for text readability */
            padding: 10px 20px;
            border-radius: 5px;
        }
        .container {
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .card button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .card button:hover {
            background-color: #45a049;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>AgriSmart : Agricultural Management System</h1>
        <p>Efficient Farming, Better Management!</p>
    </header>
    <nav>
        <a href="dashboard.php">Home</a>
        <a href="blog.php">Blog</a>
        <a href="login.php">Login</a>
        <a href="contact.php">Contact</a>
    </nav>
    <div class="hero">

    </div>
    <div class="container">
        <h2>Explore Agricultural Management System</h2>
        <p>Log in to access your personalized dashboard and manage your account effectively.</p>
    </div>
    <footer>
        <p>&copy; 2025 Agricultural Management System. All rights reserved.</p>
    </footer>
</body>
</html>