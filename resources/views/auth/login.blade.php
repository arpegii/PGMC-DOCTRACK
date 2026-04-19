<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ config('app.name', 'AFPPGMC Document Tracking System') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --color-primary: #1e5ba8;
            --color-bg: #FDFDFC;
            --color-border: #e3e3e0;
            --color-border-hover: #1e5ba835;
            --color-text: #1b1b18;
            --color-text-muted: #706f6c;
            --color-accent: #ffd700;
            --color-error: #dc2626;
            --color-success: #2d7a3e;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --color-primary: #2b7fd8;
                --color-bg: #0a0a0a;
                --color-border: #3E3E3A;
                --color-border-hover: #62605b;
                --color-text: #EDEDEC;
                --color-text-muted: #A1A09A;
                --color-accent: #ffd700;
                --color-error: #ef4444;
                --color-success: #3d9950;
            }
        }
        
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            line-height: 1.5;
        }
        
        .auth-container {
            width: 100%;
            max-width: 28rem;
            opacity: 0;
            transform: translateY(1rem);
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.03), 0px 1px 2px 0px rgba(0,0,0,0.06);
        }
        
        .auth-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--color-border);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .logo-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }
        
        .logo-text h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text);
            letter-spacing: -0.01em;
        }
        
        .logo-text p {
            font-size: 0.8125rem;
            color: var(--color-text-muted);
            font-weight: 500;
        }
        
        .auth-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.25rem;
        }
        
        .auth-header p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }
        
        @media (prefers-color-scheme: dark) {
            .alert-success {
                background: #052e16;
                border-color: #166534;
                color: #86efac;
            }
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-field {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border: 1px solid var(--color-border);
            border-radius: 0.25rem;
            background: var(--color-bg);
            color: var(--color-text);
            transition: all 0.15s ease;
            font-family: inherit;
        }
        
        .input-field:hover:not(:focus) {
            border-color: var(--color-border-hover);
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 1px var(--color-primary);
        }
        
        .input-field::placeholder {
            color: var(--color-text-muted);
            opacity: 0.6;
        }
        
        .error-message {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: var(--color-error);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--color-text);
            cursor: pointer;
        }
        
        .checkbox-input {
            width: 1rem;
            height: 1rem;
            border: 1px solid var(--color-border);
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .link {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        
        .link:hover {
            color: var(--color-accent);
        }
        
        .btn {
            width: 100%;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: var(--color-bg);
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--color-border);
        }
        
        .divider-text {
            position: relative;
            display: inline-block;
            padding: 0 0.75rem;
            background: var(--color-bg);
            font-size: 0.8125rem;
            color: var(--color-text-muted);
        }
        
        .auth-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }
        
        .auth-footer p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
        
        .auth-footer .link {
            color: var(--color-text);
            font-weight: 500;
        }
        
        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }
            
            .auth-header,
            .auth-body,
            .auth-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="/images/logo.png" alt="AFPPGMC Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="logo-text">
                        <h1>PGMC Pension Services Tracking System</h1>
                        <p>Document Management</p>
                    </div>
                </div>
                <h2>Sign in to your account</h2>
                <p>Access your document tracking system</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <!-- Email Updated Success Message -->
                @if (session('status') === 'email-updated-login')
                    <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p style="font-weight: 600; margin-bottom: 0.25rem;">Email Updated Successfully!</p>
                            <p style="font-size: 0.8125rem; opacity: 0.9;">Your email address has been changed. Please log in with your new email address.</p>
                        </div>
                    </div>
                @endif

                <!-- Other Status Messages -->
                @if (session('status') && session('status') !== 'email-updated-login')
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-wrapper">
                            <input 
                                id="username" 
                                type="text" 
                                name="username" 
                                value="{{ old('username') }}" 
                                required 
                                autofocus
                                autocomplete="username"
                                class="input-field"
                                placeholder="Enter your username"
                            >
                        </div>
                        @error('username')
                            <div class="error-message">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="7" cy="7" r="6"></circle>
                                    <line x1="7" y1="4" x2="7" y2="7"></line>
                                    <line x1="7" y1="10" x2="7.01" y2="10"></line>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                class="input-field"
                                placeholder="Enter your password"
                            >
                        </div>
                        @error('password')
                            <div class="error-message">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="7" cy="7" r="6"></circle>
                                    <line x1="7" y1="4" x2="7" y2="7"></line>
                                    <line x1="7" y1="10" x2="7.01" y2="10"></line>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                class="checkbox-input"
                            >
                            <span>Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="link">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary">
                        <span>Sign in</span>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="2" y1="7" x2="12" y2="7"></line>
                            <polyline points="8 3 12 7 8 11"></polyline>
                        </svg>
                    </button>
                </form>

                <div class="divider">
                    <span class="divider-text">or</span>
                </div>

                <div style="text-align: center;">
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.75rem;">
                        Don't have an account yet?
                    </p>
                    <a href="{{ route('register') }}" class="link" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 12h-8a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h3"></path>
                            <polyline points="9 2 12 2 12 5"></polyline>
                            <line x1="12" y1="2" x2="7" y2="7"></line>
                        </svg>
                        <span>Sign up</span>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
            </div>
        </div>
    </div>
</body>
</html>
