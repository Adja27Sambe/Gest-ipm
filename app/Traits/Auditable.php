<?php

namespace App\Traits;

use App\Models\HistoriqueMouvement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

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
        $module = class_basename($this);

        if ($action === 'created') {
            $nouvelleValeur = json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
        } elseif ($action === 'updated') {
            // Uniquement les champs modifiés
            $changes = $this->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) {
                return;
            }

            $nouvelleValeur = json_encode($changes, JSON_UNESCAPED_UNICODE);
            
            $anciens = [];
            foreach ($changes as $key => $value) {
                $anciens[$key] = $this->getOriginal($key);
            }
            $ancienneValeur = json_encode($anciens, JSON_UNESCAPED_UNICODE);
        } elseif ($action === 'deleted') {
            $ancienneValeur = json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
        }

        $identifier = $this->getKey();
        $actionFr = match($action) {
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            default => ucfirst($action),
        };

        try {
            HistoriqueMouvement::create([
                'date_heure' => now(),
                'module' => $module,
                'action' => $actionFr,
                'description' => "{$actionFr} dans le module {$module} (ID: #{$identifier})",
                'adresse_ip' => Request::ip() ?? '127.0.0.1',
                'ancienne_valeur' => $ancienneValeur,
                'nouvelle_valeur' => $nouvelleValeur,
                'id_utilisateur' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Erreur journalisation audit ({$module}): " . $e->getMessage());
        }
    }
}

