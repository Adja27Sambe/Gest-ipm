@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('salaries.show', $carte->salarie) }}" class="btn btn-link text-decoration-none text-secondary p-0 mb-2">
                <i class="bi bi-arrow-left"></i> Retour au salarié : {{ $carte->salarie->prenom }} {{ $carte->salarie->nom }}
            </a>
            <h1 class="h3 mb-0 text-dark font-weight-bold">Carte Participant Assuré (Recto - Verso)</h1>
            <p class="text-muted mb-0">Téléchargement des visuels au format d'image PNG et PDF</p>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <x-carte-assure-template :carte="$carte" :onlyRecto="false" />
    </div>
</div>
@endsection
