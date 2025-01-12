<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Agricultural Management System</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f3f4f6, #d1e8e4);
            color: #333;
        }
        header {
            background: linear-gradient(90deg, #43cea2, #185a9d);
            color: white;
            padding: 50px 20px;
            text-align: center;
            border-bottom: 5px solid #1e88e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        .container {
            padding: 40px 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }
        .creator {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
            width: 300px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .creator:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .creator img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid #43cea2;
        }
        .creator-info {
            text-align: center;
        }
        .creator-info h3 {
            margin: 10px 0;
            font-size: 1.6em;
            color: #185a9d;
        }
        .creator-info p {
            margin: 8px 0;
            line-height: 1.5;
            font-size: 1em;
        }
        .creator-info a {
            color: #43cea2;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .creator-info a:hover {
            color: #185a9d;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            font-size: 0.9em;
        }
        footer p {
            margin: 0;
        }
        @media (max-width: 768px) {
            .creator {
                width: 90%;
            }
            header h1 {
                font-size: 2.5em;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Contact Us</h1>
        <p>Connect with the creators of the Agricultural Management System</p>
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
                <p><strong>Email:</strong> <a href="mailto:hasibur@gmail.com">hasibur@gmail.com</a></p>
                <p><strong>Facebook:</strong> <a href="https://www.facebook.com/hasibur.rahmam.77" target="_blank">Hasibur's Facebook</a></p>
            </div>
        </div>

        <div class="creator">
            <img src="AnikDeb.jpg" alt="Anik Debnath Shuvo">
            <div class="creator-info">
                <h3>Anik Debnath Shuvo</h3>
                <p><strong>Phone:</strong> 01780529775</p>
                <p><strong>Email:</strong> <a href="mailto:ashuvo223401@bscse.uiu.ac.bd">ashuvo223401@bscse.uiu.ac.bd</a></p>
                <p><strong>Facebook:</strong> <a href="https://www.facebook.com/anildebnath.shuvo" target="_blank">Anik's Facebook</a></p>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Agricultural Management System. All rights reserved.</p>
    </footer>
</body>
</html>
