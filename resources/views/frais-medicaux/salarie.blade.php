@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('frais-medicaux.entreprise', $salarie->IDADHERANT) }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Retour à l'entreprise
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-injured text-primary me-2"></i>Relevé des Frais: {{ $salarie->nom_complet }}
            </h1>
            <p class="text-muted mt-1 mb-0">Entreprise : {{ $salarie->entreprise->raison_sociale ?? 'Non spécifiée' }}</p>
        </div>
        <div>
            <a href="{{ route('frais-medicaux.export-excel', ['type' => 'salarie', 'id' => $salarie->IDPARTICIPANT]) }}" class="btn btn-success shadow-sm rounded-pill px-3 me-2">
                <i class="fas fa-file-excel me-2"></i>Exporter Excel
            </a>
            <a href="{{ route('frais-medicaux.export-pdf', ['type' => 'salarie', 'id' => $salarie->IDPARTICIPANT]) }}" target="_blank" class="btn btn-danger shadow-sm rounded-pill px-3">
                <i class="fas fa-file-pdf me-2"></i>Exporter PDF
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Facturé (Salarié)</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($prestations->sum('montant'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Prise en charge</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($prestations->sum('montant') - $prestations->sum('reste_a_charge'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase mb-2 text-white-50">Total Reste à charge</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($prestations->sum('reste_a_charge'), 0, ',', ' ') }} FCFA</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">Détail des Actes et Prestations</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>N° Demande</th>
                            <th>Type de Prestation</th>
                            <th>Prestataire</th>
                            <th class="text-end">Montant Total</th>
                            <th class="text-end">Prise en charge</th>
                            <th class="text-end">Ticket Modérateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestations as $prestation)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($prestation->date_prestation)->format('d/m/Y') }}</td>
                                <td>
                                    @if($prestation->demande)
                                        <a href="{{ route('demandes.show', $prestation->demande->id_demande) }}" class="text-decoration-none">
                                            {{ $prestation->demande->numero_demande }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $prestation->typePrestation->libelle ?? 'Autre' }}</td>
                                <td>
                                    @if($prestation->praticien)
                                        <i class="fas fa-user-md text-info me-1"></i> {{ $prestation->praticien->nom_complet }}
                                    @elseif($prestation->pharmacie)
                                        <i class="fas fa-clinic-medical text-success me-1"></i> {{ $prestation->pharmacie->nom_pharmacie }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end fw-bold">{{ number_format($prestation->montant, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end text-success">{{ number_format($prestation->montant - $prestation->reste_a_charge, 0, ',', ' ') }} FCFA ({{ number_format($prestation->taux_prise_charge, 0) }}%)</td>
                                <td class="text-end text-danger">{{ number_format($prestation->reste_a_charge, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <p>Aucune prestation enregistrée pour ce salarié.</p>
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
