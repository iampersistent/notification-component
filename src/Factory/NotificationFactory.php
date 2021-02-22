<?php
declare(strict_types=1);

namespace Notification\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Notification\Notification;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\NotifierInterface;

final class NotificationFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (is_a($requestedName, Notification::class, true));
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['notifications'];
        $channelNames = $config['default']['channels'] ?? ['email'];
        if (isset($config[$requestedName])) {
            $channelNames = $config[$requestedName]['channels'] ?? $channelNames;
        }
        $channels = [];
        foreach ($channelNames as $channel) {
            $channels[$channel] = $channel . '/' . $config['channels'][$channel]['messenger'];
        }
        $notifier = $container->get(NotifierInterface::class);
        $renderer = $container->get(BodyRenderer::class);

        return new $requestedName($notifier, $renderer, $channels, []);
    }
}
