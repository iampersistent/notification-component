<?php
declare(strict_types=1);

namespace Notification\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Notification\Context\NotificationContext;
use Notification\Notification;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;

class NotificationFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (is_a($requestedName, Notification::class, true));
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['notifications'];

        $channels = $this->getChannels($requestedName, $config);
        $communicationFactories = $this->getCommunicationFactories($container, $config['channels']);
        $context = $this->getContext($container, $config['context']);
        $notifier = $container->get(NotifierInterface::class);

        return new $requestedName($notifier, $context, $channels, $communicationFactories);
    }

    protected function getChannels(string $requestedName, array $config): array
    {
        $channelNames = $config['default']['channels'] ?? ['email'];
        if (isset($config[$requestedName])) {
            $channelNames = $config[$requestedName]['channels'] ?? $channelNames;
        }
        $channels = [];
        foreach ($channelNames as $channel) {
            $channels[$channel] = $channel . '/' . $config['channel'][$channel]['messenger'];
        }

        return $channels;
    }

    protected function getCommunicationFactories(ContainerInterface $container, array $config): array
    {
        $factories = [];
        foreach ($config as $channel => $settings) {
            $factories[$channel] = $container->get($settings['communication_factory']);
        }

        return $factories;
    }

    protected function getContext(ContainerInterface $container, array $config): NotificationContext
    {
        $meta = [];
        foreach ($config as $channel => $context) {
            $factory = $context['factory'];
            $meta[$channel] = (new $factory())->create($container, $context['data']);
        }

        return new NotificationContext([], $meta);
    }
}
