<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Notification;

final class GenericNotification extends Notification
{
    public function dispatch(string $subject, string $body, array $recipients)
    {
        $this->context = [
            'body'    => $body,
        ];
        $this->setSubject($subject);

        $this->send($recipients);
    }

    protected function getAllowedChannels(): array
    {
        return [
            'email',
        ];
    }

    protected function getEmailHtmlTemplate(): ?string
    {
        return 'generic';
    }

    protected function getEmailTextTemplate(): ?string
    {
        return null;
    }
}
