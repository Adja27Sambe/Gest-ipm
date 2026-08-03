<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a3060">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Connexion — IPM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-deep:  #0a3060;
            --blue-mid:   #0f4c81;
            --blue-light: #1a6fba;
            --teal:       #00b4d8;
            --teal-light: #48cae4;
            --green:      #06d6a0;
            --error:      #ef4444;
            --text-1:     #111827;
            --text-2:     #6b7280;
            --border:     #e5e7eb;
        }

        html {
            height: 100%;
            /* Allows browser to handle viewport resize (keyboard) */
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100%;
            min-height: 100dvh;
            background: var(--blue-deep);
            /* NO overflow:hidden — lets content scroll when keyboard opens */
            display: flex;
            flex-direction: column;
        }

        /* ─── HERO — fixed decorative background ─── */
        .hero-bg {
            position: fixed;
            inset: 0;
            background: url('/images/login-bg.png') center / cover no-repeat;
            z-index: 0;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(170deg,
                rgba(10,48,96,0.90) 0%,
                rgba(15,76,129,0.78) 45%,
                rgba(0,180,216,0.50) 100%);
        }
        .hero-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.10);
            animation: pulse 5s ease-in-out infinite;
        }
        .hero-circle:nth-child(2) { width:180px;height:180px;top:-50px;right:-50px;animation-delay:0s; }
        .hero-circle:nth-child(3) { width:280px;height:280px;top:-100px;right:-100px;animation-delay:0.8s;border-color:rgba(255,255,255,0.05); }
        .hero-circle:nth-child(4) { width:110px;height:110px;bottom:20px;left:-30px;animation-delay:1.5s; }
        @keyframes pulse {
            0%,100% { opacity:.6; transform:scale(1); }
            50%      { opacity:1;  transform:scale(1.05); }
        }

        /* ─── SCROLLABLE PAGE SHELL ─── */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
        }

        /* ─── HERO CONTENT AREA ─── */
        .hero-area {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: calc(env(safe-area-inset-top, 20px) + 32px) 24px 36px;
            min-height: 230px;
        }

        .logo-pill {
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 10px 22px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.20);
            animation: popIn 0.45s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        .logo-pill img { height: 38px; width: auto; display: block; }

        @keyframes popIn {
            from { opacity:0; transform:scale(0.75); }
            to   { opacity:1; transform:scale(1); }
        }

        .hero-title {
            font-size: 20px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.3px;
            animation: fadeUp .45s ease .1s both;
        }
        .hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,0.60);
            margin-top: 5px;
            font-weight: 400;
            animation: fadeUp .45s ease .18s both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(8px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ─── BOTTOM SHEET ─── */
        .sheet {
            flex: 1;
            background: #ffffff;
            border-radius: 26px 26px 0 0;
            padding: 10px 24px calc(env(safe-area-inset-bottom, 16px) + 24px);
            /* No overflow:hidden — sheet grows with keyboard */
            animation: slideUp .5s cubic-bezier(0.32,0.72,0,1) both;
        }
        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        .sheet-handle {
            width: 40px; height: 4px;
            background: #d1d5db;
            border-radius: 2px;
            margin: 0 auto 22px;
        }

        .sheet-title { font-size: 22px; font-weight: 800; color: var(--text-1); margin-bottom: 4px; }
        .sheet-sub   { font-size: 13px; color: var(--text-2); margin-bottom: 22px; line-height:1.5; }

        /* ─── ERROR BANNER ─── */
        .error-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 16px;
            animation: shakeX .35s ease;
        }
        @keyframes shakeX {
            0%,100%{ transform:translateX(0); }
            20%    { transform:translateX(-5px); }
            60%    { transform:translateX(5px); }
        }
        .error-banner i    { color: var(--error); font-size: 15px; flex-shrink:0; margin-top:1px; }
        .error-banner span { font-size: 13px; color: #991b1b; line-height: 1.5; font-weight: 500; }

        /* ─── FIELD (floating label, iOS) ─── */
        .field { margin-bottom: 12px; }

        .field-box {
            position: relative;
            background: #f9fafb;
            border: 1.5px solid var(--border);
            border-radius: 16px;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .field-box:focus-within {
            background: #fff;
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(0,180,216,0.12);
        }
        .field-box.err {
            border-color: var(--error);
            background: #fff8f8;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.09);
        }
        .field-box:focus-within .f-ico { color: var(--teal); }
        .field-box.err .f-ico          { color: var(--error); }

        .f-ico {
            position: absolute;
            left: 15px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
            transition: color .2s;
        }

        .f-label {
            position: absolute;
            left: 44px; top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            font-weight: 500;
            color: var(--text-2);
            pointer-events: none;
            transform-origin: left center;
            transition: all .2s cubic-bezier(.4,0,.2,1);
        }
        .field-box:focus-within .f-label,
        .field-box.filled .f-label {
            top: 13px;
            transform: translateY(0) scale(.75);
            color: var(--teal);
            font-weight: 600;
        }
        .field-box.err:focus-within .f-label,
        .field-box.err.filled .f-label { color: var(--error); }

        .f-input {
            width: 100%;
            height: 58px;
            padding: 20px 46px 6px 44px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            color: var(--text-1);
            background: transparent;
            border: none;
            outline: none;
            -webkit-appearance: none;
            border-radius: 16px;
        }
        .f-input::placeholder { color: transparent; }

        .f-toggle {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 15px;
            cursor: pointer;
            padding: 6px;
            transition: color .2s;
            -webkit-tap-highlight-color: transparent;
        }
        .f-toggle:hover, .f-toggle:focus { color: var(--blue-mid); outline: none; }

        .f-error {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--error);
            font-weight: 500;
            margin-top: 5px;
            padding-left: 2px;
        }

        /* ─── iOS TOGGLE (remember me) ─── */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 6px 0 20px;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .r-check { display: none; }
        .r-track {
            flex-shrink: 0;
            width: 44px; height: 26px;
            border-radius: 13px;
            background: #d1d5db;
            position: relative;
            transition: background .25s;
        }
        .r-track::after {
            content: '';
            position: absolute;
            width: 22px; height: 22px;
            background: white;
            border-radius: 50%;
            top: 2px; left: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1);
        }
        .r-check:checked ~ .r-track               { background: var(--teal); }
        .r-check:checked ~ .r-track::after         { transform: translateX(18px); }
        .r-label { font-size: 14px; font-weight: 500; color: var(--text-1); user-select:none; }

        /* ─── SUBMIT BUTTON ─── */
        .btn-submit {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--blue-mid) 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 24px rgba(0,180,216,.35);
            transition: transform .12s, box-shadow .12s, opacity .15s;
            -webkit-tap-highlight-color: transparent;
            position: relative;
            overflow: hidden;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
        }
        .btn-submit:active  { transform: scale(0.97); box-shadow: 0 3px 12px rgba(0,180,216,.25); }
        .btn-submit:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        .spinner {
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255,255,255,.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin .65s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner  { display: block; }

        /* ─── FOOTER HELP ─── */
        .help-footer {
            margin-top: 20px;
            text-align: center;
        }
        .help-footer p { font-size: 12px; color: var(--text-2); line-height: 1.6; }
        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            font-size: 11px;
            color: #9ca3af;
        }
        .secure-badge i { color: var(--green); }

        /* ─── DESKTOP CENTERING ─── */
        @media (min-width: 560px) {
            body { align-items: center; justify-content: center; }
            .page {
                width: 100%;
                max-width: 400px;
                min-height: auto;
                border-radius: 36px;
                overflow: hidden;
                box-shadow: 0 32px 80px rgba(0,0,0,0.30);
            }
            .hero-area { min-height: 210px; }
            .sheet { border-radius: 0; }
        }
    </style>
