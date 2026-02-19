<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Being Petz Account</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        /* Header with Purple Theme */
        .header {
            background: linear-gradient(135deg, rgb(131, 55, 178) 0%, rgb(105, 44, 143) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .header p {
            opacity: 0.9;
            font-size: 12px;
        }

        /* Content Area */
        .content {
            padding: 30px;
            background-color: #f6eafe;
        }

        .welcome-message {
            margin-bottom: 25px;
            font-size: 18px;
            color: #555;
        }

        /* Credentials Card */
        .credentials-card {
            background: linear-gradient(135deg, #f9f3ff 0%, #f0e6ff 100%);
            border-left: 5px solid rgb(131, 55, 178);
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 3px 10px rgba(131, 55, 178, 0.1);
        }

        .credentials-card h3 {
            color: rgb(131, 55, 178);
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .credentials-card h3::before {
            content: "🔐";
            font-size: 24px;
        }

        .credential-item {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0d6ff;
        }

        .credential-label {
            font-weight: bold;
            color: rgb(0, 0, 0);
            min-width: 120px;
        }

        .credential-value {
            color: #333;
            font-family: 'Courier New', monospace;
            padding: 5px 10px;
            background: #f8f8f8;
            border-radius: 4px;
            flex-grow: 1;
        }

        /* Role Badge */
        .role-badge {
            display: inline-block;
            background: rgb(131, 55, 178);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Important Note */
        .important-note {
            background: #fff8e1;
            border: 2px solid #ffd54f;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            position: relative;
        }

        .important-note::before {
            content: "⚠️";
            position: absolute;
            top: -15px;
            left: 20px;
            background: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 20px;
        }

        .important-note h4 {
            color: #d84315;
            margin-bottom: 10px;
            font-size: 16px;
        }

        /* Login Button */
        .login-section {
            text-align: center;
            margin: 30px 0;
        }

        .login-btn {
            display: inline-block;
            background: linear-gradient(135deg, rgb(131, 55, 178) 0%, rgb(105, 44, 143) 100%);
            color: white !important;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(131, 55, 178, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(131, 55, 178, 0.4);
        }

        /* Footer */
        .footer {
            background: #f5f5f5;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .footer p {
            color: #666;
            font-size: 12px;
            margin: 5px 0;
        }

        .contact-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .credential-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .credential-label {
                min-width: auto;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1> Welcome to Being Petz!</h1>
            <p>Your trusted partner for pet care</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="welcome-message">
                <p>Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,</p>
                <p>We're excited to welcome you to Being Petz! Your account has been successfully created.</p>
            </div>

            <!-- Credentials Card -->
            <div class="credentials-card">
                <h3>Your Login Credentials</h3>

                <div class="credential-item">
                    <div class="credential-label">Email:</div>
                    <div class="credential-value">{{ $user->email }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label"> Password:</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>

                <div style="margin-top: 20px;">
                    <span class="role-badge">
                        {{ ucfirst($user->role) }} Account
                    </span>
                </div>
            </div>



            <!-- Login Section -->
            <div class="login-section">
                <p style="margin-bottom: 20px; color: #555;">Click the button below to access your account:</p>
                <a href="{{ url('/admin/login') }}" class="login-btn">Login to Your Account</a>

            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>© {{ date('Y') }} Being Petz. All rights reserved.</strong></p>

        </div>
    </div>
</body>

</html>
