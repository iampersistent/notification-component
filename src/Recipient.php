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
    use SmsRecipientTrait;

    /** @var string */
    private $name;

    public function __construct(string $email = '', string $name = '', string $phone = '')
    {
        if ('' === $email && '' === $phone) {
            throw new InvalidArgumentException(sprintf('"%s" needs an email or a phone but both cannot be empty.', static::class));
        }

        $this->email = $email;
        $this->name = $name;
        $this->phone = $phone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    // Name <email@address.com>
}
