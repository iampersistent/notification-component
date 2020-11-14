<?php
declare(strict_types=1);

namespace Notification\Factory;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class NotifierFactory
{
    public function __invoke(ContainerInterface $container): NotifierInterface
    {
        $config = (new GatherConfigValues)($container, 'notification');

        return new Notifier();
    }
}
