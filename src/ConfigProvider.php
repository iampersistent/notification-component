<?php
declare(strict_types=1);

namespace Notification;

use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Netglue\PsrContainer\Messenger\Container\MessageBusStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\BusNameStampMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageHandlerMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageSenderMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\TransportFactory;
use Notification\Command\SendTestEmailCommand;
use Notification\Factory\Channel\EmailChannelFactory;
use Notification\Factory\Communication\EmailCommunicationFactory;
use Notification\Factory\Context\EmailContextFactory;
use Notification\Factory\EventDispatcherFactory;
use Notification\Factory\NotifierFactory;
use Notification\Factory\Transport\NotificationTransportFactory;
use Notification\Factory\EmailBusLocatorFactory;
use Notification\Factory\MessageHandlerFactory;
use Notification\Factory\NotificationFactory;
use Notification\Locator\EmailBusLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Notifier\Notifier;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

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
                'notification:send-test-email' => SendTestEmailCommand::class,
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
                'messenger.bus.email'                      => new MessageBusStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.sender-middleware'    => new MessageSenderMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.handler-middleware'   => new MessageHandlerMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.bus-stamp-middleware' => new BusNameStampMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.transport.email'                => [TransportFactory::class, 'messenger.transport.email'],
                'messenger.handler.email'                  => new MessageHandlerFactory('notification.transport.email'),
                'notification.channel.email'               => new EmailChannelFactory('notification_channel_email'),
                'notification.transport.email'             => new NotificationTransportFactory(
                    'notification_transport_email'
                ),
                EmailBusLocator::class                     =>
                    new EmailBusLocatorFactory(
                        'messenger.bus.email'
                    ),
                EventDispatcherInterface::class            => EventDispatcherFactory::class,
                TwigExtension::class                       => TwigExtensionFactory::class,
                Environment::class                         => TwigEnvironmentFactory::class,
                Notifier::class                            => NotifierFactory::class,
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
                        SendEmailMessage::class => ['messenger.handler.email'],
                    ],
                    'middleware'           => [
                        'messenger.bus.email.bus-stamp-middleware',
                        'messenger.bus.email.sender-middleware',
                        'messenger.bus.email.handler-middleware',
                    ],
                    'routes'               => [
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
                'serializer'     => PhpSerializer::class,
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
                    'channel'               => 'notification.channel.email',
                    'communication_factory' => EmailCommunicationFactory::class,
                    'message_bus'           => 'messenger.bus.email',
                    'messenger'             => 'messenger.transport.email',
                    'transport'             => 'notification.transport.email',
                ],
            ],
            'context'  => [
                'email' => [
                    'factory' => EmailContextFactory::class,
                    'data'    => [
                    ],
                ],
            ],
        ];
    }
}
