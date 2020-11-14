<?php
declare(strict_types=1);

namespace Notification;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientTrait;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientTrait;

final class Recipient implements EmailRecipientInterface, SmsRecipientInterface
{
    use EmailRecipientTrait;
    use SmsRecipientTrait;

    /** @var string[] */
    private $channels;
    /** @var string */
    private $email;
    /** @var string */
    private $name;
    /** @var string */
    private $phone;

    public function __construct(array $channels = [])
    {
        $this->channels = $channels;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function setEmail(string $email): Recipient
    {
        $this->email = $email;
        $this->channels[] = 'email';

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Recipient
    {
        $this->name = $name;

        return $this;
    }

    public function setPhone(string $phone): Recipient
    {
        $this->phone = $phone;
        $this->channels[] = 'sns';

        return $this;
    }
}
