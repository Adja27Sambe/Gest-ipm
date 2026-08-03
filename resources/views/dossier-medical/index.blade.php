@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dossiers Médicaux - Recherche de bénéficiaire</h2>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Nom & Prénom</th>
                        <th>Entreprise</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salaries as $salarie)
                        <!-- Salarié -->
                        <tr class="clickable-row" data-href="{{ route('dossier-medical.show', ['type' => 'salarie', 'id' => $salarie->id_salarie]) }}">
                            <td>{{ $salarie->id_salarie }}</td>
                            <td><span class="badge bg-primary">Salarié</span></td>
                            <td>{{ $salarie->prenom }} {{ $salarie->nom }}</td>
                            <td>{{ $salarie->entreprise->nom ?? 'N/A' }}</td>
                            <td class="text-end">
                                <a href="{{ route('dossier-medical.show', ['type' => 'salarie', 'id' => $salarie->id_salarie]) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-folder-open"></i> Voir le dossier
                                </a>
                            </td>
                        </tr>
                        <!-- Ayants Droit du Salarié -->
                        @foreach($salarie->ayantsDroit as $ad)
                            <tr class="clickable-row" data-href="{{ route('dossier-medical.show', ['type' => 'ayant_droit', 'id' => $ad->id_ayant_droit]) }}">
                                <td>{{ $ad->id_ayant_droit }}</td>
                                <td><span class="badge bg-secondary">Ayant Droit ({{ $ad->lien_parente }})</span></td>
                                <td>{{ $ad->prenom }} {{ $ad->nom }}</td>
                                <td>{{ $salarie->entreprise->nom ?? 'N/A' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('dossier-medical.show', ['type' => 'ayant_droit', 'id' => $ad->id_ayant_droit]) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-folder-open"></i> Voir le dossier
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
