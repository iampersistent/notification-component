<?php
declare(strict_types=1);

namespace Notification\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class NotifierFactory
{
    public function __invoke(ContainerInterface $container): NotifierInterface
    {
        $channels = [];
        $channelList = $container->get('config')['notification']['channel'];
        foreach ($channelList as $channel => $config) {
            $channels[$channel] = $container->get($config['channel']);
        }

        return new Notifier($channels);
    }
}
