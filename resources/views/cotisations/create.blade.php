@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('cotisations.index') }}" class="btn btn-link text-decoration-none text-secondary me-3">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h2 class="fw-bold mb-0 text-dark">Nouvelle Cotisation</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('cotisations.store') }}" method="POST">
                @csrf
                
                <h5 class="mb-4 text-primary">Informations de la cotisation</h5>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Type de cotisation *</label>
                        <div class="d-flex gap-4">
                            <div class="form-check custom-radio">
                                <input class="form-check-input" type="radio" name="type_cotisation" id="type_entreprise" value="entreprise" {{ old('type_cotisation') == 'entreprise' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-semibold" for="type_entreprise">
                                    Part Entreprise (Globale)
                                </label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input" type="radio" name="type_cotisation" id="type_salarie" value="salarie" {{ old('type_cotisation') == 'salarie' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="type_salarie">
                                    Part Participant (Individuelle)
                                </label>
                            </div>
                        </div>
                        @error('type_cotisation')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Section Entreprise -->
                    <div class="col-md-6 section-entreprise" style="display: none;">
                        <label class="form-label fw-medium">Entreprise *</label>
                        <select name="id_entreprise" id="id_entreprise" class="form-select bg-light border-0">
                            <option value="">Sélectionnez une entreprise</option>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" {{ old('id_entreprise') == $entreprise->id ? 'selected' : '' }}>
                                    {{ $entreprise->raison_sociale }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_entreprise')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 section-entreprise" style="display: none;">
                        <label class="form-label fw-medium">Masse Salariale *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="masse_salariale" id="masse_salariale" class="form-control bg-light border-0" value="{{ old('masse_salariale') }}">
                            <span class="input-group-text bg-light border-0">FCFA</span>
                        </div>
                        @error('masse_salariale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Section Participant -->
                    <div class="col-md-6 section-salarie" style="display: none;">
                        <label class="form-label fw-medium">
                            <i class="bi bi-upc-scan text-primary me-1"></i>Matricule du Participant *
                        </label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0 text-muted">
                                    <i class="bi bi-search text-primary"></i>
                                </span>
                                <input type="text" 
                                       id="cotisation_matricule_input" 
                                       class="form-control bg-white border-0" 
                                       placeholder="Saisir matricule, nom ou prénom..." 
                                       autocomplete="off"
                                       oninput="handleCotisationMatriculeSearch(this.value)"
                                       onfocus="if(this.value.trim().length >= 1) handleCotisationMatriculeSearch(this.value)">
                                <button class="btn btn-white border-0" type="button" id="cotisation_btn_clear" onclick="clearCotisationParticipant()" style="display: none;" title="Effacer">
                                    <i class="bi bi-x-circle text-muted"></i>
                                </button>
                            </div>
                            <input type="hidden" name="id_salarie" id="id_salarie" value="{{ old('id_salarie') }}">

                            <div id="cotisation_autocomplete_dropdown" class="dropdown-menu shadow-lg border-0 rounded-3 w-100 p-2 mt-1 position-absolute" style="max-height: 250px; overflow-y: auto; z-index: 1060; display: none;">
                            </div>
                        </div>

                        <!-- Badge récapitulatif -->
                        <div id="cotisation_participant_badge" class="p-2 px-3 bg-white rounded-3 d-flex align-items-center justify-content-between mt-2 shadow-xs" style="display: none;">
                            <div>
                                <span class="badge bg-primary font-monospace me-1" id="cotisation_badge_mat">MAT</span>
                                <strong class="text-dark small" id="cotisation_badge_nom">Nom Prénom</strong>
                                <small class="text-muted ms-1" id="cotisation_badge_ent">(Entreprise)</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none" onclick="document.getElementById('cotisation_matricule_input').focus()">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>

                        @error('id_salarie')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 section-salarie" style="display: none;">
                        <label class="form-label fw-medium">Salaire de Base *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="salaire_base" id="salaire_base" class="form-control bg-light border-0" value="{{ old('salaire_base') }}">
                            <span class="input-group-text bg-light border-0">FCFA</span>
                        </div>
                        @error('salaire_base')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Champs Communs -->
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Période *</label>
                        <input type="text" name="periode" class="form-control bg-light border-0" value="{{ old('periode', date('Y-m')) }}" placeholder="Ex: 2026-07" required>
                        @error('periode')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Taux de cotisation (%) *</label>
                        <input type="number" step="0.01" name="taux" class="form-control bg-light border-0" value="{{ old('taux', 10) }}" required>
                        @error('taux')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Statut du paiement *</label>
                        <select name="statut" id="statut" class="form-select bg-light border-0" required>
                            <option value="impayee" {{ old('statut') == 'impayee' ? 'selected' : '' }}>Impayée</option>
                            <option value="payee" {{ old('statut') == 'payee' ? 'selected' : '' }}>Payée</option>
                        </select>
                        @error('statut')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 section-date" style="display: none;">
                        <label class="form-label fw-medium">Date de paiement</label>
                        <input type="date" name="date_paiement" class="form-control bg-light border-0" value="{{ old('date_paiement', date('Y-m-d')) }}">
                        @error('date_paiement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-light rounded-pill px-4">Réinitialiser</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="type_cotisation"]');
        const sectionEntreprise = document.querySelectorAll('.section-entreprise');
        const sectionSalarie = document.querySelectorAll('.section-salarie');
        const statutSelect = document.getElementById('statut');
        const sectionDate = document.querySelector('.section-date');

        function updateForm() {
            let type = document.querySelector('input[name="type_cotisation"]:checked')?.value;
            
            if (type === 'entreprise') {
                sectionEntreprise.forEach(el => el.style.display = 'block');
                sectionSalarie.forEach(el => el.style.display = 'none');
                document.getElementById('id_entreprise').setAttribute('required', 'required');
                document.getElementById('masse_salariale').setAttribute('required', 'required');
                document.getElementById('id_salarie').removeAttribute('required');
                document.getElementById('salaire_base').removeAttribute('required');
            } else if (type === 'salarie') {
                sectionEntreprise.forEach(el => el.style.display = 'none');
                sectionSalarie.forEach(el => el.style.display = 'block');
                document.getElementById('id_salarie').setAttribute('required', 'required');
                document.getElementById('salaire_base').setAttribute('required', 'required');
                document.getElementById('id_entreprise').removeAttribute('required');
                document.getElementById('masse_salariale').removeAttribute('required');
            }

            if (statutSelect.value === 'payee') {
                sectionDate.style.display = 'block';
            } else {
                sectionDate.style.display = 'none';
            }
        }

        radios.forEach(radio => radio.addEventListener('change', updateForm));
        statutSelect.addEventListener('change', updateForm);

        // Run once on load to set initial state
        updateForm();

        // Initialisation si un participant était déjà sélectionné
        const initialSalarieId = document.getElementById('id_salarie').value;
        if (initialSalarieId) {
            fetch(`{{ route('salaries.search-matricule') }}?id=${encodeURIComponent(initialSalarieId)}`)
                .then(res => res.json())
                .then(item => {
                    if (item && item.id) {
                        selectCotisationParticipant(item);
                    }
                })
                .catch(console.error);
        }
    });

    let cotisDebounce = null;

    function handleCotisationMatriculeSearch(query) {
        const dropdown = document.getElementById('cotisation_autocomplete_dropdown');
        const term = query.trim();

        if (term.length < 1) {
            dropdown.style.display = 'none';
            return;
        }

        clearTimeout(cotisDebounce);
        cotisDebounce = setTimeout(() => {
            dropdown.innerHTML = '<div class="p-2 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2 text-primary"></span>Recherche...</div>';
            dropdown.style.display = 'block';

            fetch(`{{ route('salaries.search-matricule') }}?q=${encodeURIComponent(term)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        dropdown.innerHTML = '<div class="p-2 text-center text-muted small">Aucun participant trouvé</div>';
                        return;
                    }

                    let html = '';
                    data.forEach(item => {
                        const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                        html += `
                            <a href="javascript:void(0)" class="dropdown-item p-2 rounded-2 mb-1 text-wrap d-flex justify-content-between align-items-center" onclick='selectCotisationParticipant(${itemJson})'>
                                <div>
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace me-1">${item.matricule}</span>
                                        <strong class="text-dark">${item.nom} ${item.prenom}</strong>
                                    </div>
                                    <small class="text-muted d-block mt-1">${item.entreprise}</small>
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

    function selectCotisationParticipant(item) {
        document.getElementById('id_salarie').value = item.id;
        document.getElementById('cotisation_matricule_input').value = item.matricule;
        document.getElementById('cotisation_autocomplete_dropdown').style.display = 'none';

        document.getElementById('cotisation_badge_mat').textContent = item.matricule;
        document.getElementById('cotisation_badge_nom').textContent = `${item.nom} ${item.prenom}`;
        document.getElementById('cotisation_badge_ent').textContent = `(${item.entreprise})`;
        document.getElementById('cotisation_participant_badge').style.display = 'flex';
        document.getElementById('cotisation_btn_clear').style.display = 'block';
    }

    function clearCotisationParticipant() {
        document.getElementById('id_salarie').value = '';
        document.getElementById('cotisation_matricule_input').value = '';
        document.getElementById('cotisation_participant_badge').style.display = 'none';
        document.getElementById('cotisation_btn_clear').style.display = 'none';
        document.getElementById('cotisation_autocomplete_dropdown').style.display = 'none';
        document.getElementById('cotisation_matricule_input').focus();
    }

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('cotisation_autocomplete_dropdown');
        const input = document.getElementById('cotisation_matricule_input');
        if (dropdown && !dropdown.contains(e.target) && e.target !== input) {
            dropdown.style.display = 'none';
        }
    });
</script>
@endsection
