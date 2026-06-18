<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class MembershipEndingSoon extends Notification
{
    use Queueable;

    protected $expirationDate;

    public function __construct($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Asumimos que el modelo notifiable (Socio) tiene una relación 'usuario'.
        $nombreSocio = $notifiable->usuario->nombre1 ?? 'Socio';
        $formattedDate = Carbon::parse($this->expirationDate)->format('d/m/Y');

        return (new MailMessage)
                    ->subject('Recordatorio de Vencimiento de Membresía')
                    ->greeting('¡Hola, ' . $nombreSocio . '!')
                    ->line('Te recordamos que tu membresía en nuestro gimnasio está a punto de vencer.')
                    ->line('Fecha de vencimiento: **' . $formattedDate . '**')
                    ->action('Renovar Ahora', url('/socio/perfil')) // Ruta del portal de socios
                    ->line('¡No pierdas tu progreso! Te esperamos para que continúes entrenando con nosotros.')
                    ->salutation('Saludos, El Equipo del Gimnasio');
    }
}