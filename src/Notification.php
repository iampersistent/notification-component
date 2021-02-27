<?php
declare(strict_types=1);

namespace Notification;

use Notification\Context\NotificationContext;
use Notification\Factory\Communication\CommunicationFactoryInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\Notification\Notification as Communication;

abstract class Notification
{
    /** @var \Notification\Context\NotificationContext */
    protected $context;
    /** @var string[] */
    private $channels = [];
    /** @var CommunicationFactoryInterface[] */
    private $communicationFactories;
    /** @var \Symfony\Component\Notifier\NotifierInterface */
    private $notifier;
    /** @var \Notification\RecipientChannels[] */
    private $recipientChannels = [];

    public function __construct(
        Notifier $notifier,
        NotificationContext $context,
        array $channels,
        array $communicationFactories
    ) {
        $this->channels = $channels;
        $this->communicationFactories = $communicationFactories;
        $this->context = $context;
        $this->notifier = $notifier;
    }

    public function getContext(): NotificationContext
    {
        return $this->context;
    }

    public function addRecipientChannel(RecipientChannels $recipientChannels): self
    {
        $this->recipientChannels[] = $recipientChannels;

        return $this;
    }

    public function setRecipientChannels(array $recipientChannels): self
    {
        $this->recipientChannels = $recipientChannels;

        return $this;
    }

    public function send()
    {
        $channels = $this->getChannels();

        foreach ($this->getAllowedChannels() as $channel => $transport) {
            if (!empty($channels[$channel])) {
                $communication = $this->createCommunication($channel);
                foreach ($channels[$channel] as $recipient) {
                    $this->notifier->send($communication, $recipient);
                }
            }
        }
    }

    protected function getAllowedChannels(): array
    {
        return $this->channels;
    }

    private function createCommunication($channel): Communication
    {
        $factory = $this->communicationFactories[$channel];

        return $factory->create($this->context, $channel);
    }

    private function getChannels(): array
    {
        $channels = [];
        foreach ($this->getAllowedChannels() as $channel => $transport) {
            $channels[$channel] = [];
            foreach ($this->recipientChannels as $recipientChannel) {
                $channels[$channel] =
                    array_merge($channels[$channel], $recipientChannel->getForChannel($channel));
            }
        }

        return $channels;
    }
}
