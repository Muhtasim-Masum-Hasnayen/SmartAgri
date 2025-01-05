<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Agricultural Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
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
        .container {
            padding: 20px;
        }
        .creator {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .creator img {
            border-radius: 50%;
            width: 300px;
            height: 300px;
            margin-right: 20px;
        }
        .creator-info {
            flex: 1;
        }
        .creator-info h3 {
            margin: 0;
            font-size: 1.5em;
        }
        .creator-info p {
            margin: 5px 0;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Contact Us</h1>
        <p>Get in touch with the creators of the Agricultural Management System</p>
    </header>
    <nav>
            <a href="dashboard.php">Home</a>
            <a href="blog.php">Blog</a>
            <a href="login.php">Login</a>
            <a href="contact.php">Contact</a>
        </nav>
    <div class="container">
        <div class="creator">
            <img src="mmh.jpg" alt="Muhtasim Masum Hasnayen">
            <div class="creator-info">
                <h3>Muhtasim Masum Hasnayen</h3>
                <p><strong>Phone:</strong> 01730202960</p>
                <p><strong>Email:</strong> <a href="mailto:hasnayenmasum@gmail.com">hasnayenmasum@gmail.com</a></p>
                <p><strong>Facebook:</strong> <a href="https://www.facebook.com/mh.masum.908" target="_blank">Muhtasim's Facebook</a></p>
            </div>
        </div>

        <div class="creator">
            <img src="Hasibur.jpg" alt="Md Hasibur Rahman">
            <div class="creator-info">
                <h3>Md Hasibur Rahman</h3>
                <p><strong>Phone:</strong> 01580491525</p>
                <p><strong>Email:</strong> <a href="mailto:hasnayenmasum@gmail.com">hasibur@gmail.com</a></p>
                <p><strong>Facebook:</strong> <a href="https://www.facebook.com/hasibur.rahmam.77" target="_blank">Hasibur's Facebook</a></p>
            </div>
        </div>

        <div class="creator">
            <img src="AnikDeb.jpg" alt="Third Creator">
            <div class="creator-info">
                <h3>Anik Debnath Shuvo</h3>
                <p><strong>Phone:</strong> 01780529775</p>
                <p><strong>Email:</strong> <a href="mailto:[Email Address]">ashuvo223401@bscse.uiu.ac.bd</a></p>
                <p><strong>Facebook:</strong> <a href="https://www.facebook.com/anildebnath.shuvo" target="_blank">Anik's Facebook</a></p>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Agricultural Management System. All rights reserved.</p>
    </footer>
</body>
</html>
