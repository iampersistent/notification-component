<?php
declare(strict_types=1);

namespace Notification;

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification as Communication;

abstract class Notification
{
    /** @var string */
    protected $body;
    /** @var \Notification\Recipient[][] */
    protected $channelRecipients = [];
    /** @var string[] */
    protected $channels = [];
    /** @var array */
    protected $context = [];
    /** @var \Symfony\Component\Notifier\NotifierInterface */
    protected $notifier;
    /** @var \Notification\Recipient[] */
    protected $recipients = [];
    /** @var string */
    protected $subject;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function addRecipient(Recipient $recipient, array $channels = null): Notification
    {
        $channelKey = $this->getRecipientChannels($recipient, $channels);
        $this->channelRecipients[$channelKey][] = $recipient;
        $this->recipients[] = $recipient;

        return $this;
    }

    public function setBody(string $body): Notification
    {
        $this->body = $body;
        $this->context['_body'] = $body;

        return $this;
    }

    public function send()
    {
        if (!empty($this->channels)) {
            // todo: what happens if recipient doesn't allow channel?
            $communication = $this->createCommunication($this->channels);
            $this->notifier->send($communication, ...$this->recipients);

            return;
        }
        foreach ($this->channelRecipients as $channelKey => $recipients) {
            $channels = explode(',', $channelKey);
            $communication = $this->createCommunication($channels);
            $this->notifier->send($communication, ...$recipients);
        }
    }

    public function setSubject(string $subject): Notification
    {
        $this->subject = $subject;
        $this->context['_subject'] = $subject;

        return $this;
    }

    abstract protected function getEmailTemplate(): ?string;

    private function createCommunication(array $channels): Communication
    {
        return (new Communication($this->subject, $channels));
    }

    private function getRecipientChannels(Recipient $recipient, array $channels = null): string
    {
        if (empty($channels)) {
            $channels = $recipient->getChannels();
        }

        return implode(',', $channels);
    }
}
