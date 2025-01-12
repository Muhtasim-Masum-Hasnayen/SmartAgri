<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartAgri - Farming Management System</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e3f2fd, #f1f8e9);
            color: #333;
        }
        header {
            background: linear-gradient(90deg, #43cea2, #185a9d);
            color: white;
            padding: 50px 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-bottom: 5px solid #1e88e5;
        }
        header h1 {
            margin: 0;
            font-size: 3em;
            letter-spacing: 1px;
        }
        header p {
            font-size: 1.2em;
            margin-top: 10px;
        }
        nav {
            background-color: #333;
            display: flex;
            justify-content: center;
            padding: 15px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            margin: 0 10px;
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.3s;
        }
        nav a:hover {
            background-color: #43cea2;
            transform: scale(1.05);
        }
        .hero {
            background: url('R (2).jpeg') no-repeat center center/cover;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.5);
        }
        .hero h1 {
            font-size: 3em;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .container {
            padding: 50px 20px;
            text-align: center;
        }
        .container h2 {
            font-size: 2.5em;
            color: #185a9d;
            margin-bottom: 20px;
        }
        .container p {
            font-size: 1.2em;
            margin: 20px 0;
        }
        .card {
            border: none;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            background: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .card button {
            background: linear-gradient(90deg, #43cea2, #185a9d);
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 25px;
            font-size: 1em;
            transition: background 0.3s, transform 0.3s;
        }
        .card button:hover {
            background: #43cea2;
            transform: scale(1.05);
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            font-size: 0.9em;
        }
        footer p {
            margin: 0;
        }
        @media (max-width: 768px) {
            .hero {
                height: 300px;
            }
            .hero h1 {
                font-size: 2em;
            }
            header h1 {
                font-size: 2.5em;
            }
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
        <h1>Welcome to SmartAgri</h1>
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
