<?php
declare(strict_types=1);

namespace Notification;

final class RecipientChannels
{
    /** @var array */
    private $channelRecipients = [];

    public function addRecipientsToChannel(string $channel, $recipients): self
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }
        $this->channelRecipients[$channel] = array_merge($this->channelRecipients, $recipients);

        return $this;
    }

    public function getForChannel(string $channel): array
    {
        return $this->channelRecipients[$channel] ?? [];
    }
}
