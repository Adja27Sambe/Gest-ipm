@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('frais-medicaux.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building text-primary me-2"></i>Détail Frais: {{ $entreprise->raison_sociale }}
            </h1>
        </div>
        <a href="{{ route('frais-medicaux.export-pdf', ['type' => 'entreprise', 'id' => $entreprise->IDADHERANT]) }}" target="_blank" class="btn btn-danger shadow-sm rounded-pill px-3">
            <i class="fas fa-file-pdf me-2"></i>Exporter PDF Adhérent
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Facturé</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($salaries->sum('total_frais'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Prise en charge</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($salaries->sum('total_prise_charge'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Reste à charge</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($salaries->sum('total_reste_charge'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">Répartition par Salarié (Participant)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Participant (Salarié)</th>
                            <th class="text-end">Frais Total</th>
                            <th class="text-end">Prise en charge</th>
                            <th class="text-end">Reste à charge</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries->sortByDesc('total_frais') as $salarie)
                            @if($salarie->total_frais > 0)
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $salarie->matricule }}</span></td>
                                <td>
                                    <strong>{{ $salarie->nom_complet }}</strong>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($salarie->total_frais, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end text-success">{{ number_format($salarie->total_prise_charge, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end text-danger">{{ number_format($salarie->total_reste_charge, 0, ',', ' ') }} FCFA</td>
                                <td class="text-center">
                                    <a href="{{ route('frais-medicaux.salarie', $salarie->IDPARTICIPANT) }}" class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="fas fa-list me-1"></i> Relevé
                                    </a>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <p>Aucun participant trouvé ou aucun frais enregistré.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
