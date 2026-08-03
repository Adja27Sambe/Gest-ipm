<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a3060">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Mon Espace — IPM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Bootstrap pour la pagination — chargé en HEAD pour éviter le flash --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ── RESET ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        * { -webkit-tap-highlight-color: transparent; }

        :root {
            --blue-deep:  #0a3060;
            --blue-mid:   #0f4c81;
            --blue-light: #1a6fba;
            --teal:       #00b4d8;
            --teal-light: #48cae4;
            --green:      #06d6a0;
            --amber:      #f59e0b;
            --red:        #ef4444;
            --bg:         #f1f4f8;
            --white:      #ffffff;
            --text-1:     #111827;
            --text-2:     #6b7280;
            --border:     #e5e7eb;
            /* Safe areas */
            --sat: env(safe-area-inset-top, 0px);
            --sab: env(safe-area-inset-bottom, 16px);
            /* Tab bar height + safe area */
            --tab-h: 58px;
            --tab-total: calc(var(--tab-h) + var(--sab));
        }

        html {
            height: 100%;
            background: #1a2a3a; /* dark for desktop gap */
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-1);
            /* Allow natural scroll — no overflow:hidden on body */
            min-height: 100%;
        }

        /* ─── APP SHELL ─── */
        /* Phone frame wrapper — constrained width */
        .app-shell {
            max-width: 430px;
            margin: 0 auto;
            min-height: 100dvh;
            background: var(--bg);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* ─── STATUS BAR (safe area top) ─── */
        .status-bar {
            background: var(--blue-deep);
            height: var(--sat);
            flex-shrink: 0;
        }

        /* ─── HEADER ─── */
        .app-header {
            background: linear-gradient(145deg, var(--blue-deep) 0%, var(--blue-mid) 65%, var(--blue-light) 100%);
            padding: 16px 20px 60px; /* bottom: space for stats card overlap */
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .app-header .deco-1,
        .app-header .deco-2 {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .app-header .deco-1 {
            width: 220px; height: 220px;
            border: 1px solid rgba(255,255,255,0.08);
            top: -70px; right: -70px;
        }
        .app-header .deco-2 {
            width: 120px; height: 120px;
            background: rgba(0,180,216,0.10);
            bottom: -30px; left: -20px;
        }

        .h-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .h-logo {
            background: rgba(255,255,255,0.94);
            border-radius: 12px;
            padding: 5px 14px;
        }
        .h-logo img { height: 28px; width: auto; display: block; }

        .h-actions { display: flex; gap: 8px; }
        .icon-btn {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.13);
            border: 1px solid rgba(255,255,255,0.20);
            color: white;
            font-size: 15px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s;
            /* no default button styles */
            font-family: inherit;
        }
        .icon-btn:active { background: rgba(255,255,255,0.25); }

        .h-user {
            margin-top: 16px;
            position: relative;
            z-index: 1;
        }
        .h-hello { font-size: 13px; color: rgba(255,255,255,0.58); margin-bottom: 2px; }
        .h-name  { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.3px; }
        .h-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            background: rgba(255,255,255,0.13);
            border: 1px solid rgba(255,255,255,0.20);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.82);
            font-weight: 500;
        }
        .h-dot { width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block; }

        /* ─── SCROLL CONTENT ─── */
        /*
         * KEY FIX: No overflow:hidden on .app-shell or body.
         * The page scrolls naturally. The tab bar is fixed.
         * We add bottom padding equal to tab bar height.
        */
        .scroll-content {
            flex: 1;
            padding-bottom: calc(var(--tab-total) + 12px);
        }

        /* ─── STATS CARD (overlapping header) ─── */
        /*
         * KEY FIX: Use negative margin on the card itself within the
         * scroll-content. Since scroll-content follows the header in
         * normal flow, margin-top: -44px pulls it up into the header's
         * extra bottom padding (60px). No overflow:hidden needed.
        */
        .stats-card {
            margin: -44px 16px 0;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 6px 28px rgba(10,48,96,0.14);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            overflow: hidden;
        }

        .stat-col {
            text-align: center;
            padding: 18px 6px;
            position: relative;
        }
        .stat-col:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0; top: 20%; bottom: 20%;
            width: 1px; background: var(--border);
        }
        .stat-num { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 3px; }
        .stat-num.blue  { color: var(--blue-mid); }
        .stat-num.green { color: var(--green); }
        .stat-num.amber { color: var(--amber); }
        .stat-lbl { font-size: 10px; font-weight: 600; color: var(--text-2); text-transform: uppercase; letter-spacing: .5px; }

        /* ─── SECTIONS ─── */
        .section { padding: 20px 16px 0; }
        .sec-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .sec-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-1);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .sec-icon {
            width: 26px; height: 26px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }
        .si-blue  { background: rgba(15,76,129,0.10); color: var(--blue-mid); }
        .si-teal  { background: rgba(0,180,216,0.12); color: var(--teal); }
        .si-green { background: rgba(6,214,160,0.12); color: var(--green); }
        .count-pill {
            font-size: 10px; font-weight: 700;
            background: var(--teal); color: white;
            border-radius: 20px; padding: 1px 8px;
        }

        /* ─── PRIMARY BUTTON ─── */
        .primary-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--blue-mid) 100%);
            color: white;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 5px 18px rgba(0,180,216,.30);
            transition: transform .12s, box-shadow .12s;
            position: relative;
            overflow: hidden;
        }
        .primary-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.10), transparent);
        }
        .primary-btn:active { transform: scale(0.97); box-shadow: 0 2px 10px rgba(0,180,216,.20); }

        /* ─── INFO CARD ─── */
        .info-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(10,48,96,0.07);
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child { border-bottom: none; }
        .i-icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: rgba(15,76,129,0.08);
            color: var(--blue-mid);
            font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .i-lbl { font-size: 10px; font-weight: 600; color: var(--text-2); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 1px; }
        .i-val { font-size: 14px; font-weight: 600; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ─── AYANTS DROIT — horizontal scroll ─── */
        .ad-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 2px 0 4px;
        }
        .ad-scroll::-webkit-scrollbar { display: none; }

        .ad-card {
            flex-shrink: 0;
            width: 120px;
            background: var(--white);
            border-radius: 16px;
            padding: 14px 10px;
            box-shadow: 0 2px 10px rgba(10,48,96,0.07);
            display: flex; flex-direction: column; align-items: center; gap: 7px;
        }
        .ad-av {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal-light), var(--blue-light));
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 800; color: white;
        }
        .ad-name { font-size: 11px; font-weight: 600; color: var(--text-1); text-align: center; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ad-lien { font-size: 10px; color: var(--text-2); background: #f3f4f6; border-radius: 20px; padding: 2px 8px; white-space: nowrap; }

        .ad-empty, .empty-box {
            background: var(--white);
            border: 1.5px dashed var(--border);
            border-radius: 16px;
            padding: 32px 20px;
            text-align: center;
            width: 100%;
        }
        .ad-empty i, .empty-box i { font-size: 30px; color: #d1d5db; margin-bottom: 8px; display: block; }
        .ad-empty p, .empty-box p { font-size: 13px; color: var(--text-2); }
        .empty-box h3 { font-size: 14px; color: var(--text-1); margin-bottom: 4px; }

        /* ─── DEMANDE CARDS ─── */
        .d-card {
            background: var(--white);
            border-radius: 16px;
            padding: 13px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(10,48,96,0.06);
            transition: transform .12s;
        }
        .d-card:active { transform: scale(0.985); }

        .d-ico {
            width: 44px; height: 44px;
            border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .d-ico.ok  { background: #d1fae5; color: #059669; }
        .d-ico.ko  { background: #fee2e2; color: #dc2626; }
        .d-ico.pen { background: #fef3c7; color: #d97706; }

        .d-body  { flex: 1; min-width: 0; }
        .d-num   { font-size: 13px; font-weight: 700; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .d-meta  { font-size: 11px; color: var(--text-2); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .d-right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
        .d-badge {
            font-size: 10px; font-weight: 700;
            border-radius: 20px; padding: 3px 9px;
            white-space: nowrap;
        }
        .d-badge.ok  { background: #d1fae5; color: #065f46; }
        .d-badge.ko  { background: #fee2e2; color: #991b1b; }
        .d-badge.pen { background: #fef3c7; color: #92400e; }
        .d-chev { color: #d1d5db; font-size: 11px; }

        /* ─── FLASH MESSAGES ─── */
        .flash {
            margin: 12px 16px 0;
            border-radius: 13px;
            padding: 11px 14px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .flash.ok  { background: #d1fae5; color: #065f46; }
        .flash.err { background: #fee2e2; color: #991b1b; }

        /* ─── PAGINATION override ─── */
        .pagi-wrap { padding: 14px 0 0; display: flex; justify-content: center; }
        .pagination { gap: 4px; flex-wrap: wrap; justify-content: center; }
        .page-link { border-radius: 8px !important; border: 1px solid var(--border) !important; font-size: 12px !important; color: var(--blue-mid) !important; padding: 5px 11px !important; }
        .page-item.active .page-link { background: var(--blue-mid) !important; border-color: var(--blue-mid) !important; color: white !important; }

        /* ─── FIXED TAB BAR ─── */
        .tab-bar {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 430px;
            height: var(--tab-total);
            background: rgba(255,255,255,0.92);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            border-top: 0.5px solid rgba(0,0,0,0.10);
            display: flex;
            align-items: flex-start;
            justify-content: space-around;
            padding-top: 10px;
            padding-bottom: var(--sab);
            z-index: 200;
        }

        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 60px;
            cursor: pointer;
            text-decoration: none;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            padding: 0 6px;
            position: relative;
        }
        .tab-item i { font-size: 21px; color: #9ca3af; transition: color .15s, transform .15s; }
        .tab-item span { font-size: 10px; font-weight: 500; color: #9ca3af; transition: color .15s; }
        .tab-item.active i { color: var(--blue-mid); }
        .tab-item.active span { color: var(--blue-mid); font-weight: 700; }
        .tab-item:active i { transform: scale(0.88); }

        /* Center action pill tab */
        .tab-cta {
            min-width: 70px;
        }
        .tab-cta-pill {
            width: 56px; height: 32px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--teal), var(--blue-mid));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(0,180,216,.38);
            margin: 0 auto;
        }
        .tab-cta-pill i { font-size: 16px !important; color: white !important; }
        .tab-cta span { color: var(--teal) !important; font-weight: 700 !important; }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .fu  { animation: fadeUp .35s ease both; }
        .d1  { animation-delay:.04s; }
        .d2  { animation-delay:.08s; }
        .d3  { animation-delay:.13s; }
        .d4  { animation-delay:.18s; }

        /* ─── DESKTOP ─── */
        @media (min-width: 460px) {
            html { overflow: hidden; display:flex; align-items:center; justify-content:center; min-height:100vh; }
            body { max-width: 430px; width: 100%; height: 90vh; max-height: 860px; border-radius: 40px; overflow: hidden; box-shadow: 0 24px 80px rgba(0,0,0,0.35); }
            .app-shell { height: 100%; overflow-y: auto; overflow-x: hidden; }
            .tab-bar { border-radius: 0 0 40px 40px; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    {{-- Status bar safe area --}}
    <div class="status-bar" aria-hidden="true"></div>

    {{-- ─── HEADER ─── --}}
    <header class="app-header">
        <div class="deco-1" aria-hidden="true"></div>
        <div class="deco-2" aria-hidden="true"></div>

        <div class="h-row">
            <div class="h-logo">
                <img src="{{ asset('logo.png') }}" alt="IPM Mbaarum Koolute">
            </div>
            <div class="h-actions">
                <a href="#" class="icon-btn" title="Notifications" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                </a>
                <form action="{{ route('participant.logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="icon-btn" title="Déconnexion" aria-label="Se déconnecter">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="h-user">
            <p class="h-hello">Bonjour 👋</p>
            <h1 class="h-name">{{ $salarie->prenom }} {{ $salarie->nom }}</h1>
            <div class="h-badge">
                <span class="h-dot" aria-hidden="true"></span>
                {{ $salarie->matricule }}
                @if($salarie->entreprise)
                    &nbsp;·&nbsp; {{ Str::limit($salarie->entreprise->nom, 24) }}
                @endif
            </div>
        </div>
    </header>

    {{-- ─── SCROLL CONTENT ─── --}}
    <div class="scroll-content">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flash ok fu" role="alert"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash err fu" role="alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        {{-- Stats card (overlaps header) --}}
        @php
            $total    = $salarie->demandes()->count();
            $approved = $salarie->demandes()->whereIn('statut', ['approuvée','approuvee','validée','validee'])->count();
            $pending  = $salarie->demandes()->whereNotIn('statut', ['approuvée','approuvee','validée','validee','rejetée','rejetee'])->count();
        @endphp

        <div class="stats-card fu d1">
            <div class="stat-col">
                <div class="stat-num blue">{{ $total }}</div>
                <div class="stat-lbl">Total</div>
            </div>
            <div class="stat-col">
                <div class="stat-num green">{{ $approved }}</div>
                <div class="stat-lbl">Approuvées</div>
            </div>
            <div class="stat-col">
                <div class="stat-num amber">{{ $pending }}</div>
                <div class="stat-lbl">En attente</div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="section fu d2">
            <a href="{{ route('participant.demandes.create') }}" class="primary-btn">
                <i class="fas fa-plus-circle"></i>
                Nouvelle demande
            </a>
        </div>

        {{-- Mes informations --}}
        <div class="section fu d2">
            <div class="sec-head">
                <div class="sec-title">
                    <span class="sec-icon si-blue"><i class="fas fa-id-card"></i></span>
                    Mes informations
                </div>
            </div>
            <div class="info-card">
                <div class="info-row">
                    <div class="i-icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="i-lbl">Nom complet</div>
                        <div class="i-val">{{ $salarie->prenom }} {{ $salarie->nom }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="i-icon"><i class="fas fa-hashtag"></i></div>
                    <div>
                        <div class="i-lbl">Matricule</div>
                        <div class="i-val">{{ $salarie->matricule }}</div>
                    </div>
                </div>
                @if($salarie->entreprise)
                <div class="info-row">
                    <div class="i-icon"><i class="fas fa-building"></i></div>
                    <div style="min-width:0;flex:1">
                        <div class="i-lbl">Entreprise</div>
                        <div class="i-val">{{ $salarie->entreprise->nom }}</div>
                    </div>
                </div>
                @endif
                @if(!empty($salarie->email))
                <div class="info-row">
                    <div class="i-icon"><i class="fas fa-envelope"></i></div>
                    <div style="min-width:0;flex:1">
                        <div class="i-lbl">Email</div>
                        <div class="i-val">{{ $salarie->email }}</div>
                    </div>
                </div>
                @endif
                <div class="info-row">
                    <div class="i-icon" style="background:rgba(6,214,160,0.10);color:var(--green)">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="i-lbl">Statut</div>
                        <div class="i-val" style="color:{{ strtolower($salarie->statut ?? '') === 'actif' ? 'var(--green)' : 'var(--red)' }}">
                            {{ ucfirst($salarie->statut ?? 'Inconnu') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ayants droit --}}
        <div class="section fu d3">
            <div class="sec-head">
                <div class="sec-title">
                    <span class="sec-icon si-teal"><i class="fas fa-users"></i></span>
                    Ayants droit
                    <span class="count-pill">{{ $salarie->ayantsDroit->count() }}</span>
                </div>
            </div>

            @if($salarie->ayantsDroit->count() > 0)
                <div class="ad-scroll">
                    @foreach($salarie->ayantsDroit as $ad)
                        <div class="ad-card">
                            <div class="ad-av">{{ strtoupper(substr($ad->prenom ?? '?', 0, 1)) }}</div>
                            <div class="ad-name">{{ $ad->prenom }} {{ $ad->nom }}</div>
                            <div class="ad-lien">{{ $ad->lien_parente ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ad-empty">
                    <i class="fas fa-user-plus"></i>
                    <p>Aucun ayant droit enregistré</p>
                </div>
            @endif
        </div>

        {{-- Historique demandes --}}
        <div class="section fu d4">
            <div class="sec-head">
                <div class="sec-title">
                    <span class="sec-icon si-green"><i class="fas fa-file-medical"></i></span>
                    Mes demandes
                </div>
            </div>

            @if($demandes->count() > 0)
                @foreach($demandes as $d)
                    @php
                        $s      = strtolower($d->statut ?? '');
                        $isOk   = in_array($s, ['approuvée','approuvee','validée','validee']);
                        $isKo   = in_array($s, ['rejetée','rejetee']);
                        $cls    = $isOk ? 'ok' : ($isKo ? 'ko' : 'pen');
                        $ico    = $isOk ? 'fa-circle-check' : ($isKo ? 'fa-circle-xmark' : 'fa-hourglass-half');
                        $label  = $isOk ? 'Approuvée' : ($isKo ? 'Rejetée' : 'En attente');
                    @endphp
                    <div class="d-card">
                        <div class="d-ico {{ $cls }}">
                            <i class="fas {{ $ico }}"></i>
                        </div>
                        <div class="d-body">
                            <div class="d-num">{{ $d->numero_demande ?? '—' }}</div>
                            <div class="d-meta">
                                {{ $d->typeDemande->libelle ?? 'Type inconnu' }}
                                @if($d->date_demande)
                                    · {{ \Carbon\Carbon::parse($d->date_demande)->format('d/m/Y') }}
                                @endif
                                @if($d->id_ayant_droit && $d->ayantDroit)
                                    · {{ $d->ayantDroit->prenom }}
                                @endif
                            </div>
                        </div>
                        <div class="d-right">
                            <span class="d-badge {{ $cls }}">{{ $label }}</span>
                            <i class="fas fa-chevron-right d-chev"></i>
                        </div>
                    </div>
                @endforeach

                @if($demandes->hasPages())
                    <div class="pagi-wrap">
                        {{ $demandes->links() }}
                    </div>
                @endif
            @else
                <div class="empty-box">
                    <i class="fas fa-folder-open"></i>
                    <h3>Aucune demande</h3>
                    <p>Vous n'avez pas encore soumis de demande.</p>
                </div>
            @endif
        </div>

        <div style="height: 8px"></div>
    </div>{{-- /scroll-content --}}
</div>{{-- /app-shell --}}

{{-- ─── TAB BAR (fixed) ─── --}}
<nav class="tab-bar" aria-label="Navigation principale">
    <a href="{{ route('participant.dashboard') }}" class="tab-item active" aria-current="page">
        <i class="fas fa-house-chimney"></i>
        <span>Accueil</span>
    </a>

    <a href="{{ route('participant.demandes.create') }}" class="tab-item tab-cta">
        <div class="tab-cta-pill" aria-hidden="true">
            <i class="fas fa-plus"></i>
        </div>
        <span>Demande</span>
    </a>

    <form action="{{ route('participant.logout') }}" method="POST" style="margin:0">
        @csrf
        <button type="submit" class="tab-item">
            <i class="fas fa-right-from-bracket"></i>
            <span>Quitter</span>
        </button>
    </form>
</nav>
</body>
</html>
