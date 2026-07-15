@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Demandes de prise en charge</h2>
    <a href="{{ route('demandes.create') }}" class="btn btn-primary shadow-sm">
        + Nouvelle demande
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Date</th>
                        <th>Salarié (ID)</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr>
                        <td class="ps-4 text-muted">#{{ $demande->id_demande }}</td>
                        <td>{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</td>
                        <td>{{ $demande->id_salarie }}</td>
                        <td>{{ Str::limit($demande->motif, 40) }}</td>
                        <td>
                            @if($demande->statut == 'Approuvée')
                                <span class="badge bg-success bg-opacity-10 text-success">Approuvée</span>
                            @elseif($demande->statut == 'En cours')
                                <span class="badge bg-warning bg-opacity-10 text-warning">En cours</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $demande->statut }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="#" class="btn btn-sm btn-light text-primary">Voir</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Aucune demande pour le moment.
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
