<?php
declare(strict_types=1);

namespace Notification;

use Notification\Context\EmailContext;
use Notification\Context\NotificationContext;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\Notification\Notification as Communication;

abstract class Notification
{
    /** @var \Notification\Recipient[] */
    protected $bcc = [];
    /** @var string */
    protected $body;
    /** @var [] */
    protected $communicationFactories;
    /** @var \Notification\Recipient[] */
    protected $channelRecipients = [];
    /** @var string[] */
    protected array $channels = [];
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

    public function __construct(
        Notifier $notifier,
        BodyRenderer $renderer,
        array $channels,
        array $communicationFactories
    ) {
        $this->channels = $channels;
        $this->communicationFactories = $communicationFactories;
        $this->notifier = $notifier;
        $this->renderer = $renderer;
    }

    public function dispatch(NotificationContext $context, array $recipientChannels)
    {
        $this->sortRecipientChannels($recipientChannels);
        $this->handleContext($context);

        $this->send();
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
        foreach ($this->getAllowedChannels() as $channel) {
            if (!empty($this->channelRecipients[$channel])) {
                $communication = $this->createCommunication($channel);
                foreach ($this->channelRecipients[$channel] as $recipient) {
                    $this->notifier->send($communication, $recipient);
                }
            }
        }
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    abstract protected function getEmailHtmlTemplate(): ?string;
    abstract protected function getEmailTextTemplate(): ?string;
    abstract protected function handleContext(NotificationContext $notificationContext);

    protected function getAllowedChannels(): array
    {
        return $this->channels;
    }

    protected function setSubject(string $subject): Notification
    {
        $this->subject = $subject;
        $this->context['_subject'] = $subject;

        return $this;
    }

    private function addRecipient(Recipient $recipient, array $channels = null): Notification
    {
        $channelKey = $this->getRecipientChannels($recipient, $channels);
        $this->channelRecipients[$channelKey][] = $recipient;
        $this->recipients[] = $recipient;

        return $this;
    }

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

    private function createCommunication(string $channel): Communication
    {
        $emailContext = $this->createEmailContext();

        $communication = (new EmailCommunication($emailContext, [$channel]));
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

    /**
     * @param \Notification\RecipientChannels[] $recipientChannels
     */
    private function sortRecipientChannels(array $recipientChannels)
    {
        foreach ($recipientChannels as $recipientChannel) {
            foreach ($this->getAllowedChannels() as $channel) {
                $this->channelRecipients[$channel] =
                    array_merge($this->channelRecipients, $recipientChannel->getForChannel($channel));
            }
        }
    }
}
