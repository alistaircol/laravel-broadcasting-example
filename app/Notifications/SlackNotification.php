<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackNotification extends Notification
{
    use Queueable;

    protected string $message;
    protected array $fields;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $message, array $fields = [])
    {
        $this->message = $message;
        $this->fields = $fields;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'slack',
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->from(config('app.name'))
            ->attachment(function (SlackAttachment $attachment) {
                $image = 'https://static.wikia.nocookie.net/dogelore/images/8/87/411.png/revision/latest?cb=20200330152532';
                $attachment
                    ->pretext('Cheems')
                    ->fallback('Cheems')
                    ->title('Cheems', $image)
                    ->image($image)
                    ->content('Cheems')
                    ->action('Cheems', 'https://ac93.uk/i-like-doge')
                    ->author('Cheems', 'https://ac93.uk/author-link')
                    ->footer('Cheems');
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'fields' => $this->fields,
        ];
    }
}
