<?php
declare(strict_types=1);

namespace Notification;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Netglue\PsrContainer\Messenger\Container\MessageBusStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageHandlerMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageSenderMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\TransportFactory;
use Notification\Factory\Channel\EmailChannelFactory;
use Notification\Factory\EmailBusLocatorFactory;
use Notification\Factory\MessageHandlerFactory;
use Notification\Factory\NotificationFactory;
use Notification\Locator\EmailBusLocator;
use Notification\Notification\GenericNotification;
use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'laminas-cli'   => $this->getConsoleConfig(),
            'notifications' => $this->getNotifications(),
            'symfony'       => [
                'messenger' => $this->getMessenger(),
            ],
        ];
    }

    private function getConsoleConfig(): array
    {
        return [
            'commands' => [
                'notification:send-test-email' => \Notification\Command\SendTestEmailCommand::class,
            ],
        ];
    }

    private function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                NotificationFactory::class,
            ],
            'factories'          => [
                'messenger.bus.email'                    => new MessageBusStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.sender-middleware'  => new MessageSenderMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.handler-middleware' => new MessageHandlerMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.transport.email'              => [TransportFactory::class, 'messenger.transport.email'],
                'messenger.handler.email'                => new MessageHandlerFactory('messenger.transport.email'),
                EmailBusLocator::class                   =>
                    new EmailBusLocatorFactory(
                        'messenger.bus.email'
                    ),
                EventDispatcherInterface::class          => \Notification\Factory\EventDispatcherFactory::class,
                \Mezzio\Twig\TwigExtension::class        => \Mezzio\Twig\TwigExtensionFactory::class,
                \Twig\Environment::class                 => \Mezzio\Twig\TwigEnvironmentFactory::class,
            ],
        ];
    }

    private function getMessenger(): array
    {
        return [
            'routing'    => [
                SendEmailMessage::class => 'messenger.transport.email',
            ],
            'buses'      => [
                'messenger.bus.email' => [
                    'allows_zero_handlers' => true,
                    'handler_locator'      => EmailBusLocator::class,
                    'handlers'             => [
                        SendEmailMessage::class => ['messenger.transport.email'],
                    ],
                    'middleware'           => [
                        'messenger.bus.email.sender-middleware',
                        'messenger.bus.email.handler-middleware',
                    ],
                    'routes' => [
                        '*' => ['messenger.transport.email'],
                    ],
                ],
            ],
            'transports' => $this->getMessengerTransports(),
        ];
    }

    private function getMessengerTransports(): array
    {
        return [
            'messenger.transport.email' => [
                'dsn'            => 'doctrine://dbal-default?queue_name=email',
                'serializer'     => SerializerInterface::class, // custom serializer service
                'options'        => [
                ],
                'retry_strategy' => [
                    'max_retries' => 3,
                    'delay'       => 1000,
                    'multiplier'  => 2,
                    'max_delay'   => 0,
                ],
            ],
        ];
    }

    private function getNotifications(): array
    {
        return [
            'default'  => [
                'channels' => [
                    'email',
                ],
            ],
            'channels' => [
                'email' => [
                    'config'    => 'notification_email',
                    'factory'   => EmailChannelFactory::class,
                    'transport' => 'messenger.transport.email',
                ],
            ],
        ];
    }
}
