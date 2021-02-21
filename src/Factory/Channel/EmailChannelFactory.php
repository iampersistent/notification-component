<?php
declare(strict_types=1);

namespace Notification\Factory\Channel;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

final class EmailChannelFactory
{
    public function __invoke(ContainerInterface $container, string $configName): ChannelInterface
    {
        $config = (new GatherConfigValues)($container, $configName);
        $transportFactory = "Notification\\Factory\\Transport\\Email\\{$config['transport']}Factory";
        $transport = (new $transportFactory)($container, $config);
        $messageBusName = $config['message_bus'] ?? 'messenger.bus.email';
        $messageBus = $container->get($messageBusName);
        $from = $config['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
