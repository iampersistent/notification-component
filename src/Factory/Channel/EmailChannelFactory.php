<?php
declare(strict_types=1);

namespace Notification\Factory\Channel;

use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

final class EmailChannelFactory
{
    public function __invoke(ContainerInterface $container, array $emailConfig): EmailChannel
    {
        $transportFactory = "Notification\\Factory\\Transport\\Email\\{$emailConfig['transport']}Factory";
        $transport = (new $transportFactory)($container, $emailConfig);
        $messageBus = null;
        $from = null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
