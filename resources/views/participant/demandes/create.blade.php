<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a3060">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Nouvelle Prise en Charge — IPM</title>
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
            padding: 22px 18px;
            box-shadow: 0 4px 20px rgba(10,48,96,0.06);
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 14px; font-weight: 700; color: var(--blue-deep); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }
        .card-title i { color: var(--teal); }

        .form-group { margin-bottom: 16px; }
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
            padding: 13px 14px;
            font-size: 14px;
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
        textarea.f-control { resize: vertical; min-height: 80px; }
        
        .invalid-feedback { font-size: 12px; color: var(--red); margin-top: 5px; font-weight: 500; }
        .form-hint { font-size: 11px; color: var(--text-2); margin-top: 6px; display: flex; align-items: center; gap: 5px; }

        /* ─── TYPE TILES ─── */
        .type-tiles-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }
        .type-tile {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            background: #fafbfc;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .type-tile.active {
            border-color: var(--teal);
            background: #f0faff;
            box-shadow: 0 4px 14px rgba(0,180,216,0.15);
        }
        .type-tile input[type="radio"] {
            display: none;
        }
        .tile-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .tile-icon.bon { background: rgba(6, 214, 160, 0.15); color: #059669; }
        .tile-icon.feuille { background: rgba(15, 76, 129, 0.12); color: var(--blue-mid); }
        .tile-icon.lettre { background: rgba(0, 180, 216, 0.15); color: var(--teal); }

        .tile-content { flex: 1; }
        .tile-title { font-size: 14px; font-weight: 700; color: var(--text-1); margin-bottom: 2px; }
        .tile-subtitle { font-size: 12px; color: var(--text-2); }
        
        .tile-check {
            width: 22px; height: 22px; border-radius: 50%; border: 2px solid #cbd5e1; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 11px; transition: all 0.2s;
        }
        .type-tile.active .tile-check {
            background: var(--teal); border-color: var(--teal);
        }

        /* ─── ACTE PILLS ─── */
        .acte-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .acte-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-1);
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.15s;
        }
        .acte-pill.selected {
            background: #e0f2fe; border-color: var(--teal); color: var(--blue-deep);
        }
        .acte-pill input { display: none; }
        
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
            display: flex; align-items: center; justify-content: center; width: 100%; height: 50px;
            background: #e2e8f0; color: var(--text-2); font-size: 15px; font-weight: 600; text-decoration: none;
            border-radius: 16px; margin-top: 10px; transition: background .2s;
        }
        .btn-cancel:active { background: #cbd5e1; }

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
        <h1 class="header-title">Nouvelle Prise en Charge</h1>
        <div class="icon-btn hidden"></div>
    </header>

    <div class="scroll-content">
        
        @if(session('error'))
            <div class="form-group fu">
                <div style="background: #fee2e2; color: #991b1b; padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 500;">
                    <i class="fas fa-circle-exclamation me-1"></i> {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="form-group fu">
                <div style="background: #fee2e2; color: #991b1b; padding: 12px 14px; border-radius: 12px; font-size: 12px;">
                    <div style="font-weight:700; margin-bottom:4px;"><i class="fas fa-triangle-exclamation"></i> Informations requises :</div>
                    <ul style="margin:0; padding-left:16px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('participant.demandes.store') }}" method="POST" class="fu" id="participantDemandeForm">
            @csrf
            
            <!-- 1. SÉLECTION VISUELLE DU TYPE DE DEMANDE -->
            <div class="form-card">
                <div class="card-title">
                    <i class="fas fa-shapes"></i>
                    <span>Type de prise en charge</span>
                </div>
                
                <div class="type-tiles-grid">
                    @foreach($typesDemande as $type)
                        @php
                            $tlib = strtolower($type->libelle);
                            $isBon = str_contains($tlib, 'bon');
                            $isFeuille = str_contains($tlib, 'feuille');
                            $iconClass = $isBon ? 'fa-pills' : ($isFeuille ? 'fa-stethoscope' : 'fa-hospital');
                            $themeClass = $isBon ? 'bon' : ($isFeuille ? 'feuille' : 'lettre');
                            $subText = $isBon ? 'Pharmacie & Optique (Ordonnance)' : ($isFeuille ? 'Consultation Cabinet / Clinique' : 'Hospitalisation & Actes lourds');
                        @endphp
                        <label class="type-tile {{ old('id_type_demande', 1) == $type->id_type_demande ? 'active' : '' }}" onclick="selectParticipantType(this)">
                            <input type="radio" 
                                   name="id_type_demande" 
                                   value="{{ $type->id_type_demande }}" 
                                   data-name="{{ $tlib }}"
                                   {{ old('id_type_demande', 1) == $type->id_type_demande ? 'checked' : '' }} 
                                   required>
                            <div class="tile-icon {{ $themeClass }}">
                                <i class="fas {{ $iconClass }}"></i>
                            </div>
                            <div class="tile-content">
                                <div class="tile-title">{{ $type->libelle }}</div>
                                <div class="tile-subtitle">{{ $subText }}</div>
                            </div>
                            <div class="tile-check">
                                <i class="fas fa-check"></i>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- 2. BÉNÉFICIAIRE -->
            <div class="form-card">
                <div class="card-title">
                    <i class="fas fa-user-shield"></i>
                    <span>Bénéficiaire des soins</span>
                </div>

                <div class="form-group">
                    <label for="beneficiaire" class="f-label">Pour qui est cette demande ? <span>*</span></label>
                    <div class="f-select-wrap">
                        <select name="beneficiaire" id="beneficiaire" class="f-control @error('beneficiaire') is-invalid @enderror" required>
                            <option value="salarie" {{ old('beneficiaire') == 'salarie' ? 'selected' : '' }}>
                                Moi-même ({{ $salarie->prenom }} {{ $salarie->nom }})
                            </option>
                            @if($ayantsDroit->count() > 0)
                                <optgroup label="Mes ayants droit déclarés">
                                    @foreach($ayantsDroit as $ad)
                                        @php
                                            $ageAd = $ad->age ? ' — ' . $ad->age . ' ans' : '';
                                        @endphp
                                        <option value="ayant_droit_{{ $ad->id_ayant_droit }}" {{ old('beneficiaire') == 'ayant_droit_'.$ad->id_ayant_droit ? 'selected' : '' }}>
                                            {{ $ad->prenom }} {{ $ad->nom }} ({{ $ad->lien_parente }}{{ $ageAd }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    @error('beneficiaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- 3. CONFIGURATION SPÉCIFIQUE DYNAMIQUE -->
            <div class="form-card">
                <div class="card-title">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Détails & Prestataire</span>
                </div>

                <!-- SECTION BON DE COMMANDE : PHARMACIE + ORDONNANCE -->
                <div id="p_section_bon">
                    <div class="form-group">
                        <label for="id_pharmacie" class="f-label">Pharmacie conventionnée <span>*</span></label>
                        <div class="f-select-wrap">
                            <select name="id_pharmacie" id="id_pharmacie" class="f-control @error('id_pharmacie') is-invalid @enderror">
                                <option value="">Choisir une pharmacie</option>
                                @foreach($pharmacies as $pharmacie)
                                    <option value="{{ $pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK }}" {{ old('id_pharmacie') == ($pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK) ? 'selected' : '' }}>
                                        💊 {{ $pharmacie->nom ?? $pharmacie->NOMPHARM }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-hint"><i class="fas fa-check-circle" style="color:var(--green)"></i> Uniquement les pharmacies agréées IPM.</div>
                        @error('id_pharmacie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:10px; margin-top:14px;">
                        <div class="form-group">
                            <label for="date_ordonnance" class="f-label">Date ordonnance <span>*</span></label>
                            <input type="date" 
                                   name="date_ordonnance" 
                                   id="date_ordonnance" 
                                   class="f-control @error('date_ordonnance') is-invalid @enderror" 
                                   value="{{ old('date_ordonnance', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" 
                                   min="{{ now()->subMonths(6)->toDateString() }}">
                            @error('date_ordonnance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="nombre_articles" class="f-label">Nb articles <span>*</span></label>
                            <input type="number" 
                                   name="nombre_articles" 
                                   id="nombre_articles" 
                                   class="f-control @error('nombre_articles') is-invalid @enderror" 
                                   value="{{ old('nombre_articles', 1) }}" 
                                   min="1" 
                                   max="20">
                            @error('nombre_articles') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- SECTION PRATICIEN : POUR FEUILLE ET LETTRE -->
                <div id="p_section_praticien" style="display: none;">
                    <div class="form-group">
                        <label for="id_praticien" class="f-label">Médecin / Clinique conventionné <span>*</span></label>
                        <div class="f-select-wrap">
                            <select name="id_praticien" id="id_praticien" class="f-control @error('id_praticien') is-invalid @enderror">
                                <option value="">Choisir un praticien</option>
                                @foreach($praticiens as $praticien)
                                    <option value="{{ $praticien->id_praticien ?? $praticien->PRCLEUNIK }}" {{ old('id_praticien') == ($praticien->id_praticien ?? $praticien->PRCLEUNIK) ? 'selected' : '' }}>
                                        🩺 {{ $praticien->nom ?? $praticien->NOMPRAT }} {{ $praticien->specialite ? '('.$praticien->specialite.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-hint"><i class="fas fa-check-circle" style="color:var(--teal)"></i> Médecin traitant ou spécialiste conventionné.</div>
                        @error('id_praticien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- SECTION ACTE : POUR LETTRE DE GARANTIE -->
                <div id="p_section_acte" style="display: none; margin-top:14px;">
                    <label class="f-label">Type d'acte requis <span>*</span></label>
                    <div class="acte-grid">
                        @foreach(['Hospitalisation', 'Radiologie', 'Analyses Médicales', 'Spécialité Médicale', 'Médecine Générale', 'Maternité / Accouchement', 'Optique Médicale', 'Consultation + Soins Dentaires', 'Consultation Ophtalmologie'] as $acte)
                            <label class="acte-pill {{ old('type_acte') == $acte ? 'selected' : '' }}" onclick="selectActe(this)">
                                <input type="radio" name="type_acte" value="{{ $acte }}" {{ old('type_acte', 'Hospitalisation') == $acte ? 'checked' : '' }}>
                                <span>{{ $acte }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- DESCRIPTION / MOTIF -->
                <div class="form-group" style="margin-top: 14px;">
                    <label for="description" class="f-label">Motif ou précisions complémentaires</label>
                    <textarea name="description" id="description" class="f-control" placeholder="Précisez la pathologie, les symptômes ou des détails utiles...">{{ old('description') }}</textarea>
                </div>
            </div>

            <button type="submit" class="primary-btn" id="p_submit_btn">
                <i class="fas fa-paper-plane"></i>
                <span>Soumettre la demande</span>
            </button>
            
            <a href="{{ route('participant.dashboard') }}" class="btn-cancel">
                Annuler
            </a>

        </form>
    </div>

</div>

{{-- ─── TAB BAR (fixed) ─── --}}
<nav class="tab-bar" aria-label="Navigation principale">
    <a href="{{ route('participant.dashboard') }}" class="tab-item">
        <i class="fas fa-house-chimney"></i>
        <span>Accueil</span>
    </a>

    <a href="{{ route('participant.demandes.create') }}" class="tab-item tab-cta active" aria-current="page">
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

<script>
function selectParticipantType(tileElement) {
    document.querySelectorAll('.type-tile').forEach(t => t.classList.remove('active'));
    tileElement.classList.add('active');
    
    const radio = tileElement.querySelector('input[type="radio"]');
    radio.checked = true;
    
    applyParticipantTypeFields(radio.getAttribute('data-name') || '');
}

function selectActe(pillElement) {
    document.querySelectorAll('.acte-pill').forEach(p => p.classList.remove('selected'));
    pillElement.classList.add('selected');
    pillElement.querySelector('input').checked = true;
}

function applyParticipantTypeFields(typeName) {
    typeName = typeName.toLowerCase();
    const isBon = typeName.includes('bon');
    const isFeuille = typeName.includes('feuille');
    const isLettre = typeName.includes('lettre');

    const secBon = document.getElementById('p_section_bon');
    const secPrat = document.getElementById('p_section_praticien');
    const secActe = document.getElementById('p_section_acte');

    const phSelect = document.getElementById('id_pharmacie');
    const prSelect = document.getElementById('id_praticien');
    const dOrd = document.getElementById('date_ordonnance');
    const nbArt = document.getElementById('nombre_articles');
    const btnSpan = document.querySelector('#p_submit_btn span');

    if (isBon) {
        secBon.style.display = 'block';
        secPrat.style.display = 'none';
        secActe.style.display = 'none';

        phSelect.setAttribute('required', 'required');
        dOrd.setAttribute('required', 'required');
        nbArt.setAttribute('required', 'required');
        prSelect.removeAttribute('required');
        prSelect.value = '';

        btnSpan.textContent = 'Demander un Bon de Commande';
    } else {
        secBon.style.display = 'none';
        phSelect.removeAttribute('required');
        phSelect.value = '';
        dOrd.removeAttribute('required');
        nbArt.removeAttribute('required');

        secPrat.style.display = 'block';
        prSelect.setAttribute('required', 'required');

        if (isFeuille) {
            secActe.style.display = 'none';
            btnSpan.textContent = 'Demander une Feuille de Maladie';
        } else if (isLettre) {
            secActe.style.display = 'block';
            btnSpan.textContent = 'Demander une Lettre de Garantie';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const activeRadio = document.querySelector('input[name="id_type_demande"]:checked');
    if (activeRadio) {
        applyParticipantTypeFields(activeRadio.getAttribute('data-name') || '');
    }
});
</script>
</body>
</html>
