<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Agricultural Management System</title>
    <style>
        /* General Styling */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom right, #e3f2fd, #bbdefb);
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: linear-gradient(to right, #4caf50, #8bc34a);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-bottom: 5px solid #2e7d32;
        }
        header h1 {
            font-size: 2.5rem;
            margin: 0;
            font-weight: 700;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
        }

        /* Navigation Bar */
        nav {
            background: linear-gradient(to right, #2e7d32, #388e3c);
            padding: 15px 0;
            display: flex;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            font-size: 1.1rem;
            padding: 8px 15px;
            border-radius: 25px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        nav a:hover {
            background: white;
            color: #4caf50;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(to bottom right, #81c784, #aed581);
            color: white;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.2);
        }
        .hero h2 {
            font-size: 2.8rem;
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        .hero p {
            font-size: 1.3rem;
            margin-top: 15px;
            font-weight: 300;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .container h2 {
            font-size: 2rem;
            color: #2e7d32;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
        }
        .container p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Video Cards */
        .video-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .video-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .video-card iframe {
            width: 100%;
            height: 200px;
            border: none;
        }

        /* Footer */
        footer {
            background: linear-gradient(to right, #4caf50, #8bc34a);
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 1rem;
        }
        footer a {
            color: #aed581;
            text-decoration: none;
            font-weight: bold;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Agricultural Management System</h1>
    </header>
    <nav>
        <a href="dashboard.php">Home</a>
        <a href="login.php">Login</a>
        <a href="contact.php">Contact</a>
    </nav>

    <div class="hero">
        <h2>Innovative Solutions for Modern Farming</h2>
        <p>Explore the latest trends and technologies shaping the future of agriculture.</p>
    </div>

    <div class="container">
        <h2>Explore Agricultural Topics Through Videos</h2>
        <p>Learn more about sustainable practices and modern farming techniques with these curated videos.</p>

        <div class="video-container">
            <!-- Video Cards -->
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/JeU_EYFH1Jk" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/Vf_shMr3pbw" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/jo8Joe8XOB4" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/0BxQSe9pHrY" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/4ZGoTTwKUCY" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/ERS1RvMVyQk" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/aDF3Khvhlpg" allowfullscreen></iframe>
            </div>
            <div class="video-card">
                <iframe src="https://www.youtube.com/embed/iwl58ID80Vs" allowfullscreen></iframe>
            </div>


        </div>
    </div>

    <footer>
        <p>&copy; 2025 Agricultural Management System. All rights reserved.</p>
        <p><a href="privacy-policy.html">Privacy Policy</a> | <a href="terms-of-service.html">Terms of Service</a></p>
    </footer>
</body>
</html>
