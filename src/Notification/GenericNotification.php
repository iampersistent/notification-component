<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Notification;

final class GenericNotification extends Notification
{
    public function setBody(string $body): Notification
    {
        $this->body = $body;
        $this->context['body'] = $body;

        return $this;
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
