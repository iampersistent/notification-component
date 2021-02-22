<?php
declare(strict_types=1);

namespace Notification\Factory\Transport;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;

final class NotificationTransportFactory
{
    /** @var string */
    private $config;

    public function __construct(string $config)
    {
        $this->config = $config;
    }

    public function __invoke(ContainerInterface $container)
    {
        $config = (new GatherConfigValues)($container, $this->config);

        $transportFactory = 'Notification\\Factory\\Transport\\Email\\' . $config['type'] . 'Factory';

        return (new $transportFactory($this->config))($container);
    }
}
