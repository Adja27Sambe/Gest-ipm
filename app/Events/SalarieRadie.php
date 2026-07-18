<?php

namespace App\Events;

use App\Models\Salarie;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalarieRadie
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $salarie;

    /**
     * Create a new event instance.
     */
    public function __construct(Salarie $salarie)
    {
        $this->salarie = $salarie;
    }
}
