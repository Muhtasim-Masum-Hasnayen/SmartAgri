<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Agricultural Management System</title>
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
        .video-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
            margin-top: 20px;
        }
        .video-container iframe {
            border: none;
        }
        .video-card {
            width: 560px;
            height: 315px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to the Blog</h1>

    </header>
    <nav>
                <a href="dashboard.php">Home</a>
                <a href="login.php">Login</a>
                <a href="contact.php">Contact</a>
            </nav>

    <div class="hero">
        <!-- You can add an image or other content here -->
    </div>

    <div class="container">
        <h2>Explore Agricultural Topics Through Videos</h2>
        <p>Check out these informative videos on agriculture to learn more!</p>

        <div class="video-container">
            <!-- Video 1 -->
            <div class="video-card">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/JeU_EYFH1Jk" allowfullscreen></iframe>
            </div>

            <!-- Video 2 -->
            <div class="video-card">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/Vf_shMr3pbw" allowfullscreen></iframe>
            </div>

            <!-- Video 3 -->
            <div class="video-card">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/jo8Joe8XOB4" allowfullscreen></iframe>
            </div>

            <!-- Video 4 -->
            <div class="video-card">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/0BxQSe9pHrY" allowfullscreen></iframe>
            </div>

            <!-- Video 5 -->
            <div class="video-card">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/4ZGoTTwKUCY" allowfullscreen></iframe>
            </div>

             <!-- Video 6 -->
                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/FzFH9QNFv5Y" allowfullscreen></iframe>
                        </div>

                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/0IIgtlsJMY0" allowfullscreen></iframe>
                        </div>

                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/TfZVew-qzAM" allowfullscreen></iframe>
                        </div>

                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/ERS1RvMVyQk" allowfullscreen></iframe>
                        </div>

                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/aDF3Khvhlpg" allowfullscreen></iframe>
                        </div>

                        <div class="video-card">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/iwl58ID80Vs" allowfullscreen></iframe>
                        </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Agricultural Management System. All rights reserved.</p>
    </footer>
</body>
</html>
