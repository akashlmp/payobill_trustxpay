<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            text-align: center;
        }
        h1 {
            font-size: 6rem;
            margin: 0;
        }
        p {
            font-size: 1.5rem;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #0078d7;
            color: white;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        a:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>401</h1>
        <p>Unauthorized Access</p>
        <p>You do not have permission to view this page.</p>
        <a href="{{ url()->previous() }}">Go Back to Home</a>
    </div>
</body>
</html>
