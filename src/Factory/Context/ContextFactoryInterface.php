<?php
declare(strict_types=1);

namespace Notification\Factory\Context;

use Psr\Container\ContainerInterface;

interface ContextFactoryInterface
{
    public function create(ContainerInterface $container, array $data);
}
