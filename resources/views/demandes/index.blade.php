@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Tableau de bord - Prises en charge médicales</h2>
    @canedit
    <button class="btn btn-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#createDemandeModal">
        <i class="bi bi-plus-lg me-2"></i> Nouvelle Prise en charge
    </button>
    @endcanedit
</div>

<!-- KPIs Dashboard -->
@if(isset($stats))
<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-files text-primary fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Prises en charge</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-circle text-success fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">Approuvées</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['approuvees'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-arrow-repeat text-info fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">En Cours</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['en_cours'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-hourglass-split text-warning fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">En Attente</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['en_attente'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('demandes.index') }}" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique (n° prise en charge, bénéficiaire, matricule, type)..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select bg-light border-0">
                    <option value="">Tous les statuts</option>
                    <option value="Approuvée" {{ request('statut') == 'Approuvée' ? 'selected' : '' }}>Approuvée</option>
                    <option value="En cours" {{ request('statut') == 'En cours' ? 'selected' : '' }}>En cours</option>
                    <option value="Rejetée" {{ request('statut') == 'Rejetée' ? 'selected' : '' }}>Rejetée</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end">
                <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 100px;" onchange="this.form.submit()">
                    <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 / page</option>
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date & Heure</th>
                        <th>Type de Prise en charge</th>
                        <th>N° Prise en charge</th>
                        <th>Bénéficiaire (Participant/Ayant-droit)</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    @php
                        $docNumber = '-';
                        $docType = strtolower($demande->typeDemande->libelle ?? '');
                        if(str_contains($docType, 'bon') && $demande->bonCommande) $docNumber = $demande->bonCommande->numero_bon;
                        if(str_contains($docType, 'feuille') && $demande->feuilleMaladie) $docNumber = $demande->feuilleMaladie->numero_feuille;
                        if(str_contains($docType, 'lettre') && $demande->lettreGarantie) $docNumber = $demande->lettreGarantie->numero_lettre;
                        
                        $beneficiaire = $demande->salarie->prenom . ' ' . $demande->salarie->nom;
                        if($demande->ayantDroit) {
                            $beneficiaire = $demande->ayantDroit->prenom . ' ' . $demande->ayantDroit->nom . ' (Ayant-droit)';
                        }
                    @endphp
                    <tr>
                        <td class="ps-4 fw-medium text-nowrap">
                            <i class="bi bi-clock me-1 text-muted"></i>
                            {{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y à H:i') }}
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">
                                {{ $demande->typeDemande->libelle ?? 'Inconnu' }}
                            </span>
                        </td>
                        <td class="text-muted fw-bold">{{ $docNumber }}</td>
                        <td>{{ $beneficiaire }}</td>
                        <td>
                            @if($demande->statut == 'Approuvée')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Approuvée</span>
                            @elseif($demande->statut == 'En cours' || $demande->statut == 'en_attente')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">En cours</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $demande->statut }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions sur la prise en charge">
                                <!-- Imprimer PDF (Accessible au lecteur) -->
                                <a href="{{ route('demandes.pdf', $demande->id_demande) }}" target="_blank" class="btn btn-outline-danger d-inline-flex align-items-center" title="Imprimer le PDF">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>
                                </a>
                                @canedit
                                <!-- Supprimer -->
                                <form action="{{ route('demandes.destroy', $demande->id_demande) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette prise en charge ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary border-start-0" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcanedit
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-folder2-open fs-1 d-block mb-3 opacity-50"></i>
                            Aucune prise en charge générée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage de {{ $demandes->firstItem() ?? 0 }} à {{ $demandes->lastItem() ?? 0 }} sur {{ $demandes->total() }} prises en charge
    </div>
    <div>
        {{ $demandes->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
@canedit
<!-- Modal Création Demande -->
<div class="modal fade" id="createDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouvelle Prise en charge médical</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('demandes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="bi bi-ui-radios-grid text-primary me-1"></i> Type de Prise en Charge *
                            </label>
                            <select name="id_type_demande" id="type_demande_select" class="form-select form-select-lg fw-semibold" required onchange="toggleFields()">
                                <option value="">-- Choisissez le type de document --</option>
                                @foreach($typesDemande as $td)
                                    @php
                                        $tlib = strtolower($td->libelle);
                                        $iconPrefix = str_contains($tlib, 'bon') ? '💊 ' : (str_contains($tlib, 'feuille') ? '🩺 ' : '🏥 ');
                                        $descSuffix = str_contains($tlib, 'bon') ? ' — Pharmacie & Optique (Ordonnance)' : (str_contains($tlib, 'feuille') ? ' — Consultation Médicale' : ' — Hospitalisation & Actes Lourds');
                                    @endphp
                                    <option value="{{ $td->id_type_demande }}" data-name="{{ $tlib }}">{{ $iconPrefix . $td->libelle . $descSuffix }}</option>
                                @endforeach
                            </select>
                            <div id="type_helper_alert" class="mt-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i> Sélectionnez un type pour voir les prestataires et champs requis.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">
                                <i class="bi bi-upc-scan text-primary me-1"></i>Matricule du Participant *
                            </label>
                            <div class="position-relative">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-search text-primary"></i>
                                    </span>
                                    <input type="text" 
                                           id="modal_matricule_input" 
                                           class="form-control border-start-0" 
                                           placeholder="Saisir matricule, nom ou prénom..." 
                                           autocomplete="off"
                                           oninput="handleModalMatriculeSearch(this.value)"
                                           onfocus="if(this.value.trim().length >= 1) handleModalMatriculeSearch(this.value)">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" id="modal_btn_clear_matricule" onclick="clearModalParticipant()" style="display: none;" title="Effacer">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="id_salarie" id="modal_selected_id_salarie" required>

                                <!-- Menu d'autocomplétion -->
                                <div id="modal_autocomplete_dropdown" class="dropdown-menu shadow-lg border-0 rounded-3 w-100 p-2 mt-1 position-absolute" style="max-height: 250px; overflow-y: auto; z-index: 1070; display: none;">
                                </div>
                            </div>

                            <!-- Carte récapitulative -->
                            <div id="modal_participant_badge" class="p-2 px-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 d-flex align-items-center justify-content-between mt-2" style="display: none;">
                                <div class="small">
                                    <span class="badge bg-primary font-monospace me-1" id="modal_badge_mat">MAT</span>
                                    <strong class="text-dark" id="modal_badge_nom">Nom Prénom</strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none" onclick="focusModalMatricule()">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Bénéficiaire des soins *</label>
                            <select name="id_ayant_droit" id="ayant_droit_select" class="form-select">
                                <option value="">Pour le participant lui-même</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Motif de la prise en charge</label>
                            <textarea name="motif" class="form-control" rows="2" placeholder="Ex: Consultation, ordonnance médicale, hospitalisation..."></textarea>
                        </div>

                        <!-- Praticien (Médecin / Clinique) -->
                        <div class="col-md-12 dynamic-field" id="field_praticien" style="display: none;">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-stethoscope text-primary me-1"></i> Praticien / Clinique conventionné *
                            </label>
                            <select name="id_praticien" id="praticien_select" class="form-select">
                                <option value="">Sélectionner un praticien</option>
                                @foreach($praticiens as $praticien)
                                    <option value="{{ $praticien->id_praticien ?? $praticien->PRCLEUNIK }}">
                                        🩺 {{ $praticien->nom ?? $praticien->NOMPRAT }} {{ $praticien->specialite ? '('.$praticien->specialite.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted"><i class="bi bi-shield-check text-success me-1"></i>Exclut strictement les pharmacies.</div>
                        </div>

                        <!-- Pharmacie -->
                        <div class="col-md-12 dynamic-field" id="field_pharmacie" style="display: none;">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-capsule text-success me-1"></i> Pharmacie / Opticien conventionné *
                            </label>
                            <select name="id_pharmacie" id="pharmacie_select" class="form-select">
                                <option value="">Sélectionner une pharmacie</option>
                                @foreach($pharmacies as $pharmacie)
                                    <option value="{{ $pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK }}">
                                        💊 {{ $pharmacie->nom ?? $pharmacie->NOMPHARM }} {{ $pharmacie->adresse ? '('.$pharmacie->adresse.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted"><i class="bi bi-shield-check text-success me-1"></i>Exclut strictement les médecins.</div>
                        </div>

                        <!-- Champs spécifiques Bon de Commande -->
                        <div class="col-md-6 dynamic-field" id="field_date_ordonnance" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Date de l'ordonnance *</label>
                            <input type="date" name="date_ordonnance" id="date_ordonnance_input" class="form-control" max="{{ date('Y-m-d') }}" min="{{ now()->subMonths(6)->toDateString() }}">
                            <div class="form-text small text-muted">Ordonnance valide (max 6 mois)</div>
                        </div>
                        <div class="col-md-6 dynamic-field" id="field_nombre_articles" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Nombre d'articles autorisés *</label>
                            <input type="number" name="nombre_articles" id="nombre_articles_input" class="form-control" value="1" min="1" max="20">
                        </div>

                        <!-- Champ spécifique Lettre de Garantie -->
                        <div class="col-12 dynamic-field" id="field_choix_acte" style="display: none;">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-hospital text-info me-1"></i> Choix de l'acte pris en charge *
                            </label>
                            <div class="d-flex flex-wrap gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Hospitalisation" id="acte_hospitalisation">
                                    <label class="form-check-label fw-medium" for="acte_hospitalisation">🏨 Hospitalisation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Consultation" id="acte_consultation">
                                    <label class="form-check-label fw-medium" for="acte_consultation">🩺 Consultation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Radiologie" id="acte_radiologie">
                                    <label class="form-check-label fw-medium" for="acte_radiologie">🩻 Radiologie</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Analyse" id="acte_analyse">
                                    <label class="form-check-label fw-medium" for="acte_analyse">🧪 Analyse</label>
                                </div>
                            </div>
                        </div>

                        <!-- Champs spécifiques Feuille / Lettre -->
                        <div class="col-12 dynamic-field" id="field_observations" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Observations / Remarques</label>
                            <textarea name="observations" class="form-control" rows="2" placeholder="Précisions médicales, devis..."></textarea>
                        </div>

                        <div class="col-12 dynamic-field" id="field_diagnostic" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Diagnostic (Feuille de Maladie)</label>
                            <textarea name="diagnostic" class="form-control" rows="2" placeholder="Diagnostic ou motif clinique..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="modal_submit_btn">Générer la prise en charge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanedit

<script>
    function updateAyantsDroit() {
        const salarieSelect = document.getElementById('salarie_select');
        const ayantDroitSelect = document.getElementById('ayant_droit_select');
        
        ayantDroitSelect.innerHTML = '<option value="">Pour le participant lui-même</option>';
        
        const selectedOption = salarieSelect.options[salarieSelect.selectedIndex];
        if(!selectedOption.value) return;
        
        const ayantsDroit = JSON.parse(selectedOption.getAttribute('data-ayants-droit') || '[]');
        
        ayantsDroit.forEach(ad => {
            let ageStr = '';
            if (ad.date_naissance) {
                const ageDiff = Date.now() - new Date(ad.date_naissance).getTime();
                const age = Math.abs(new Date(ageDiff).getUTCFullYear() - 1970);
                ageStr = ` (${age} ans)`;
            }
            ayantDroitSelect.innerHTML += `<option value="${ad.id_ayant_droit}">${ad.prenom} ${ad.nom} — ${ad.lien_parente || 'Ayant droit'}${ageStr}</option>`;
        });
    }

    function toggleFields() {
        const typeSelect = document.getElementById('type_demande_select');
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const docName = (selectedOption.getAttribute('data-name') || '').toLowerCase();
        const helperAlert = document.getElementById('type_helper_alert');
        const submitBtn = document.getElementById('modal_submit_btn');

        const isBon = docName.includes('bon');
        const isFeuille = docName.includes('feuille');
        const isLettre = docName.includes('lettre');

        // Textes d'aide
        if (isBon) {
            helperAlert.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i><strong>Bon de Commande :</strong> Ordonnance obligatoire (max 6 mois). Prestataire autorisé : <strong>Pharmacie uniquement</strong>.</span>';
            submitBtn.textContent = 'Générer le Bon de Commande';
        } else if (isFeuille) {
            helperAlert.innerHTML = '<span class="text-primary"><i class="bi bi-check-circle-fill me-1"></i><strong>Feuille de Maladie :</strong> Consultation médicale. Prestataire autorisé : <strong>Médecin / Clinique</strong>.</span>';
            submitBtn.textContent = 'Générer la Feuille de Maladie';
        } else if (isLettre) {
            helperAlert.innerHTML = '<span class="text-info"><i class="bi bi-check-circle-fill me-1"></i><strong>Lettre de Garantie :</strong> Accord préalable. Prestataire : <strong>Établissement de soins / Spécialiste</strong>.</span>';
            submitBtn.textContent = 'Générer la Lettre de Garantie';
        } else {
            helperAlert.innerHTML = '<i class="bi bi-info-circle me-1"></i> Sélectionnez un type pour voir les prestataires et champs requis.';
            submitBtn.textContent = 'Générer la prise en charge';
        }

        // Affichage des champs spécifiques
        document.getElementById('field_observations').style.display = (isFeuille || isLettre) ? 'block' : 'none';
        document.getElementById('field_diagnostic').style.display = isFeuille ? 'block' : 'none';
        
        document.getElementById('field_date_ordonnance').style.display = isBon ? 'block' : 'none';
        document.getElementById('field_nombre_articles').style.display = isBon ? 'block' : 'none';
        
        document.getElementById('field_choix_acte').style.display = isLettre ? 'block' : 'none';

        // Validation stricte : Pharmacie pour BC, Praticien pour FM/LG
        const pharmacieSelect = document.getElementById('pharmacie_select');
        const praticienSelect = document.getElementById('praticien_select');
        const dateInput = document.getElementById('date_ordonnance_input');
        const nbInput = document.getElementById('nombre_articles_input');

        if (isBon) {
            document.getElementById('field_pharmacie').style.display = 'block';
            pharmacieSelect.setAttribute('required', 'required');
            dateInput.setAttribute('required', 'required');
            nbInput.setAttribute('required', 'required');
            
            document.getElementById('field_praticien').style.display = 'none';
            praticienSelect.removeAttribute('required');
            praticienSelect.value = '';
        } else if (isFeuille || isLettre) {
            document.getElementById('field_praticien').style.display = 'block';
            praticienSelect.setAttribute('required', 'required');
            
            document.getElementById('field_pharmacie').style.display = 'none';
            pharmacieSelect.removeAttribute('required');
            pharmacieSelect.value = '';
            dateInput.removeAttribute('required');
            nbInput.removeAttribute('required');
        } else {
            document.getElementById('field_praticien').style.display = 'none';
            document.getElementById('field_pharmacie').style.display = 'none';
            pharmacieSelect.removeAttribute('required');
            praticienSelect.removeAttribute('required');
        }
    }

    let modalSearchDebounce = null;
    let currentModalParticipant = null;

    function handleModalMatriculeSearch(query) {
        const dropdown = document.getElementById('modal_autocomplete_dropdown');
        const term = query.trim();

        if (term.length < 1) {
            dropdown.style.display = 'none';
            return;
        }

        clearTimeout(modalSearchDebounce);
        modalSearchDebounce = setTimeout(() => {
            dropdown.innerHTML = '<div class="p-2 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2 text-primary"></span>Recherche...</div>';
            dropdown.style.display = 'block';

            fetch(`{{ route('salaries.search-matricule') }}?q=${encodeURIComponent(term)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        dropdown.innerHTML = `<div class="p-2 text-center text-muted small">Aucun participant trouvé</div>`;
                        return;
                    }

                    let html = '';
                    data.forEach(item => {
                        const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                        html += `
                            <a href="javascript:void(0)" class="dropdown-item p-2 rounded-2 mb-1 text-wrap d-flex justify-content-between align-items-center" onclick='selectModalParticipantItem(${itemJson})'>
                                <div>
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace me-1">${item.matricule}</span>
                                        <strong class="text-dark">${item.nom} ${item.prenom}</strong>
                                    </div>
                                    <small class="text-muted d-block mt-1"><i class="bi bi-building me-1"></i>${item.entreprise}</small>
                                </div>
                                <span class="badge ${item.statut === 'actif' ? 'bg-success' : 'bg-secondary'} bg-opacity-10 ${item.statut === 'actif' ? 'text-success' : 'text-secondary'} rounded-pill small">${item.statut}</span>
                            </a>
                        `;
                    });
                    dropdown.innerHTML = html;
                })
                .catch(err => {
                    dropdown.innerHTML = '<div class="p-2 text-center text-danger small">Erreur lors de la recherche</div>';
                });
        }, 200);
    }

    function selectModalParticipantItem(item) {
        currentModalParticipant = item;
        document.getElementById('modal_selected_id_salarie').value = item.id;
        document.getElementById('modal_matricule_input').value = item.matricule;
        document.getElementById('modal_autocomplete_dropdown').style.display = 'none';

        document.getElementById('modal_badge_mat').textContent = item.matricule;
        document.getElementById('modal_badge_nom').textContent = `${item.nom} ${item.prenom}`;
        document.getElementById('modal_participant_badge').style.display = 'flex';
        document.getElementById('modal_btn_clear_matricule').style.display = 'block';

        const ayantDroitSelect = document.getElementById('ayant_droit_select');
        ayantDroitSelect.innerHTML = '<option value="">Pour le participant lui-même</option>';

        if (Array.isArray(item.ayants_droit) && item.ayants_droit.length > 0) {
            item.ayants_droit.forEach(ad => {
                const ageStr = ad.age ? ` (${ad.age} ans)` : '';
                const opt = document.createElement('option');
                opt.value = ad.id_ayant_droit;
                opt.textContent = `${ad.prenom} ${ad.nom} — ${ad.lien_parente || 'Ayant droit'}${ageStr}`;
                ayantDroitSelect.appendChild(opt);
            });
        }
    }

    function clearModalParticipant() {
        currentModalParticipant = null;
        document.getElementById('modal_selected_id_salarie').value = '';
        document.getElementById('modal_matricule_input').value = '';
        document.getElementById('modal_participant_badge').style.display = 'none';
        document.getElementById('modal_btn_clear_matricule').style.display = 'none';
        document.getElementById('modal_autocomplete_dropdown').style.display = 'none';

        const ayantDroitSelect = document.getElementById('ayant_droit_select');
        ayantDroitSelect.innerHTML = '<option value="">Pour le participant lui-même</option>';
        document.getElementById('modal_matricule_input').focus();
    }

    function focusModalMatricule() {
        const inp = document.getElementById('modal_matricule_input');
        inp.focus();
        inp.select();
        if (inp.value.trim().length >= 1) {
            handleModalMatriculeSearch(inp.value);
        }
    }

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('modal_autocomplete_dropdown');
        const input = document.getElementById('modal_matricule_input');
        if (dropdown && !dropdown.contains(e.target) && e.target !== input) {
            dropdown.style.display = 'none';
        }
    });
</script>
@endsection
