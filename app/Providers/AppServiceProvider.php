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
        Event::listen(Login::class, LogAuthenticationAction::class);
        Event::listen(Logout::class, LogAuthenticationAction::class);
        Event::listen(
            SalarieRadie::class,
            UpdateAyantDroitStatut::class,
        );

        // --- Optimisation Mode Strict ---
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        // --- Optimisation de la Gestion des Rôles (Gates) ---
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Passe-droit global pour les Administrateurs
            if ($user->role && $user->role->libelle === 'Administrateur') {
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
                    \Illuminate\Support\Facades\Gate::define($permission->libelle, function ($user) use ($permission) {
                        return $user->hasPermission($permission->libelle);
                    });
                }
            }
        } catch (\Exception $e) {
            // Silencieux si la BDD n'est pas encore prête
        }
    }
}
