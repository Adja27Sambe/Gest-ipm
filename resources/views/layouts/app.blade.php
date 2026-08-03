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
            /* Palette Moderne pastel/glassmorphism */
            --primary-blue: #0d6efd; /* Bootstrap primary */
            --primary-blue-hover: #0b5ed7;
            --secondary-green: #20c997; /* Teal/Mint modern */
            --secondary-green-hover: #1aa179;
            --bg-gradient-start: #f8f9fa;
            --bg-gradient-end: #e2e8f0;
            --sidebar-width: 280px;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            background-attachment: fixed;
            color: #2d3748;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* --- Dashboard Layout --- */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar - Glassmorphism */
        #sidebar-wrapper {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-right: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
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
            padding: 2rem 1rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            background: transparent;
            z-index: 10;
        }

        .sidebar-nav {
            padding: 1.5rem 1rem;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.2rem;
            color: #4a5568;
            text-decoration: none;
            font-weight: bold;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-link i {
            font-size: 1.2rem;
            margin-right: 1rem;
            color: #a0aec0;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.9);
            color: var(--primary-blue);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .sidebar-link:hover i {
            color: var(--primary-blue);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #0a58ca 100%);
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .sidebar-link.active i {
            color: white;
        }

        /* Branding border on the top navbar */
        .brand-border {
            height: 5px;
            width: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1050;
        }

        /* Main Content Area */
        #page-content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-width: 0;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header - Glassmorphism */
        .top-navbar {
            background: rgba(255, 255, 255, 0.6) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        #menu-toggle {
            cursor: pointer;
            color: #2d3748;
            background: white;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        #menu-toggle:hover { 
            color: var(--primary-blue); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .main-container {
            padding: 2rem;
            animation: fadeIn 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toggled State for Desktop */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: calc(var(--sidebar-width) * -1);
        }
        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }

        /* Modern UI Elements */
        
        /* Cards */
        .card {
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            background: rgba(255, 255, 255, 0.95);
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.03);
            padding: 1.5rem 1.75rem;
            border-radius: 20px 20px 0 0 !important;
        }
        .card-body { padding: 1.75rem; }

        /* Buttons */
        .btn {
            font-weight: 600;
            border-radius: 12px;
            padding: 0.7rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
            letter-spacing: 0.3px;
        }
        .btn:active { transform: scale(0.96); }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #00d2ff 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0b5ed7 0%, #00c6f0 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
            color: #fff;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary-green) 0%, #48c6ef 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(32, 201, 151, 0.3);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #1aa179 0%, #3eb6de 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 201, 151, 0.4);
            color: #fff;
        }

        .btn-light {
            background: rgba(255,255,255,0.9);
            color: #2d3748;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-light:hover {
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: var(--primary-blue);
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            border: 1px solid rgba(0,0,0,0.08);
            background-color: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
            color: #4a5568;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
            border-color: var(--primary-blue);
        }

        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table th {
            font-weight: 700;
            color: #a0aec0;
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1rem 1.5rem;
        }
        .table td { 
            vertical-align: middle; 
            background: #fff;
            padding: 1rem 1.5rem;
            border: none;
        }
        .table tbody tr {
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .table tbody tr td:first-child { border-radius: 12px 0 0 12px; }
        .table tbody tr td:last-child { border-radius: 0 12px 12px 0; }
        
        .clickable-row { cursor: pointer; }
        .clickable-row:hover {
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            z-index: 2;
            position: relative;
        }

        /* Badges */
        .badge {
            padding: 0.5em 1em;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1); 
            }
            #page-content-wrapper {
                margin-left: 0;
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0; 
                box-shadow: 10px 0 30px rgba(0,0,0,0.15);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 1035;
                transition: all 0.3s ease;
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
                    <a href="{{ route('reporting.index') }}" class="sidebar-link {{ request()->routeIs('reporting.*') ? 'active' : '' }}">
                        <i class="bi bi-pie-chart"></i> Tableau de bord
                    </a>
                    <hr class="text-secondary opacity-25 my-3">
                    
                    @can('gerer_demandes')
                    <a href="{{ route('demandes.index') }}" class="sidebar-link {{ request()->routeIs('demandes.*') && !request()->routeIs('demandes.validation.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Demandes
                    </a>
                    <a href="{{ route('demandes.validation.index') }}" class="sidebar-link {{ request()->routeIs('demandes.validation.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i> Validation Demandes
                    </a>
                    @endcan
                    @can('gerer_entreprises')
                    <a href="{{ route('entreprises.index') }}" class="sidebar-link {{ request()->routeIs('entreprises.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Adhérents
                    </a>
                    @endcan
                    @can('gerer_salaries')
                    <a href="{{ route('salaries.index') }}" class="sidebar-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Participants
                    </a>
                    @endcan
                    @can('gerer_prestataires')
                    <a href="{{ route('praticiens.index') }}" class="sidebar-link {{ request()->routeIs('praticiens.*') ? 'active' : '' }}">
                        <i class="bi bi-hospital"></i> Praticiens
                    </a>
                    <a href="{{ route('pharmacies.index') }}" class="sidebar-link {{ request()->routeIs('pharmacies.*') ? 'active' : '' }}">
                        <i class="bi bi-capsule"></i> Pharmacies
                    </a>
                    @endcan
                    @can('gerer_prestations')

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
                    <a href="{{ route('dossier-medical.index') }}" class="sidebar-link {{ request()->routeIs('dossier-medical.*') ? 'active' : '' }}">
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
                    <a href="{{ route('audit.index') }}" class="sidebar-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Audit
                    </a>
                    @endcan
                    @can('gerer_roles')
                    <a href="{{ route('utilisateurs.index') }}" class="sidebar-link {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Utilisateurs
                    </a>
                    <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i> Rôles
                    </a>
                    @endcan
                    @can('gerer_parametres_couverture')
                    <a href="{{ route('parametres-couverture.index') }}" class="sidebar-link {{ request()->routeIs('parametres-couverture.*') ? 'active' : '' }}">
                        <i class="bi bi-sliders"></i> Couvertures
                    </a>
                    @endcan
                @endauth
            </div>
            
            <div class="mt-auto p-3 text-center" style="font-size: 0.8rem; color: #8c98a4;">
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
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Des erreurs ont été trouvées :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
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

            // Clickable Rows
            document.querySelectorAll('.clickable-row').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input') && !e.target.closest('.dropdown')) {
                        if (this.dataset.href) {
                            window.location = this.dataset.href;
                        }
                    }
                });
            });

            // Initialize Dynamic Search on all GET search forms
            initDynamicSearch();
        });

        // --- Global Dynamic Search Engine ---
        function initDynamicSearch() {
            const searchInputs = document.querySelectorAll('form[method="GET"] input[name="search"], input.dynamic-search-input');
            
            searchInputs.forEach(input => {
                const form = input.closest('form');
                let debounceTimer;

                // Instant local DOM filtering for immediate visual response
                input.addEventListener('input', function() {
                    const query = this.value.trim().toLowerCase();
                    const table = document.querySelector('table');
                    
                    if (table) {
                        const rows = table.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (!query || text.includes(query)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    // Debounced server fetch for exact results and pagination sync
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        if (form) {
                            performAjaxSearch(form);
                        }
                    }, 300);
                });

                // Instant trigger on select filter changes
                if (form) {
                    const selects = form.querySelectorAll('select:not([name="per_page"])');
                    selects.forEach(select => {
                        select.addEventListener('change', function() {
                            performAjaxSearch(form);
                        });
                    });
                }
            });
        }

        function performAjaxSearch(form) {
            const url = new URL(form.action || window.location.href);
            const formData = new FormData(form);
            
            formData.forEach((value, key) => {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });

            window.history.replaceState({}, '', url);

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Replace table body or responsive container
                const currentTableContainer = document.querySelector('.table-responsive') || document.querySelector('table');
                const newTableContainer = doc.querySelector('.table-responsive') || doc.querySelector('table');
                
                if (currentTableContainer && newTableContainer) {
                    currentTableContainer.innerHTML = newTableContainer.innerHTML;
                }

                // Rebind clickable rows
                document.querySelectorAll('.clickable-row').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input') && !e.target.closest('.dropdown')) {
                            if (this.dataset.href) {
                                window.location = this.dataset.href;
                            }
                        }
                    });
                });
            })
            .catch(err => console.error('Erreur recherche dynamique:', err));
        }
    </script>
    @stack('scripts')
</body>
</html>