</head>
<body>

    {{-- Fixed background --}}
    <div class="hero-bg" aria-hidden="true">
        <div class="hero-circle"></div>
        <div class="hero-circle"></div>
        <div class="hero-circle"></div>
    </div>

    <div class="page">
        {{-- Hero --}}
        <div class="hero-area">
            <div class="logo-pill">
                <img src="{{ asset('logo.png') }}" alt="IPM Mbaarum Koolute">
            </div>
            <h1 class="hero-title">Espace Bénéficiaire</h1>
            <p class="hero-sub">Votre santé, notre priorité</p>
        </div>

        {{-- Sheet --}}
        <div class="sheet">
            <div class="sheet-handle" aria-hidden="true"></div>
            <h2 class="sheet-title">Connexion</h2>
            <p class="sheet-sub">Accédez à votre espace personnel IPM</p>

            {{-- Errors --}}
            @if($errors->any())
                <div class="error-banner" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="error-banner" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('participant.login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Matricule --}}
                <div class="field">
                    <div class="field-box {{ $errors->has('matricule') ? 'err' : '' }}" id="boxMat">
                        <i class="fas fa-id-card f-ico"></i>
                        <label class="f-label" for="matricule">Matricule</label>
                        <input class="f-input" type="text" id="matricule" name="matricule"
                               value="{{ old('matricule') }}"
                               autocomplete="username" autofocus required>
                    </div>
                    @error('matricule')
                        <div class="f-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Code sécurité --}}
                <div class="field">
                    <div class="field-box {{ $errors->has('code_securite') ? 'err' : '' }}" id="boxCode">
                        <i class="fas fa-lock f-ico"></i>
                        <label class="f-label" for="code_securite">Code de sécurité</label>
                        <input class="f-input" type="password" id="code_securite" name="code_securite"
                               autocomplete="current-password" required>
                        <button type="button" class="f-toggle" id="togglePwd" aria-label="Afficher le code">
                            <i class="fas fa-eye" id="eyeIco"></i>
                        </button>
                    </div>
                    @error('code_securite')
                        <div class="f-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember --}}
                <label class="remember-row" for="remember">
                    <input type="checkbox" id="remember" name="remember" class="r-check">
                    <div class="r-track" aria-hidden="true"></div>
                    <span class="r-label">Se souvenir de moi</span>
                </label>

                {{-- Submit --}}
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">
                        <i class="fas fa-arrow-right-to-bracket"></i>&ensp;Se connecter
                    </span>
                    <div class="spinner" aria-hidden="true"></div>
                </button>

                <div class="help-footer">
                    <p>Contactez votre employeur ou l'IPM<br>pour obtenir vos identifiants.</p>
                    <div class="secure-badge">
                        <i class="fas fa-shield-halved"></i> Connexion chiffrée SSL
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ── Floating label: mark box as "filled"
        function updateFilled(input) {
            const box = input.closest('.field-box');
            box.classList.toggle('filled', input.value.trim().length > 0);
        }

        document.querySelectorAll('.f-input').forEach(inp => {
            inp.addEventListener('input', () => updateFilled(inp));
            updateFilled(inp); // init on page load (e.g. autofill)
        });

        // ── Toggle password
        const codeInput = document.getElementById('code_securite');
        document.getElementById('togglePwd').addEventListener('click', function () {
            const show = codeInput.type === 'password';
            codeInput.type = show ? 'text' : 'password';
            document.getElementById('eyeIco').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        // ── Submit loader
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // ── Scroll focused input into view when keyboard opens (iOS)
        document.querySelectorAll('.f-input').forEach(inp => {
            inp.addEventListener('focus', () => {
                setTimeout(() => {
                    inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 350);
            });
        });
    </script>
</body>
</html>
