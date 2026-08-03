@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Validation des Demandes</h2>
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('demandes.validation.index') }}" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique (n° demande, bénéficiaire)..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-end">
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
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">En attente</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions validation">
                                <!-- Aperçu PDF -->
                                <a href="{{ route('demandes.pdf', $demande->id_demande) }}" target="_blank" class="btn btn-outline-primary" title="Aperçu PDF">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- Approuver -->
                                <form action="{{ route('demandes.approuver', $demande->id_demande) }}" method="POST" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment approuver cette demande ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success border-start-0" title="Approuver">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <!-- Rejeter -->
                                <form action="{{ route('demandes.rejeter', $demande->id_demande) }}" method="POST" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment rejeter cette demande ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger border-start-0" title="Rejeter">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-circle fs-1 d-block mb-3 opacity-50 text-success"></i>
                            Aucune demande en attente de validation.
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
