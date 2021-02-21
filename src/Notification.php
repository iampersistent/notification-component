<?php
declare(strict_types=1);

namespace Notification;

use Notification\Context\EmailContext;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification as Communication;

abstract class Notification
{
    /** @var \Notification\Recipient[] */
    protected $bcc = [];
    /** @var string */
    protected $body;
    /** @var \Notification\Recipient[][] */
    protected $channelRecipients = [];
    /** @var string[] */
    protected $channels = [];
    /** @var array */
    protected $context = [];
    /** @var \Notification\Recipient[] */
    protected $from = [];
    /** @var \Symfony\Component\Notifier\NotifierInterface */
    protected $notifier;
    /** @var \Notification\Recipient[] */
    protected $recipients = [];
    /** @var \Symfony\Bridge\Twig\Mime\BodyRenderer */
    protected $renderer;
    /** @var \Notification\Recipient[] */
    protected $replyTo = [];
    /** @var string */
    protected $subject;

    public function __construct(Notifier $notifier, BodyRenderer $renderer, array $channels)
    {
        $this->channels = $channels;
        $this->notifier = $notifier;
        $this->renderer = $renderer;
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

    public function getContext(): array
    {
        return $this->context;
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

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Notification
    {
        $this->subject = $subject;
        $this->context['_subject'] = $subject;

        return $this;
    }

    abstract protected function getEmailHtmlTemplate(): ?string;

    abstract protected function getEmailTextTemplate(): ?string;

    private function createEmailContext(): EmailContext
    {
        return (new EmailContext())
            ->setBcc($this->bcc)
            ->setBodyContext($this->context)
            ->setFrom($this->from)
            ->setHtmlTemplate($this->getEmailHtmlTemplate())
            ->setReplyTo($this->replyTo)
            ->setSubject($this->subject)
            ->setTextTemplate($this->getEmailTextTemplate());
    }

    private function createCommunication(array $channels): Communication
    {
        $emailContext = $this->createEmailContext();

        $communication = (new EmailCommunication($emailContext, $channels));
        $this->renderEmail($communication);

        return $communication;
    }

    private function getRecipientChannels(Recipient $recipient, array $channels = null): string
    {
        if (empty($channels)) {
            $channels = $recipient->getChannels();
        }

        return implode(',', $channels);
    }

    private function renderEmail(EmailCommunication $communication)
    {
        $email = $communication->getEmail();

        $this->renderer->render($email);
    }
}
