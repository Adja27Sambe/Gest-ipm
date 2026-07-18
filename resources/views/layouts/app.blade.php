<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mbaarum Koolute - IPM Dashboard</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-blue: #0056b3;
            --primary-blue-hover: #004494;
            --secondary-green: #0B8E36;
            --secondary-green-hover: #086b28;
            --bg-light: #f4f7f6;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: #2c3e50;
            overflow-x: hidden;
        }

        /* --- Dashboard Layout --- */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar */
        #sidebar-wrapper {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: #ffffff;
            box-shadow: 2px 0 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-heading {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid #edf2f7;
            position: sticky;
            top: 0;
            background: #ffffff;
            z-index: 10;
        }

        .sidebar-nav {
            padding: 1.5rem 1rem;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #555;
            text-decoration: none;
            font-weight: 500;
            border-radius: 0 10px 10px 0; /* Arrondi seulement à droite pour laisser place à la bordure gauche */
            margin-bottom: 0.4rem;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .sidebar-link i {
            font-size: 1.1rem;
            margin-right: 0.8rem;
            color: #777;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background-color: #f0f4f8;
            color: var(--primary-blue);
            border-left-color: var(--secondary-green);
            transform: translateX(5px);
        }

        .sidebar-link:hover i {
            color: var(--secondary-green);
        }

        .sidebar-link.active {
            background-color: rgba(0, 86, 179, 0.06);
            color: var(--primary-blue);
            font-weight: 600;
            border-left-color: var(--primary-blue);
        }

        .sidebar-link.active i {
            color: var(--primary-blue);
        }

        /* Branding border on the top navbar */
        .brand-border {
            height: 4px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary-blue) 0%, var(--secondary-green) 100%);
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1050;
        }

        .sidebar-link.text-danger:hover, .sidebar-link.text-danger.active {
            background-color: #f8d7da;
            color: #dc3545;
            border-left-color: #dc3545;
        }
        .sidebar-link.text-danger:hover i, .sidebar-link.text-danger.active i { color: #dc3545; }

        .sidebar-link.text-success:hover, .sidebar-link.text-success.active {
            background-color: #d1e7dd;
            color: #198754;
            border-left-color: #198754;
        }
        .sidebar-link.text-success:hover i, .sidebar-link.text-success.active i { color: #198754; }

        /* Main Content Area */
        #page-content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-width: 0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-light);
        }

        /* Top Header */
        .top-navbar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        #menu-toggle {
            cursor: pointer;
            color: #333;
            transition: color 0.2s;
        }
        #menu-toggle:hover { color: var(--primary-blue); }

        .main-container {
            padding: 2rem;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toggled State for Desktop */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: calc(var(--sidebar-width) * -1);
        }
        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }

        /* Custom UI Elements (Cards, Buttons, Tables) */
        .card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #edf2f7;
            padding: 1.25rem 1.5rem;
            border-radius: 16px 16px 0 0 !important;
        }
        .card-body { padding: 1.5rem; }

        .btn {
            font-weight: 600;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
            border: none;
        }
        .btn:active { transform: scale(0.95); }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #007bff 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.2);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-blue-hover) 0%, var(--primary-blue) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 86, 179, 0.3);
            color: #fff;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary-green) 0%, #28a745 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(11, 142, 54, 0.2);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, var(--secondary-green-hover) 0%, var(--secondary-green) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11, 142, 54, 0.3);
            color: #fff;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #eef0f3;
            background-color: #fcfdfd;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1);
            border-color: var(--primary-blue);
        }

        .table th {
            font-weight: 600;
            color: #6c757d;
            border-bottom: 2px solid #edf2f7;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table td { vertical-align: middle; }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1); /* Hidden by default on mobile */
            }
            #page-content-wrapper {
                margin-left: 0;
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0; /* Shown when toggled */
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
            }
            
            /* Overlay on mobile */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.4);
                backdrop-filter: blur(2px);
                z-index: 1035;
            }
            #wrapper.toggled .sidebar-overlay {
                display: block;
            }
            
            .main-container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="brand-border"></div>
    <div id="wrapper">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        @if(!request()->is('login'))
        <aside id="sidebar-wrapper">
            <div class="sidebar-heading pb-3">
                <img src="{{ asset('logo.png') }}" alt="Mbaarum Koolute" class="img-fluid" style="max-height: 60px; width: auto; object-fit: contain;" onerror="this.onerror=null; this.src='https://via.placeholder.com/150x50/0056b3/ffffff?text=Mbaarum+Koolute';">
            </div>
            
            <div class="sidebar-nav">
                @auth
                    @can('gerer_demandes')
                    <a href="{{ route('demandes.index') }}" class="sidebar-link {{ request()->routeIs('demandes.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Demandes
                    </a>
                    @endcan
                    @can('gerer_entreprises')
                    <a href="{{ route('entreprises.index') }}" class="sidebar-link {{ request()->routeIs('entreprises.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Entreprises
                    </a>
                    @endcan
                    @can('gerer_salaries')
                    <a href="{{ route('salaries.index') }}" class="sidebar-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Salariés
                    </a>
                    @endcan
                    @can('gerer_prestataires')
                    <a href="{{ route('prestataires.index') }}" class="sidebar-link {{ request()->routeIs('prestataires.*') ? 'active' : '' }}">
                        <i class="bi bi-hospital"></i> Prestataires
                    </a>
                    @endcan
                    @can('gerer_prestations')
                    <a href="{{ route('devis.index') }}" class="sidebar-link {{ request()->routeIs('devis.*') ? 'active' : '' }}">
                        <i class="bi bi-calculator"></i> Devis
                    </a>
                    <a href="{{ route('prestations.index') }}" class="sidebar-link {{ request()->routeIs('prestations.*') ? 'active' : '' }}">
                        <i class="bi bi-activity"></i> Prestations
                    </a>
                    @endcan
                    @can('gerer_cotisations')
                    <a href="{{ route('cotisations.index') }}" class="sidebar-link {{ request()->routeIs('cotisations.*') ? 'active' : '' }}">
                        <i class="bi bi-piggy-bank"></i> Cotisations
                    </a>
                    @endcan
                    @can('Gérer la facturation')
                    <a href="{{ route('factures.index') }}" class="sidebar-link {{ request()->routeIs('factures.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Factures
                    </a>
                    @endcan
                    
                    <hr class="text-secondary opacity-25 my-3">
                    
                    @can('consulter_dossier_medical')
                    <a href="{{ route('dossier-medical.index') }}" class="sidebar-link text-danger {{ request()->routeIs('dossier-medical.*') ? 'active' : '' }}">
                        <i class="bi bi-heart-pulse"></i> Dossier Médical
                    </a>
                    @endcan
                    @can('gerer_pieces_jointes')
                    <a href="{{ route('pieces-jointes.index') }}" class="sidebar-link {{ request()->routeIs('pieces-jointes.*') ? 'active' : '' }}">
                        <i class="bi bi-folder2-open"></i> Documents
                    </a>
                    @endcan
                    @can('gerer_medias')
                    <a href="{{ route('medias.index') }}" class="sidebar-link {{ request()->routeIs('medias.*') ? 'active' : '' }}">
                        <i class="bi bi-images"></i> Médias
                    </a>
                    @endcan
                    
                    <hr class="text-secondary opacity-25 my-3">
                    
                    @can('voir_audit')
                    <a href="{{ route('audit.index') }}" class="sidebar-link text-success {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Audit
                    </a>
                    @endcan
                    @can('gerer_roles')
                    <a href="{{ route('roles.index') }}" class="sidebar-link text-primary {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i> Rôles
                    </a>
                    @endcan
                    @can('gerer_parametres_couverture')
                    <a href="{{ route('parametres-couverture.index') }}" class="sidebar-link text-info {{ request()->routeIs('parametres-couverture.*') ? 'active' : '' }}">
                        <i class="bi bi-sliders"></i> Couvertures
                    </a>
                    @endcan
                @endauth
            </div>
            
            <div class="mt-auto p-3 text-center" style="font-size: 0.8rem; color: #aaa;">
                &copy; {{ date('Y') }} Gest-IPM
            </div>
        </aside>
        @endif

        <!-- Page Content -->
        <div id="page-content-wrapper" class="{{ request()->is('login') ? 'ms-0 w-100' : '' }}">
            
            @if(!request()->is('login'))
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top" style="background: rgba(255, 255, 255, 0.9) !important; backdrop-filter: blur(10px);">
                <div class="container-fluid px-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-list fs-2 me-3" id="menu-toggle"></i>
                        <h4 class="mb-0 fw-bold d-none d-md-block text-dark">Espace Gestion</h4>
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="d-md-none ms-2" style="max-height: 35px; width: auto; object-fit: contain;" onerror="this.style.display='none';">
                    </div>

                    @auth
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle btn btn-light px-3 py-2 rounded-pill shadow-sm d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--primary-blue) !important; font-weight: 600; border: 1px solid #eef0f3;">
                                <i class="bi bi-person-circle me-2 fs-5"></i> 
                                <span class="d-none d-md-inline">{{ auth()->user()->login }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2" aria-labelledby="navbarDropdown">
                                <li><h6 class="dropdown-header text-center">{{ auth()->user()->login }}</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger fw-semibold py-2 d-flex align-items-center">
                                            <i class="bi bi-box-arrow-right me-2"></i>Se déconnecter
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endauth
                </div>
            </nav>
            @endif

            <!-- Main Content Container -->
            <div class="container-fluid {{ request()->is('login') ? 'p-0 m-0' : 'px-4 py-4 mt-4' }}">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @yield('modals')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var wrapper = document.getElementById("wrapper");
            var toggleBtn = document.getElementById("menu-toggle");
            var overlay = document.getElementById("sidebar-overlay");

            // Toggle menu
            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                wrapper.classList.toggle("toggled");
            });

            // Close menu when clicking overlay on mobile
            if(overlay) {
                overlay.addEventListener("click", function(e) {
                    wrapper.classList.remove("toggled");
                });
            }

            // Fix for Modals inside table-responsive or deeper divs
            document.querySelectorAll('.modal').forEach(function(modal) {
                document.body.appendChild(modal);
            });
        });
    </script>
</body>
</html>
