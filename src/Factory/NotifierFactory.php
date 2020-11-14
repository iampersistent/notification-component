<?php
declare(strict_types=1);

namespace Notification\Factory;

use ConfigValue\GatherConfigValues;
use Notification\Factory\Channel\EmailChannelFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class NotifierFactory
{
    public function __invoke(ContainerInterface $container): NotifierInterface
    {
        $channels = [];
        $emailConfig = (new GatherConfigValues)($container, 'notification_email');
        if (!empty($emailConfig)) {
            $channels[] = (new EmailChannelFactory)($container, $emailConfig);
        }

        return new Notifier($channels);
    }
}
