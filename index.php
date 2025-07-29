<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Hall Seating Arrangement</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #4facfe, #00f2fe); /* Premium gradient background */
        }

        .container {
            text-align: center;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            max-width: 400px;
            width: 90%;
        }

        h1 {
            font-size: 2rem;
            color: #0D47A1;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 1.5rem;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background: linear-gradient(135deg, #0D47A1, #2196F3); /* Gradient button */
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .button:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        footer {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #888;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Exam Hall Seating Arrangement</h1>
        <p>Please select your login type:</p>
        <div>
            <a href="login_student.php" class="button">Student Login</a>
            <a href="login_admin.php" class="button">Admin Login</a>
        </div>
        <footer>&copy; 2025 Exam Management System</footer>
    </div>
</body>
</html>
