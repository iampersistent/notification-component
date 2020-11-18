<?php
declare(strict_types=1);

namespace Notification;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                \Twig\Environment::class          => \Mezzio\Twig\TwigEnvironmentFactory::class,
                \Mezzio\Twig\TwigExtension::class => \Mezzio\Twig\TwigExtensionFactory::class,
            ],
        ];
    }
}
