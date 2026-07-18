<?php

namespace App\Events;

use App\Models\Devis;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DevisStatutChange
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $devis;
    public $oldStatut;
    public $newStatut;

    /**
     * Create a new event instance.
     */
    public function __construct(Devis $devis, string $oldStatut, string $newStatut)
    {
        $this->devis = $devis;
        $this->oldStatut = $oldStatut;
        $this->newStatut = $newStatut;
    }
}
