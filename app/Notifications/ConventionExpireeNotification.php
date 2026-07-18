<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConventionExpireeNotification extends Notification
{
    use Queueable;

    protected $convention;

    /**
     * Create a new notification instance.
     */
    public function __construct($convention)
    {
        $this->convention = $convention;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'convention_id' => $this->convention->id_convention,
            'prestataire' => $this->convention->prestataire->nom,
            'date_fin' => $this->convention->date_fin,
            'message' => 'La convention avec ' . $this->convention->prestataire->nom . ' expire dans moins de 30 jours (' . $this->convention->date_fin . ').'
        ];
    }
}
