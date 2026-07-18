<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\HistoriqueMouvement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LogAuthenticationAction
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $action = '';
        $description = '';
        $user = $event->user; // Peut être null si échec, mais ici on gère Login/Logout qui ont l'utilisateur

        if ($event instanceof Login) {
            $action = 'Connexion';
            $description = 'Connexion de l\'utilisateur au système.';
        } elseif ($event instanceof Logout) {
            $action = 'Déconnexion';
            $description = 'Déconnexion de l\'utilisateur.';
        } else {
            return;
        }

        if ($user) {
            HistoriqueMouvement::create([
                'id_utilisateur' => $user->id_utilisateur,
                'date_heure' => Carbon::now(),
                'module' => 'Authentification',
                'action' => $action,
                'description' => $description,
                'adresse_ip' => $this->request->ip(),
                'ancienne_valeur' => null,
                'nouvelle_valeur' => null,
            ]);
        }
    }
}
