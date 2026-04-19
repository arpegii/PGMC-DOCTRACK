<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - {{ config('app.name', 'AFPPGMC Document Tracking System') }}</title>
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
            padding: 2rem 1.5rem;
            line-height: 1.5;
        }
        
        .auth-container {
            width: 100%;
            max-width: 42rem;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        
        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .form-group-full {
                grid-column: 1 / -1;
            }
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }
        
        .required {
            color: var(--color-error);
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
        
        .password-requirements {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.25rem;
        }
        
        .req-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }
        
        .req-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--color-text-muted);
            margin-bottom: 0.25rem;
        }
        
        .req-item:last-child { margin-bottom: 0; }
        
        .req-item.met { color: var(--color-success); }
        
        .req-icon {
            width: 0.875rem;
            height: 0.875rem;
            border: 1.5px solid currentColor;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .req-item.met .req-icon {
            background: var(--color-success);
            border-color: var(--color-success);
            color: white;
        }
        
        .req-icon svg { width: 0.5rem; height: 0.5rem; display: none; }
        .req-item.met .req-icon svg { display: block; }
        
        .link {
            color: var(--color-text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        .link:hover { color: var(--color-accent); }
        
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
            margin-top: 1.5rem;
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
            margin: 1.5rem 0;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }
        
        .divider p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
            margin-bottom: 0;
        }
        
        .auth-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }
        
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .auth-header, .auth-body, .auth-footer {
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
                        <img src="images/logo.png" alt="AFPPGMC Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="logo-text">
                        <h1>PGMC Pension Services Tracking System</h1>
                        <p>Document Management</p>
                    </div>
                </div>
                <h2>Sign up</h2>
                <p>Complete the form below to request access to the system</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <div class="form-grid">

                        <!-- Name -->
                        <div class="form-group form-group-full">
                            <label for="name" class="form-label">
                                Full name <span class="required">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                class="input-field"
                                placeholder="Enter Full name"
                            >
                            @error('name')
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

                        <!-- Username -->
                        <div class="form-group form-group-full">
                            <label for="username" class="form-label">
                                Username <span class="required">*</span>
                            </label>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                required
                                autocomplete="username"
                                class="input-field"
                                placeholder="Enter Username"
                            >
                            <small style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.375rem;">
                                Lowercase letters, numbers, and underscores only
                            </small>
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

                        <!-- Email -->
                        {{-- COMMENTED OUT - TO BE USED LATER
                        <div class="form-group form-group-full">
                            <label for="email" class="form-label">
                                Email address <span class="required">*</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                class="input-field"
                                placeholder="name@company.com"
                            >
                            @error('email')
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
                        --}}

                        <!-- Unit -->
                        <div class="form-group form-group-full">
                            <label class="form-label">
                                Unit <span class="required">*</span>
                            </label>

                            <div id="unit-picker" style="position: relative;">

                                <!-- Display button -->
                                <button
                                    type="button"
                                    id="unit-picker-btn"
                                    onclick="toggleUnitDropdown(event)"
                                    style="
                                        width: 100%;
                                        padding: 0.625rem 0.875rem;
                                        font-size: 0.875rem;
                                        border: 1px solid var(--color-border);
                                        border-radius: 0.25rem;
                                        background: var(--color-bg);
                                        color: var(--color-text-muted);
                                        font-family: inherit;
                                        cursor: pointer;
                                        display: flex;
                                        align-items: center;
                                        justify-content: space-between;
                                        transition: all 0.15s ease;
                                        text-align: left;
                                    "
                                >
                                    <span id="unit-picker-label">Select unit</span>
                                    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Hidden input submitted with form -->
                                <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id') }}">

                                @error('unit_id')
                                    <div class="error-message">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="7" cy="7" r="6"></circle>
                                            <line x1="7" y1="4" x2="7" y2="7"></line>
                                            <line x1="7" y1="10" x2="7.01" y2="10"></line>
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror

                                <!-- Dropdown list -->
                                <div
                                    id="unit-dropdown"
                                    style="
                                        display: none;
                                        position: absolute;
                                        top: calc(100% + 4px);
                                        left: 0;
                                        width: 100%;
                                        background: white;
                                        border: 1px solid #d1d5db;
                                        border-radius: 0.5rem;
                                        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                                        z-index: 99999;
                                        overflow: hidden;
                                        max-height: 260px;
                                        overflow-y: auto;
                                    "
                                >
                                    @foreach($units as $unit)
                                        @if(in_array($unit->name, [
                                            'Resumption NCO', 'TOP NCO', 'Restoration NCO',
                                            'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO',
                                            'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
                                        ]))
                                            @continue
                                        @endif

                                        @if($unit->name === 'PAU')
                                            <div
                                                class="reg-unit-row"
                                                data-unit-id="{{ $unit->id }}"
                                                data-unit-name="PAU"
                                                data-has-flyout="pau"
                                                style="padding:0.6rem 0.875rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                            >
                                                <span>PAU</span>
                                                <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>

                                        @elseif($unit->name === 'BGCU')
                                            <div
                                                class="reg-unit-row"
                                                data-unit-id="{{ $unit->id }}"
                                                data-unit-name="BGCU"
                                                data-has-flyout="bgcu"
                                                style="padding:0.6rem 0.875rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                            >
                                                <span>BGCU</span>
                                                <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>

                                        @else
                                            <div
                                                class="reg-unit-row"
                                                data-unit-id="{{ $unit->id }}"
                                                data-unit-name="{{ $unit->name }}"
                                                style="padding:0.6rem 0.875rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                                            >
                                                {{ $unit->name }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group form-group-full">
                            <label for="password" class="form-label">
                                Password <span class="required">*</span>
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="input-field"
                                placeholder="Create a strong password"
                            >
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

                            <div class="password-requirements">
                                <div class="req-title">Password must contain:</div>
                                <div class="req-item" id="req-length">
                                    <div class="req-icon">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 7 6 10 11 4"></polyline>
                                        </svg>
                                    </div>
                                    <span>At least 8 characters</span>
                                </div>
                                <div class="req-item" id="req-uppercase">
                                    <div class="req-icon">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 7 6 10 11 4"></polyline>
                                        </svg>
                                    </div>
                                    <span>One uppercase letter</span>
                                </div>
                                <div class="req-item" id="req-lowercase">
                                    <div class="req-icon">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 7 6 10 11 4"></polyline>
                                        </svg>
                                    </div>
                                    <span>One lowercase letter</span>
                                </div>
                                <div class="req-item" id="req-number">
                                    <div class="req-icon">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 7 6 10 11 4"></polyline>
                                        </svg>
                                    </div>
                                    <span>One number</span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group form-group-full">
                            <label for="password_confirmation" class="form-label">
                                Confirm password <span class="required">*</span>
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="input-field"
                                placeholder="Confirm your password"
                            >
                        </div>

                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary">
                        <span>Sign up</span>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="1" y1="7" x2="13" y2="7"></line>
                            <polyline points="7 1 13 7 7 13"></polyline>
                        </svg>
                    </button>
                </form>

                <div class="divider">
                    <p>
                        Already have an account?
                        <a href="{{ route('login') }}" class="link">Sign in here</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer"></div>
        </div>
    </div>

    <!-- PAU Flyout — appended to body so nothing clips it -->
    <div id="reg-pau-flyout" style="
        display:none;
        position:fixed;
        width:230px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.5rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    ">
        <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
            PAU SUB-UNITS
        </div>
        @foreach($units as $subUnit)
            @if(in_array($subUnit->name, [
                'Resumption NCO', 'TOP NCO', 'Restoration NCO',
                'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO'
            ]))
                <div
                    class="reg-flyout-item"
                    data-unit-id="{{ $subUnit->id }}"
                    data-unit-name="{{ $subUnit->name }}"
                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                >
                    {{ $subUnit->name }}
                </div>
            @endif
        @endforeach
    </div>

    <!-- BGCU Flyout — appended to body so nothing clips it -->
    <div id="reg-bgcu-flyout" style="
        display:none;
        position:fixed;
        width:210px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.5rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    ">
        <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
            BGCU SUB-UNITS
        </div>
        @foreach($units as $subUnit)
            @if(in_array($subUnit->name, [
                'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
            ]))
                <div
                    class="reg-flyout-item"
                    data-unit-id="{{ $subUnit->id }}"
                    data-unit-name="{{ $subUnit->name }}"
                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                >
                    {{ $subUnit->name }}
                </div>
            @endif
        @endforeach
    </div>

    <script>
        let regFlyoutTimers = {};

        // ── Unit picker toggle ────────────────────────────────────────────────
        function toggleUnitDropdown(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('unit-dropdown');
            const isOpen   = dropdown.style.display === 'block';
            dropdown.style.display = isOpen ? 'none' : 'block';
            if (isOpen) {
                hideFlyout('reg-pau-flyout');
                hideFlyout('reg-bgcu-flyout');
            }
        }

        function selectUnit(id, name) {
            document.getElementById('unit_id').value = id;
            const label       = document.getElementById('unit-picker-label');
            label.textContent = name;
            label.style.color = 'var(--color-text)';
            document.getElementById('unit-dropdown').style.display = 'none';
            hideFlyout('reg-pau-flyout');
            hideFlyout('reg-bgcu-flyout');
        }

        function hideFlyout(id) {
            clearTimeout(regFlyoutTimers[id]);
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        }

        // ── Init ──────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {

            // Move flyouts to <body> so page overflow never clips them
            const pauFlyout  = document.getElementById('reg-pau-flyout');
            const bgcuFlyout = document.getElementById('reg-bgcu-flyout');
            document.body.appendChild(pauFlyout);
            document.body.appendChild(bgcuFlyout);

            // Flyout item hover + click
            document.querySelectorAll('#reg-pau-flyout .reg-flyout-item, #reg-bgcu-flyout .reg-flyout-item').forEach(item => {
                item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
                item.addEventListener('mouseleave', () => item.style.background = '');
                item.addEventListener('click', () => selectUnit(item.dataset.unitId, item.dataset.unitName));
            });

            // Keep flyout open when mouse is inside it
            [pauFlyout, bgcuFlyout].forEach(flyout => {
                flyout.addEventListener('mouseenter', () => clearTimeout(regFlyoutTimers[flyout.id]));
                flyout.addEventListener('mouseleave', () => hideFlyout(flyout.id));
            });

            // Unit row hover + click
            document.querySelectorAll('.reg-unit-row').forEach(row => {

                row.addEventListener('mouseenter', () => {
                    row.style.background = '#f3f4f6';
                    const flyoutKey = row.dataset.hasFlyout;
                    if (flyoutKey) {
                        const other = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                        hideFlyout('reg-' + other + '-flyout');
                        clearTimeout(regFlyoutTimers['reg-' + flyoutKey + '-flyout']);
                        const rect   = row.getBoundingClientRect();
                        const flyout = document.getElementById('reg-' + flyoutKey + '-flyout');
                        flyout.style.top  = rect.top + 'px';
                        flyout.style.left = (rect.right + 6) + 'px';
                        flyout.style.display = 'block';
                    } else {
                        hideFlyout('reg-pau-flyout');
                        hideFlyout('reg-bgcu-flyout');
                    }
                });

                row.addEventListener('mouseleave', () => {
                    row.style.background = '';
                    const flyoutKey = row.dataset.hasFlyout;
                    if (flyoutKey) {
                        regFlyoutTimers['reg-' + flyoutKey + '-flyout'] = setTimeout(() => {
                            hideFlyout('reg-' + flyoutKey + '-flyout');
                        }, 120);
                    }
                });

                // PAU and BGCU are clickable as units themselves
                row.addEventListener('click', () => {
                    selectUnit(row.dataset.unitId, row.dataset.unitName);
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                const picker = document.getElementById('unit-picker');
                if (
                    picker && !picker.contains(e.target) &&
                    !pauFlyout.contains(e.target) &&
                    !bgcuFlyout.contains(e.target)
                ) {
                    document.getElementById('unit-dropdown').style.display = 'none';
                    hideFlyout('reg-pau-flyout');
                    hideFlyout('reg-bgcu-flyout');
                }
            });

            // Restore old() value on validation error
            const oldValue = "{{ old('unit_id') }}";
            if (oldValue) {
                document.querySelectorAll('.reg-unit-row').forEach(row => {
                    if (row.dataset.unitId == oldValue) {
                        selectUnit(row.dataset.unitId, row.dataset.unitName);
                    }
                });

                // Also check flyout items (sub-units)
                document.querySelectorAll('.reg-flyout-item').forEach(item => {
                    if (item.dataset.unitId == oldValue) {
                        selectUnit(item.dataset.unitId, item.dataset.unitName);
                    }
                });
            }
        });

        // ── Password Requirements ─────────────────────────────────────────────
        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('input', function () {
            const password = this.value;
            document.getElementById('req-length').classList.toggle('met', password.length >= 8);
            document.getElementById('req-uppercase').classList.toggle('met', /[A-Z]/.test(password));
            document.getElementById('req-lowercase').classList.toggle('met', /[a-z]/.test(password));
            document.getElementById('req-number').classList.toggle('met', /[0-9]/.test(password));
        });
    </script>
</body>
</html>