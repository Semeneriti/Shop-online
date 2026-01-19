
<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <form action="handle_login.php" method = "POST">
            <h1 class="brand-title">Welcome Back</h1>
            <p class="brand-subtitle">Sign in to continue to your account</p>
        </div>

        <form class="login-form" id="loginForm">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    class="form-input"
                    placeholder="name@company.com"
                    required
                />
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label for="password" class="form-label">Password</label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <input
                    type="password"
                    id="password"
                    class="form-input"
                    placeholder="Enter your password"
                    required
                />
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" class="checkbox-input" />
                    <span class="checkbox-text">Remember me for 30 days</span>
                </label>
            </div>

            <button type="submit" class="submit-btn">
                <span class="btn-text">Sign In</span>
                <span class="btn-loader"></span>
            </button>
        </form>

        <div class="divider">
            <span class="divider-text">or continue with</span>
        </div>

        <div class="social-buttons">
            <button class="social-btn">
                <svg class="social-icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>
            <button class="social-btn">
                <svg class="social-icon" viewBox="0 0 24 24" fill="#000">
                    <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                </svg>
                GitHub
            </button>
        </div>

        <p class="signup-text">
            Don't have an account? <a href="#" class="signup-link">Sign up</a>
        </p>
    </div>
</div>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fafafa;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
        padding: 20px;
    }

    .login-wrapper {
        width: 100%;
        max-width: 440px;
    }

    .login-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 48px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .login-header {
        margin-bottom: 32px;
        text-align: center;
    }

    .brand-title {
        font-size: 28px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .brand-subtitle {
        font-size: 15px;
        color: #6b7280;
        font-weight: 400;
    }

    .login-form {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .forgot-link {
        font-size: 13px;
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .forgot-link:hover {
        color: #1d4ed8;
    }

    .form-input {
        width: 100%;
        padding: 11px 14px;
        font-size: 15px;
        color: #111827;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: all 0.2s ease;
        outline: none;
    }

    .form-input::placeholder {
        color: #9ca3af;
    }

    .form-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .checkbox-group {
        margin-bottom: 24px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .checkbox-input {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        cursor: pointer;
        accent-color: #2563eb;
    }

    .checkbox-text {
        font-size: 14px;
        color: #374151;
        user-select: none;
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background: #2563eb;
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .submit-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .btn-loader {
        display: none;
    }

    .submit-btn.loading .btn-text {
        opacity: 0;
    }

    .submit-btn.loading .btn-loader {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .divider {
        position: relative;
        text-align: center;
        margin: 32px 0;
    }

    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e5e7eb;
    }

    .divider-text {
        position: relative;
        display: inline-block;
        padding: 0 16px;
        background: #ffffff;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .social-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
    }

    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .social-btn:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .social-icon {
        width: 18px;
        height: 18px;
    }

    .signup-text {
        text-align: center;
        font-size: 14px;
        color: #6b7280;
    }

    .signup-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .signup-link:hover {
        color: #1d4ed8;
    }

    @media (max-width: 480px) {
        .login-container {
            padding: 32px 24px;
        }

        .brand-title {
            font-size: 24px;
        }

        .social-buttons {
            grid-template-columns: 1fr;
        }
    }

</style>

