@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Statuts des Cotisations</h2>
        <p class="text-muted mb-0">Suivi individuel par salarié et par période</p>
    </div>
    <a href="{{ route('cotisations.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm border">
        <i class="bi bi-pie-chart-fill text-primary me-2"></i> Rapport global
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filtres Modernes -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-body p-4">
        <form action="{{ route('cotisations.statut') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold text-uppercase tracking-wide">Salarié</label>
                <div class="input-group input-group-lg bg-light rounded-3">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-person text-muted"></i></span>
                    <select name="id_salarie" class="form-select bg-transparent border-0 shadow-none">
                        <option value="">Tous les salariés</option>
                        @foreach($salaries as $salarie)
                            <option value="{{ $salarie->id_salarie }}" {{ request('id_salarie') == $salarie->id_salarie ? 'selected' : '' }}>
                                {{ $salarie->nom }} {{ $salarie->prenom }} ({{ $salarie->matricule }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase tracking-wide">Période</label>
                <div class="input-group input-group-lg bg-light rounded-3">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-calendar3 text-muted"></i></span>
                    <input type="month" name="periode" class="form-control bg-transparent border-0 shadow-none" value="{{ request('periode') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg rounded-3 flex-grow-1 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Filtrer
                </button>
                <a href="{{ route('cotisations.statut') }}" class="btn btn-light btn-lg rounded-3 border">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Liste des cotisations -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Période</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Salarié</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-center">Salaire & Taux</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Montant</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Statut</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($cotisations as $cotisation)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="badge bg-light text-dark border"><i class="bi bi-calendar2-minus me-1"></i> {{ $cotisation->periode }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 40px; height: 40px;">
                                    {{ substr($cotisation->salarie->nom ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $cotisation->salarie->nom ?? 'N/A' }} {{ $cotisation->salarie->prenom ?? '' }}</h6>
                                    <small class="text-muted">{{ $cotisation->salarie->matricule ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="text-dark fw-medium">{{ number_format($cotisation->salaire_base, 0, ',', ' ') }} FCFA</div>
                            <small class="text-muted"><i class="bi bi-percent"></i> {{ $cotisation->taux }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <h6 class="mb-0 fw-bold text-primary">{{ number_format($cotisation->montant, 0, ',', ' ') }} FCFA</h6>
                        </td>
                        <td class="px-4 py-3">
                            @if($cotisation->statut == 'payee')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> Payée
                                </span>
                                <div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i> {{ $cotisation->date_paiement ? \Carbon\Carbon::parse($cotisation->date_paiement)->format('d/m/Y') : '-' }}</div>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Impayée
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            @if($cotisation->statut != 'payee')
                                <form action="{{ route('cotisations.payer', $cotisation->id_cotisation) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" onclick="return confirm('Confirmer le paiement de cette cotisation ?')">
                                        <i class="bi bi-check2-all me-1"></i> Payer
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-light rounded-pill px-3 border disabled">
                                    <i class="bi bi-check2 text-success"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                                <h5>Aucune cotisation trouvée</h5>
                                <p class="mb-0">Modifiez vos filtres ou lancez la génération mensuelle.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $cotisations->links() }}
</div>

@endsection
