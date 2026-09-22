@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Frais Médicaux (Adhérents)
        </h1>
        <div>
            <a href="{{ route('frais-medicaux.export-excel', ['type' => 'global']) }}" class="btn btn-success shadow-sm rounded-pill px-3 me-2">
                <i class="fas fa-file-excel me-2"></i>Exporter Excel
            </a>
            <a href="{{ route('frais-medicaux.export-pdf', ['type' => 'global']) }}" target="_blank" class="btn btn-danger shadow-sm rounded-pill px-3">
                <i class="fas fa-file-pdf me-2"></i>Exporter PDF
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Formulaire de filtre -->
            <form action="{{ route('frais-medicaux.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Rechercher une entreprise..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Rechercher</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Adhérent (Entreprise)</th>
                            <th>Code</th>
                            <th class="text-end">Montant Total Facturé</th>
                            <th class="text-end">Prise en charge (IPM)</th>
                            <th class="text-end">Reste à charge (Adhérent)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entreprises as $entreprise)
                            <tr>
                                <td>
                                    <strong>{{ $entreprise->raison_sociale }}</strong>
                                </td>
                                <td><span class="badge bg-secondary">{{ $entreprise->code_adherent }}</span></td>
                                <td class="text-end fw-bold text-primary">{{ number_format($entreprise->total_frais ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end text-success">{{ number_format($entreprise->total_prise_charge ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end text-danger">{{ number_format($entreprise->total_reste_charge ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="text-center">
                                    <a href="{{ route('frais-medicaux.entreprise', $entreprise->IDADHERANT) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-eye me-1"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <p>Aucune entreprise trouvée.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Affichage de {{ $entreprises->firstItem() ?? 0 }} à {{ $entreprises->lastItem() ?? 0 }} sur {{ $entreprises->total() }} entreprises
                </div>
                <div>
                    {{ $entreprises->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
