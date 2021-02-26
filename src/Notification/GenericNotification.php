<?php
declare(strict_types=1);

namespace Notification\Notification;

use Notification\Notification;

final class GenericNotification extends Notification
{
    public function dispatch(string $subject, string $body)
    {
        $this->context->set('body', $body);
        /** @var \Notification\Context\EmailContext $emailContext */
        $emailContext = $this->context->getMeta('email');
        $emailContext
            ->setHtmlTemplate('generic')
            ->setSubject($subject);
        $this->send();
    }

    protected function getAllowedChannels(): array
    {
        return [
            'email',
        ];
    }
}
