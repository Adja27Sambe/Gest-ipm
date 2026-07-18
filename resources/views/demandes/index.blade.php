@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Tableau de bord - Demandes</h2>
    <button class="btn btn-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#createDemandeModal">
        <i class="bi bi-plus-lg me-2"></i> Générer un Document
    </button>
</div>

<!-- KPIs Dashboard -->
@if(isset($stats))
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-files text-primary fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Demandes</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
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
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-hourglass-split text-warning fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">En Cours / En Attente</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['en_cours'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<h5 class="fw-bold mb-3 text-dark">Dernières Demandes</h5>


<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Type de Document</th>
                        <th>N° Document</th>
                        <th>Bénéficiaire</th>
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
                        <td class="ps-4 fw-medium">{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
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
                            <!-- Download PDF (Utilise la route API pour la démo, mais devrait idéalement avoir une route web ou utiliser l'API) -->
                            <a href="/api/demandes/{{ $demande->id_demande }}/pdf" target="_blank" class="btn btn-sm btn-light text-danger me-1" title="Télécharger PDF">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            <!-- Delete -->
                            <form action="{{ route('demandes.destroy', $demande->id_demande) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette demande ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-secondary" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-folder2-open fs-1 d-block mb-3 opacity-50"></i>
                            Aucune demande générée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $demandes->links('pagination::bootstrap-5') }}
</div>
@endsection

@section('modals')
<!-- Modal Création Demande -->
<div class="modal fade" id="createDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Générer un Document (Prise en charge)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('demandes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Type de Document *</label>
                            <select name="id_type_demande" id="type_demande_select" class="form-select" required onchange="toggleFields()">
                                <option value="">Sélectionner</option>
                                @foreach($typesDemande as $td)
                                    <option value="{{ $td->id_type_demande }}" data-name="{{ strtolower($td->libelle) }}">{{ $td->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Type de Prestation</label>
                            <select name="id_type_prestation" class="form-select">
                                <option value="">Automatique par défaut</option>
                                @foreach($typesPrestation as $tp)
                                    <option value="{{ $tp->id_type_prestation }}">{{ $tp->libelle }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Utile pour calculer le taux de couverture</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Salarié *</label>
                            <select name="id_salarie" id="salarie_select" class="form-select" required onchange="updateAyantsDroit()">
                                <option value="">Sélectionner un salarié</option>
                                @foreach($salaries as $salarie)
                                    <option value="{{ $salarie->id_salarie }}" data-ayants-droit="{{ json_encode($salarie->ayantsDroit) }}">
                                        {{ $salarie->prenom }} {{ $salarie->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Ayant-droit (Optionnel)</label>
                            <select name="id_ayant_droit" id="ayant_droit_select" class="form-select">
                                <option value="">Pour le salarié lui-même</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Motif de la demande</label>
                            <textarea name="motif" class="form-control" rows="2" placeholder="Ex: Consultation dentaire..."></textarea>
                        </div>

                        <!-- Prestataire -->
                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Praticien / Prestataire *</label>
                            <select name="id_prestataire" id="prestataire_select" class="form-select" required>
                                <option value="">Sélectionner un praticien</option>
                                @foreach($prestataires as $prestataire)
                                    <option value="{{ $prestataire->id_prestataire }}" data-type="{{ strtolower($prestataire->type->libelle ?? '') }}">
                                        {{ $prestataire->nom_raison_sociale }} - {{ $prestataire->type->libelle ?? 'Non défini' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Champs spécifiques Bon de Commande -->
                        <div class="col-md-6 dynamic-field" id="field_date_ordonnance" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Date de l'ordonnance *</label>
                            <input type="date" name="date_ordonnance" class="form-control">
                        </div>
                        <div class="col-md-6 dynamic-field" id="field_nombre_articles" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Nombre d'articles *</label>
                            <input type="number" name="nombre_articles" class="form-control" value="1" min="1">
                        </div>

                        <!-- Champ spécifique Lettre de Garantie -->
                        <div class="col-12 dynamic-field" id="field_choix_acte" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Choix de l'acte *</label>
                            <select name="choix_acte" class="form-select">
                                <option value="">Sélectionner</option>
                                <option value="Hospitalisation">Hospitalisation</option>
                                <option value="Consultation">Consultation</option>
                                <option value="Radiologie">Radiologie</option>
                                <option value="Analyse">Analyse</option>
                            </select>
                        </div>

                        <!-- Champs spécifiques Feuille / Lettre -->
                        <div class="col-12 dynamic-field" id="field_observations" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Observations / Remarques</label>
                            <textarea name="observations" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-12 dynamic-field" id="field_diagnostic" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Diagnostic (Feuille de Maladie)</label>
                            <textarea name="diagnostic" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Générer le document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateAyantsDroit() {
        const salarieSelect = document.getElementById('salarie_select');
        const ayantDroitSelect = document.getElementById('ayant_droit_select');
        
        ayantDroitSelect.innerHTML = '<option value="">Pour le salarié lui-même</option>';
        
        const selectedOption = salarieSelect.options[salarieSelect.selectedIndex];
        if(!selectedOption.value) return;
        
        const ayantsDroit = JSON.parse(selectedOption.getAttribute('data-ayants-droit') || '[]');
        
        ayantsDroit.forEach(ad => {
            ayantDroitSelect.innerHTML += `<option value="${ad.id_ayant_droit}">${ad.prenom} ${ad.nom}</option>`;
        });
    }

    function toggleFields() {
        const typeSelect = document.getElementById('type_demande_select');
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const docName = selectedOption.getAttribute('data-name') || '';

        // Affichage des champs spécifiques
        document.getElementById('field_observations').style.display = (docName.includes('feuille') || docName.includes('lettre')) ? 'block' : 'none';
        document.getElementById('field_diagnostic').style.display = (docName.includes('feuille')) ? 'block' : 'none';
        
        document.getElementById('field_date_ordonnance').style.display = docName.includes('bon') ? 'block' : 'none';
        document.getElementById('field_nombre_articles').style.display = docName.includes('bon') ? 'block' : 'none';
        
        document.getElementById('field_choix_acte').style.display = docName.includes('lettre') ? 'block' : 'none';

        // Filtrage des praticiens
        const prestataireSelect = document.getElementById('prestataire_select');
        const options = prestataireSelect.options;
        let firstValid = null;

        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const typeLibelle = option.getAttribute('data-type');
            const isPharmacieOuOpticien = typeLibelle.includes('pharmacie') || typeLibelle.includes('opticien');

            if (docName.includes('bon')) {
                // Seulement Pharmacies et Opticiens
                option.style.display = isPharmacieOuOpticien ? 'block' : 'none';
                if (!firstValid && isPharmacieOuOpticien) firstValid = option.value;
            } else {
                // Tout sauf Pharmacies et Opticiens
                option.style.display = !isPharmacieOuOpticien ? 'block' : 'none';
                if (!firstValid && !isPharmacieOuOpticien) firstValid = option.value;
            }
        }
        
        // Reset selection if currently selected option is hidden
        const currentSelected = prestataireSelect.options[prestataireSelect.selectedIndex];
        if (currentSelected && currentSelected.style.display === 'none') {
            prestataireSelect.value = '';
        }
    }
</script>
@endsection
