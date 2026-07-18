<?php

namespace App\Traits;

use App\Models\HistoriqueMouvement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     *
     * @return void
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAction('created');
        });

        static::updated(function ($model) {
            $model->logAction('updated');
        });

        static::deleted(function ($model) {
            $model->logAction('deleted');
        });
    }

    /**
     * Log the action to the HistoriqueMouvement table.
     *
     * @param  string  $action
     * @return void
     */
    protected function logAction($action)
    {
        $ancienneValeur = null;
        $nouvelleValeur = null;

        if ($action === 'created') {
            $nouvelleValeur = json_encode($this->getAttributes());
        } elseif ($action === 'updated') {
            // Uniquement les champs modifiés
            $nouvelleValeur = json_encode($this->getChanges());
            
            $anciens = [];
            foreach ($this->getChanges() as $key => $value) {
                $anciens[$key] = $this->getOriginal($key);
            }
            $ancienneValeur = json_encode($anciens);
            
            // S'il n'y a pas de changement réel, on ne logge pas
            if (empty($anciens)) {
                return;
            }
        } elseif ($action === 'deleted') {
            $ancienneValeur = json_encode($this->getAttributes());
        }

        HistoriqueMouvement::create([
            'date_heure' => now(),
            'module' => class_basename($this),
            'action' => $action,
            'description' => "Enregistrement {$action} dans " . class_basename($this),
            'adresse_ip' => Request::ip(),
            'ancienne_valeur' => $ancienneValeur,
            'nouvelle_valeur' => $nouvelleValeur,
            'id_utilisateur' => Auth::id(),
        ]);
    }
}
