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
    /** @var string[] */
    protected $channels = [];
    /** @var \Notification\Context\NotificationContext */
    protected $context;
    /** @var \Notification\Recipient[] */
    protected $from = [];
    /** @var \Symfony\Component\Notifier\NotifierInterface */
    protected $notifier;
    /** @var \Symfony\Bridge\Twig\Mime\BodyRenderer */
    protected $renderer;
    /** @var \Notification\Recipient[] */
    protected $replyTo = [];
    /** @var string */
    protected $subject;

    /** @var \Notification\RecipientChannels[] */
    private $recipientChannels = [];

    public function __construct(
        Notifier $notifier,
        BodyRenderer $renderer,
        array $channels,
        array $communicationFactories
    ) {
        $this->channels = $channels;
        $this->communicationFactories = $communicationFactories;
        $this->context = new NotificationContext();
        $this->notifier = $notifier;
        $this->renderer = $renderer;
    }

    public function setBody(string $body): Notification
    {
        $this->body = $body;
        $this->context['_body'] = $body;

        return $this;
    }

    public function getContext(): NotificationContext
    {
        return $this->context;
    }

    public function setRecipientChannels(array $recipientChannels)
    {
        $this->recipientChannels = $recipientChannels;

        return $this;
    }

    public function send()
    {
        $channels = $this->getChannels();

        foreach ($this->getAllowedChannels() as $channel) {
            if (!empty($channels[$channel])) {
                $communication = $this->createCommunication($channel);
                foreach ($channels[$channel] as $recipient) {
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

    private function createEmailContext(): EmailContext
    {
        return (new EmailContext())
            ->setBcc($this->bcc)
            ->setBodyContext($this->context->toArray())
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

    private function renderEmail(EmailCommunication $communication)
    {
        $email = $communication->getEmail();

        $this->renderer->render($email);
    }

    private function getChannels(): array
    {
        $channels = [];
        foreach ($this->recipientChannels as $recipientChannel) {
            foreach ($this->getAllowedChannels() as $channel) {
                $channels[$channel] =
                    array_merge($channels, $recipientChannel->getForChannel($channel));
            }
        }

        return $channels;
    }
}
