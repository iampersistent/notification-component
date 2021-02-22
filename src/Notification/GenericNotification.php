<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Context\NotificationContext;
use Notification\Notification;

final class GenericNotification extends Notification
{
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

    protected function handleContext(NotificationContext $notificationContext)
    {
        $this->context = [
            'body'    => $notificationContext->get('body'),
        ];
        $this->setSubject($notificationContext->get('subject'));
    }
}
