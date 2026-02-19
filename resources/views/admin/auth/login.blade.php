<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Login Page</title>
    <!-- Material Design Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, rgb(131, 55, 178) 0%, rgb(209, 147, 248) 50%, #f2f2f2 100%);
            padding: 20px;
        }

        .container {
            display: flex;
            width: 850px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .image-section {
            flex: 1;
            background:
                url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            color: rgb(131, 55, 178);
        }

        h2 {
            margin-bottom: 10px;
            color: #333;
            font-size: 32px;
            font-weight: 700;
        }

        .subtitle {
            color: #777;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .input-group input:focus {
            border-color: rgb(131, 55, 178);
            outline: none;
        }

        /* Password toggle button styling */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: rgb(131, 55, 178);
            background: rgba(241, 117, 14, 0.1);
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: rgb(131, 55, 178);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .forgot-password a:hover {
            color: rgb(131, 55, 178);
            text-decoration: underline;
        }

        .login-btn {
            background-color: rgb(131, 55, 178);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(241, 117, 14, 0.4);
            background: linear-gradient(to right, rgb(220, 166, 254), rgb(170, 77, 227));
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #777;
        }

        .signup-link a {
            color: rgb(131, 55, 178);
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .image-section h3 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .image-section p {
            font-size: 16px;
            max-width: 300px;
            line-height: 1.6;
        }

        /* Error message styling */
        .invalid-feedback {
            display: block;
            color: rgb(131, 55, 178);
            font-size: 14px;
            margin-top: 5px;
        }

        .is-invalid {
            border-color: rgb(131, 55, 178);
            !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
        }

        /* Success/Error alerts */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: rgb(131, 55, 178);
            color: #721c24;
            border: 1px solid rgb(131, 55, 178);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 100%;
            }

            .image-section {
                padding: 40px 20px;
                min-height: 200px;
            }

            .login-section {
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-section">
            <h2>LOGIN</h2>
            <p class="subtitle">Please sign in to your account</p>

            <!-- Display Messages -->
            <div id="messageContainer"></div>

            <form method="POST" action="{{ route('admin.loginSubmit') }}" class="form-horizontal mt-4 pt-2"
                id="loginForm">
                @csrf
                <div class="input-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" class="@error('email') is-invalid @enderror" name="email"
                        value="{{ old('email') }}" required autocomplete="email" autofocus
                        placeholder="Enter your email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="@error('password') is-invalid @enderror"
                            placeholder="Enter password" id="password-input" name="password" required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="mdi mdi-eye-off"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">Sign In</button>


            </form>
        </div>

        <div class="image-section">
            <h3>Join Our Community</h3>
            <p>Discover amazing features and connect with people around the world. Your journey starts here.</p>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password-input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('mdi-eye-off');
                icon.classList.add('mdi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('mdi-eye');
                icon.classList.add('mdi-eye-off');
            }
        });

        // Form submission with validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password-input').value;
            const messageContainer = document.getElementById('messageContainer');

            // Clear previous messages
            messageContainer.innerHTML = '';

            // Basic validation
            if (!email || !password) {
                showMessage('Please fill in all fields', 'error');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }

            // Password length validation
            if (password.length < 6) {
                showMessage('Password must be at least 6 characters long', 'error');
                return;
            }


            setTimeout(() => {
                this.submit();
            }, 1000);
        });

        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';

            messageContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;

            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 3000);
            }
        }

        // Add loading state to button
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('.login-btn');
            submitBtn.innerHTML = 'Signing In...';
            submitBtn.disabled = true;

            // Re-enable button after 3 seconds (for demo purposes)
            setTimeout(() => {
                submitBtn.innerHTML = 'Sign In';
                submitBtn.disabled = false;
            }, 3000);
        });
    </script>
</body>

</html>
