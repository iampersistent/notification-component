<?php
declare(strict_types=1);

namespace Notification;

use Symfony\Component\Notifier\NotifierInterface;

abstract class Notification
{
    /** @var string */
    protected $body;
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

    public function addRecipient(Recipient $recipient): Notification
    {
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

    }

    public function setSubject(string $subject): Notification
    {
        $this->subject = $subject;
        $this->context['_subject'] = $subject;

        return $this;
    }

    abstract protected function getEmailTemplate(): ?string;
}
