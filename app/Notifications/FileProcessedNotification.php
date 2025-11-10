<?php

namespace App\Notifications;

use App\Models\FileUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public FileUpload $upload)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your CSV File Has Been Processed')
            ->line("File: {$this->upload->filename}")
            ->line("Path: {$this->upload->path}")
            ->line("Status: {$this->upload->status}")
            ->action('View Uploads', url('/uploads'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'filename' => $this->upload->filename,
            'path' => $this->upload->path,
            'status' => $this->upload->status,
        ];
    }
}
