<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Notification;

final class GenericNotification extends Notification
{
    protected function getEmailHtmlTemplate(): ?string
    {
        return 'generic';
    }

    protected function getEmailTextTemplate(): ?string
    {
        return null;
    }
}
