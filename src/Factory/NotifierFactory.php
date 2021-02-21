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
        $channelList = $container->get('config')['notifications']['channels'];
        foreach ($channelList as $channel => $config) {
            $transport = $config['transport'] ?? "messenger.transport.{$channel}";
            $name = "$channel/$transport";
            $factory = $config['factory'] ?? 'Notification\\Factory\\Channel\\' . ucfirst($channel) . 'ChannelFactory';
            $config = $config['config'] ?? "notification_$channel";

            $channels[$channel] = (new $factory)($container, $config);
        }

        return new Notifier($channels);
    }
}
