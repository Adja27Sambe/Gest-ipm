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
                                    Part Salarié (Individuelle)
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
                                <option value="{{ $entreprise->id_entreprise }}" {{ old('id_entreprise') == $entreprise->id_entreprise ? 'selected' : '' }}>
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

                    <!-- Section Salarié -->
                    <div class="col-md-6 section-salarie" style="display: none;">
                        <label class="form-label fw-medium">Salarié *</label>
                        <select name="id_salarie" id="id_salarie" class="form-select bg-light border-0">
                            <option value="">Sélectionnez un salarié</option>
                            @foreach($salaries as $salarie)
                                <option value="{{ $salarie->id_salarie }}" {{ old('id_salarie') == $salarie->id_salarie ? 'selected' : '' }}>
                                    {{ $salarie->prenom }} {{ $salarie->nom }} ({{ $salarie->entreprise->raison_sociale ?? 'Sans entreprise' }})
                                </option>
                            @endforeach
                        </select>
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
    });
</script>
@endsection
