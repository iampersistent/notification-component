<?php
declare(strict_types=1);

namespace Notification\Factory\Transport\Email;

use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesSmtpTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AmazonSMTPFactory
{
    public function __invoke(ContainerInterface $container, array $config): SesSmtpTransport
    {
        $dispatcher = $container->get(EventDispatcherInterface::class);
        $logger = null;

        return new SesSmtpTransport($config['username'], $config['password'], $config['region'], $dispatcher, $logger);
    }
}
