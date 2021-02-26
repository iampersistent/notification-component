<?php
declare(strict_types=1);

namespace Notification\Factory\Communication;

use Notification\Context\NotificationContext;
use Symfony\Component\Notifier\Notification\Notification as Communication;

interface CommunicationFactoryInterface
{
    public function create(NotificationContext $context, string $channel): Communication;
}
