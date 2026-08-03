@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Tableau de bord - Demandes</h2>
    <button class="btn btn-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#createDemandeModal">
        <i class="bi bi-plus-lg me-2"></i> Nouvelle Demande
    </button>
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
                    <h6 class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Demandes</h6>
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
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique (n° demande, bénéficiaire, type)..." value="{{ request('search') }}" autocomplete="off">
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
                        <th>Type de Demande</th>
                        <th>N° Demande</th>
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
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions sur la demande">
                                <!-- Imprimer PDF -->
                                <a href="{{ route('demandes.pdf', $demande->id_demande) }}" target="_blank" class="btn btn-outline-danger d-inline-flex align-items-center" title="Imprimer le PDF">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>
                                </a>
                                <!-- Supprimer -->
                                <form action="{{ route('demandes.destroy', $demande->id_demande) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary border-start-0" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
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

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage de {{ $demandes->firstItem() ?? 0 }} à {{ $demandes->lastItem() ?? 0 }} sur {{ $demandes->total() }} demandes
    </div>
    <div>
        {{ $demandes->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Création Demande -->
<div class="modal fade" id="createDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouvelle Demande (Prise en charge)</h5>
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

                        <!-- Praticien -->
                        <div class="col-md-12 dynamic-field" id="field_praticien" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Praticien *</label>
                            <select name="id_praticien" id="praticien_select" class="form-select">
                                <option value="">Sélectionner un praticien</option>
                                @foreach($praticiens as $praticien)
                                    <option value="{{ $praticien->id_praticien }}">
                                        {{ $praticien->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pharmacie -->
                        <div class="col-md-12 dynamic-field" id="field_pharmacie" style="display: none;">
                            <label class="form-label fw-medium text-muted small mb-1">Pharmacie *</label>
                            <select name="id_pharmacie" id="pharmacie_select" class="form-select">
                                <option value="">Sélectionner une pharmacie</option>
                                @foreach($pharmacies as $pharmacie)
                                    <option value="{{ $pharmacie->id_pharmacie }}">
                                        {{ $pharmacie->nom }}
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
                            <label class="form-label fw-medium text-muted small mb-1">Choix de l'acte(s) *</label>
                            <div class="d-flex flex-wrap gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Hospitalisation" id="acte_hospitalisation">
                                    <label class="form-check-label" for="acte_hospitalisation">Hospitalisation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Consultation" id="acte_consultation">
                                    <label class="form-check-label" for="acte_consultation">Consultation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Radiologie" id="acte_radiologie">
                                    <label class="form-check-label" for="acte_radiologie">Radiologie</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="choix_acte[]" value="Analyse" id="acte_analyse">
                                    <label class="form-check-label" for="acte_analyse">Analyse</label>
                                </div>
                            </div>
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
                    <button type="submit" class="btn btn-primary">Générer la demande</button>
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


        // Logique de validation stricte : Pharmacie pour BC, Praticien pour FM/LG
        if (docName.includes('bon')) {
            document.getElementById('field_pharmacie').style.display = 'block';
            document.getElementById('pharmacie_select').setAttribute('required', 'required');
            
            document.getElementById('field_praticien').style.display = 'none';
            document.getElementById('praticien_select').removeAttribute('required');
            document.getElementById('praticien_select').value = '';
        } else if (docName.includes('feuille') || docName.includes('lettre')) {
            document.getElementById('field_praticien').style.display = 'block';
            document.getElementById('praticien_select').setAttribute('required', 'required');
            
            document.getElementById('field_pharmacie').style.display = 'none';
            document.getElementById('pharmacie_select').removeAttribute('required');
            document.getElementById('pharmacie_select').value = '';
        } else {
            document.getElementById('field_praticien').style.display = 'none';
            document.getElementById('field_pharmacie').style.display = 'none';
        }
    }
</script>
@endsection
