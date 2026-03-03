<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - {{ config('app.name', 'Being Petz') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --bg: #f9fafb;
            --text: #1f2937;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .icon {
            font-size: 80px;
            margin-bottom: 1.5rem;
            color: var(--primary);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            font-size: 1.125rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🛠️</div>
        <div class="badge">Scheduled Maintenance</div>
        <h1>We'll be back soon!</h1>
        <p>Being Petz is currently undergoing scheduled maintenance to improve our services. We apologize for the inconvenience and appreciate your patience.</p>
        <div style="font-size: 0.875rem; color: #9ca3af;">
            &copy; {{ date('Y') }} {{ config('app.name', 'Being Petz') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
