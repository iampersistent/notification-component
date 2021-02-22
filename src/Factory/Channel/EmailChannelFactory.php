<?php
declare(strict_types=1);

namespace Notification\Factory\Channel;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

final class EmailChannelFactory
{
    /** @var string */
    private $channel;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    public function __invoke(ContainerInterface $container): ChannelInterface
    {
        $config = (new GatherConfigValues)($container, $this->channel);
        $transport = $container->get($config['transport']);
        $messageBusName = $config['message_bus'] ?? 'messenger.bus.email';
        $messageBus = $container->get($messageBusName);
        $from = $config['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
