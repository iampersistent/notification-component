<?php
declare(strict_types=1);

namespace Notification\Context;

final class NotificationContext
{
    /** @var array */
    private $data;
    /** @var array */
    private $meta;

    public function __construct(array $data = [], array $meta = [])
    {
        $this->data = $data;
        $this->meta = $meta;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function setMeta(string $key, $meta): self
    {
        $this->meta[$key] = $meta;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
