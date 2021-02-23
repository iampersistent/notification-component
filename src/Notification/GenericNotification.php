<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Notification;

final class GenericNotification extends Notification
{
    public function dispatch(string $subject, string $body)
    {
        $this->context->set('body', $body);
        $this->setSubject($subject);

        $this->send();
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
