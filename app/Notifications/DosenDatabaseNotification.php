<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DosenDatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $title, private readonly string $message, private readonly array $payload = []) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        $preference = $notifiable->notificationPreference;
        $category = $this->payload['email_category'] ?? $this->payload['category'] ?? null;

        $categoryEnabled = match ($category) {
            'assignment', 'inbox' => (bool) ($preference?->email_for_assignments ?? false),
            'calendar', 'schedule' => (bool) ($preference?->email_for_schedule_changes ?? false),
            'document' => (bool) ($preference?->email_for_documents ?? false),
            default => false,
        };

        if (config('dosen_farmasi.integration.mail_enabled', true) && ($preference?->email_enabled ?? false) && $categoryEnabled && filled($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'payload' => $this->payload,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Informasi Dosen Farmasi UBP')
            ->line($this->message)
            ->line('Silakan buka aplikasi Dosen Farmasi UBP untuk melihat detail lengkap.');
    }
}
