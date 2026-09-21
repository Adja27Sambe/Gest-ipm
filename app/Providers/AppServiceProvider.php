<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Prestation;
use App\Models\HistoriqueMedical;
use App\Observers\PrestationObserver;
use App\Observers\HistoriqueMedicalObserver;
use App\Observers\PaiementPrestataireObserver;
use App\Models\PaiementPrestataire;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogAuthenticationAction;
use App\Events\SalarieRadie;
use App\Listeners\UpdateAyantDroitStatut;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Prestation::observe(PrestationObserver::class);
        HistoriqueMedical::observe(HistoriqueMedicalObserver::class);
        PaiementPrestataire::observe(PaiementPrestataireObserver::class);
        \App\Models\Salarie::observe(\App\Observers\SalarieObserver::class);
        \App\Models\Entreprise::observe(\App\Observers\EntrepriseObserver::class);
        \App\Models\Demande::observe(\App\Observers\DemandeObserver::class);
        Event::listen(Login::class, LogAuthenticationAction::class);
        Event::listen(Logout::class, LogAuthenticationAction::class);
        Event::listen(
            SalarieRadie::class,
            UpdateAyantDroitStatut::class,
        );

        // --- Directives Blade pour la distinction Lecteur / Éditeur ---
        \Illuminate\Support\Facades\Blade::if('canedit', function () {
            return auth()->check() && auth()->user()->canEdit();
        });

        \Illuminate\Support\Facades\Blade::if('readonly', function () {
            return auth()->check() && auth()->user()->isReadOnly();
        });

        // --- Optimisation Mode Strict ---
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        // --- Optimisation de la Gestion des Rôles (Gates) ---
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Passe-droit global pour les Administrateurs
            if ($user && $user->role && ($user->role->libelle === 'Administrateur' || $user->role->code === 'Administrateur')) {
                return true;
            }
        });

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permission')) {
                // Mise en cache des permissions pour éviter un appel DB à chaque requête
                $permissions = \Illuminate\Support\Facades\Cache::rememberForever('permissions_all', function () {
                    return \App\Models\Permission::all();
                });

                foreach ($permissions as $permission) {
                    if ($permission->code) {
                        \Illuminate\Support\Facades\Gate::define($permission->code, function ($user) use ($permission) {
                            return $user->hasPermission($permission->code);
                        });
                    }
                    if ($permission->libelle && $permission->libelle !== $permission->code) {
                        \Illuminate\Support\Facades\Gate::define($permission->libelle, function ($user) use ($permission) {
                            return $user->hasPermission($permission->libelle);
                        });
                    }
                }

                // Alias de sécurité pour la compatibilité des routes et vues existantes
                \Illuminate\Support\Facades\Gate::define('Gérer la facturation', function ($user) {
                    return $user->hasPermission('gerer_facturation') || $user->hasPermission('Gérer la facturation');
                });
            }
        } catch (\Exception $e) {
            // Silencieux si la BDD n'est pas encore prête
        }
    }
}
