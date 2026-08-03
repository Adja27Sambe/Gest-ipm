<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a3060">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Nouvelle Demande — IPM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
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
            --sat: env(safe-area-inset-top, 0px);
            --sab: env(safe-area-inset-bottom, 16px);
            --tab-h: 58px;
            --tab-total: calc(var(--tab-h) + var(--sab));
        }

        html { height: 100%; background: #1a2a3a; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-1); min-height: 100%; }

        /* ─── APP SHELL ─── */
        .app-shell {
            max-width: 430px; margin: 0 auto; min-height: 100dvh; background: var(--bg); position: relative; display: flex; flex-direction: column;
        }

        /* ─── STATUS BAR ─── */
        .status-bar { background: var(--blue-deep); height: var(--sat); flex-shrink: 0; }

        /* ─── HEADER ─── */
        .app-header {
            background: linear-gradient(145deg, var(--blue-deep) 0%, var(--blue-mid) 65%, var(--blue-light) 100%);
            padding: 16px 20px 24px;
            flex-shrink: 0; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: space-between;
        }
        .header-title { color: white; font-size: 18px; font-weight: 700; flex: 1; text-align: center; }
        .icon-btn {
            width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.13); border: 1px solid rgba(255,255,255,0.20);
            color: white; font-size: 15px; display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
            transition: background .2s; z-index: 10; position: relative;
        }
        .icon-btn.hidden { visibility: hidden; }
        .icon-btn:active { background: rgba(255,255,255,0.25); }
        
        .app-header .deco-1 { position: absolute; width: 180px; height: 180px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.08); top: -50px; right: -50px; pointer-events: none; }

        /* ─── SCROLL CONTENT ─── */
        .scroll-content { flex: 1; padding: 20px 16px calc(var(--tab-total) + 20px); }

        /* ─── FORM ELEMENTS ─── */
        .form-card {
            background: var(--white);
            border-radius: 20px;
            padding: 24px 20px;
            box-shadow: 0 4px 20px rgba(10,48,96,0.08);
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group:last-child { margin-bottom: 0; }
        
        .f-label {
            display: block; font-size: 13px; font-weight: 600; color: var(--blue-deep); margin-bottom: 8px;
        }
        .f-label span { color: var(--red); }
        
        .f-select-wrap {
            position: relative;
        }
        .f-select-wrap::after {
            content: '\f107'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-2); pointer-events: none;
        }
        
        .f-control {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 14px 14px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: var(--text-1);
            transition: all .2s;
            appearance: none;
            outline: none;
        }
        .f-control:focus {
            background: white; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(0,180,216,0.1);
        }
        .f-control.is-invalid { border-color: var(--red); background: #fef2f2; }
        
        select.f-control { padding-right: 40px; }
        textarea.f-control { resize: vertical; min-height: 100px; }
        
        .invalid-feedback { font-size: 12px; color: var(--red); margin-top: 5px; font-weight: 500; }
        .form-hint { font-size: 11px; color: var(--text-2); margin-top: 6px; display: flex; align-items: center; gap: 5px; }
        
        /* ─── PRIMARY BUTTON ─── */
        .primary-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 54px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--blue-mid) 100%);
            color: white; font-size: 16px; font-weight: 700; border: none; border-radius: 16px;
            cursor: pointer; box-shadow: 0 6px 20px rgba(0,180,216,.30); transition: transform .12s, box-shadow .12s;
            position: relative; overflow: hidden;
        }
        .primary-btn::after {
            content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,.10), transparent);
        }
        .primary-btn:active { transform: scale(0.97); box-shadow: 0 3px 10px rgba(0,180,216,.20); }

        .btn-cancel {
            display: flex; align-items: center; justify-content: center; width: 100%; height: 54px;
            background: #f1f4f8; color: var(--text-2); font-size: 15px; font-weight: 600; text-decoration: none;
            border-radius: 16px; margin-top: 12px; transition: background .2s;
        }
        .btn-cancel:active { background: #e5e7eb; }

        /* ─── TAB BAR (fixed) ─── */
        .tab-bar {
            position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; height: var(--tab-total);
            background: rgba(255,255,255,0.92); backdrop-filter: blur(20px); border-top: 0.5px solid rgba(0,0,0,0.10);
            display: flex; align-items: flex-start; justify-content: space-around; padding-top: 10px; padding-bottom: var(--sab); z-index: 200;
        }
        .tab-item {
            display: flex; flex-direction: column; align-items: center; gap: 3px; min-width: 60px; cursor: pointer; text-decoration: none;
            background: none; border: none; padding: 0 6px; font-family: 'Inter', sans-serif;
        }
        .tab-item i { font-size: 21px; color: #9ca3af; transition: color .15s, transform .15s; }
        .tab-item span { font-size: 10px; font-weight: 500; color: #9ca3af; transition: color .15s; }
        .tab-item.active i, .tab-item.active span { color: var(--blue-mid); }
        .tab-item:active i { transform: scale(0.88); }
        
        .tab-cta { min-width: 70px; }
        .tab-cta-pill {
            width: 56px; height: 32px; border-radius: 16px; background: linear-gradient(135deg, var(--teal), var(--blue-mid));
            display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,180,216,.38); margin: 0 auto;
        }
        .tab-cta-pill i { font-size: 16px !important; color: white !important; }
        .tab-cta span { color: var(--teal) !important; font-weight: 700 !important; }

        /* ─── ANIMATION ─── */
        .fu { animation: fadeUp .4s ease both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }

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

    <div class="status-bar" aria-hidden="true"></div>

    <header class="app-header">
        <div class="deco-1" aria-hidden="true"></div>
        <a href="{{ route('participant.dashboard') }}" class="icon-btn" aria-label="Retour">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="header-title">Nouvelle Demande</h1>
        <div class="icon-btn hidden"></div>
    </header>

    <div class="scroll-content">
        
        @if(session('error'))
            <div class="form-group fu">
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 500;">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            </div>
        @endif

        <form action="{{ route('participant.demandes.store') }}" method="POST" class="fu">
            @csrf
            
            <div class="form-card">
                
                <div class="form-group">
                    <label for="id_type_demande" class="f-label">Type de demande <span>*</span></label>
                    <div class="f-select-wrap">
                        <select name="id_type_demande" id="id_type_demande" class="f-control @error('id_type_demande') is-invalid @enderror" required>
                            <option value="">Sélectionnez un type</option>
                            @foreach($typesDemande as $type)
                                <option value="{{ $type->id_type_demande }}" {{ old('id_type_demande') == $type->id_type_demande ? 'selected' : '' }}>
                                    {{ $type->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('id_type_demande') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="beneficiaire" class="f-label">Bénéficiaire <span>*</span></label>
                    <div class="f-select-wrap">
                        <select name="beneficiaire" id="beneficiaire" class="f-control @error('beneficiaire') is-invalid @enderror" required>
                            <option value="salarie" {{ old('beneficiaire') == 'salarie' ? 'selected' : '' }}>Moi-même ({{ $salarie->prenom }} {{ $salarie->nom }})</option>
                            @if($ayantsDroit->count() > 0)
                                <optgroup label="Mes ayants droit">
                                    @foreach($ayantsDroit as $ad)
                                        <option value="ayant_droit_{{ $ad->id_ayant_droit }}" {{ old('beneficiaire') == 'ayant_droit_'.$ad->id_ayant_droit ? 'selected' : '' }}>
                                            {{ $ad->prenom }} {{ $ad->nom }} ({{ $ad->lien_parente }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    @error('beneficiaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-card">
                <div class="form-group" id="praticien_container">
                    <label for="id_praticien" class="f-label">Praticien (Médecin/Clinique)</label>
                    <div class="f-select-wrap">
                        <select name="id_praticien" id="id_praticien" class="f-control @error('id_praticien') is-invalid @enderror">
                            <option value="">Sélectionnez un praticien (optionnel)</option>
                            @foreach($praticiens as $praticien)
                                <option value="{{ $praticien->id_praticien }}" {{ old('id_praticien') == $praticien->id_praticien ? 'selected' : '' }}>
                                    {{ $praticien->nom }} {{ $praticien->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-hint"><i class="fas fa-info-circle"></i> Utile pour une Lettre de Garantie</div>
                </div>
                
                <div class="form-group mt-4" id="pharmacie_container">
                    <label for="id_pharmacie" class="f-label">Pharmacie</label>
                    <div class="f-select-wrap">
                        <select name="id_pharmacie" id="id_pharmacie" class="f-control @error('id_pharmacie') is-invalid @enderror">
                            <option value="">Sélectionnez une pharmacie (optionnel)</option>
                            @foreach($pharmacies as $pharmacie)
                                <option value="{{ $pharmacie->id_pharmacie }}" {{ old('id_pharmacie') == $pharmacie->id_pharmacie ? 'selected' : '' }}>
                                    {{ $pharmacie->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-hint"><i class="fas fa-info-circle"></i> Utile pour un Bon de Commande</div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-group">
                    <label for="description" class="f-label">Informations complémentaires</label>
                    <textarea name="description" id="description" class="f-control" placeholder="Précisez le motif ou d'autres informations utiles...">{{ old('description') }}</textarea>
                </div>
            </div>

            <button type="submit" class="primary-btn">
                Soumettre la demande
            </button>
            
            <a href="{{ route('participant.dashboard') }}" class="btn-cancel">
                Annuler
            </a>

        </form>
    </div>

</div>

{{-- ─── TAB BAR (fixed) ─── --}}
<nav class="tab-bar" aria-label="Navigation principale">
    <a href="{{ route('participant.dashboard') }}" class="tab-item" aria-current="page">
        <i class="fas fa-house-chimney"></i>
        <span>Accueil</span>
    </a>

    <a href="{{ route('participant.demandes.create') }}" class="tab-item tab-cta active">
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
