@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 login-wrapper">
    <div class="row g-0 vh-100">
        <!-- Section Visuel (Gauche) - Caché sur mobile -->
        <div class="col-lg-7 d-none d-lg-block position-relative bg-light">
            <div class="visuel-overlay"></div>
            <!-- L'image de fond est chargée via CSS en utilisant le helper asset() -->
            <div class="h-100 w-100 visuel-bg" style="background-image: url('{{ asset('visuel.png') }}'); background-size: cover; background-position: center;"></div>
        </div>

        <!-- Section Formulaire (Droite) -->
        <div class="col-lg-5 d-flex align-items-center justify-content-center bg-white shadow-lg z-index-1">
            <div class="w-100 px-4 px-md-5" style="max-width: 450px;">
                <div class="text-center mb-5">
                    <!-- Logo Mbaarum Koolute -->
                    <img src="{{ asset('logo.png') }}" alt="Mbaarum Koolute" class="mb-4 logo-animate img-fluid" style="max-height: 90px; width: auto; object-fit: contain;" onerror="this.onerror=null; this.src='https://via.placeholder.com/150x60/0056b3/ffffff?text=Mbaarum+Koolute';">
                    
                    <h3 class="fw-bold text-dark">Bienvenue</h3>
                    <p class="text-muted">Veuillez vous connecter à votre espace</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    <div class="mb-4 input-group-custom">
                        <label for="login" class="form-label fw-semibold text-secondary">Identifiant</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input id="login" type="text" class="form-control bg-light border-start-0 ps-0 @error('login') is-invalid @enderror" name="login" value="{{ old('login') }}" required autocomplete="login" autofocus placeholder="Entrez votre identifiant">
                        </div>
                    </div>

                    <div class="mb-5 input-group-custom">
                        <label for="mot_de_passe" class="form-label fw-semibold text-secondary">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input id="mot_de_passe" type="password" class="form-control bg-light border-start-0 ps-0 @error('mot_de_passe') is-invalid @enderror" name="mot_de_passe" required autocomplete="current-password" placeholder="Entrez votre mot de passe">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold shadow-sm login-btn">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-5">
                    <p class="text-muted small">&copy; {{ date('Y') }} GEST-IPM - Mbaarum Koolute</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Surpasse le style du layout par défaut pour avoir une page pleine (sans marges/padding) */
    body {
        margin: 0;
        padding: 0;
        background-color: #fff;
    }
    
    #wrapper, .container-fluid.mt-4 {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    /* Optionnel: Un léger overlay sombre ou coloré sur l'image si on veut ajouter du texte par dessus plus tard */
    .visuel-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 86, 179, 0.1) 0%, rgba(11, 142, 54, 0.1) 100%);
        pointer-events: none;
    }

    /* Animation du logo */
    .logo-animate {
        animation: popIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        transform: scale(0);
    }

    @keyframes popIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    /* Animation du formulaire */
    .login-form {
        animation: slideUp 0.8s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    @keyframes slideUp {
        to { opacity: 1; transform: translateY(0); }
    }

    /* Styles des inputs */
    .input-group-custom .input-group-text,
    .input-group-custom .form-control {
        border-color: #eef0f3;
        transition: all 0.3s ease;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    .input-group-custom .input-group:focus-within .input-group-text {
        border-color: var(--primary-blue, #0056b3);
        color: var(--primary-blue, #0056b3) !important;
        background-color: #fff !important;
    }
    
    .input-group-custom .input-group:focus-within .form-control {
        border-color: var(--primary-blue, #0056b3);
        background-color: #fff !important;
        box-shadow: none;
    }

    /* Style du bouton */
    .login-btn {
        background: linear-gradient(135deg, var(--primary-blue, #0056b3), #007bff);
        border: none;
        transition: all 0.3s ease;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 86, 179, 0.3) !important;
    }
</style>

<!-- Script pour masquer les éléments superflus du Layout principal si nécessaire -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sur la page de login, on masque potentiellement le top-navbar ou la sidebar si on est forcé d'utiliser app.blade.php
        const sidebar = document.getElementById('sidebar-wrapper');
        const pageContent = document.getElementById('page-content-wrapper');
        const navbar = document.querySelector('.navbar');
        
        if (sidebar) sidebar.style.display = 'none';
        if (navbar) navbar.style.display = 'none';
        if (pageContent) {
            pageContent.style.marginLeft = '0';
            pageContent.style.width = '100%';
            pageContent.style.padding = '0';
        }
    });
</script>
@endsection
