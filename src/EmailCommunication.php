<?php
declare(strict_types=1);

namespace Notification;

use Notification\Context\EmailContext;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification as Communication;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

final class EmailCommunication extends Communication implements EmailNotificationInterface
{
    /** @var \Symfony\Component\Notifier\Message\EmailMessage */
    private $message;

    public function __construct(EmailContext $emailContext, array $channels = [])
    {
        $this->createEmailMessage($emailContext);

        parent::__construct($emailContext->getSubject(), $channels);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        return new EmailMessage($this->message);
    }

    private function createEmailMessage(EmailContext $emailContext)
    {
        $email = (new TemplatedEmail())
            ->context($emailContext->getBodyContext())
            ->subject($emailContext->getSubject())
        ;
        if ($template = $emailContext->getHtmlTemplate()) {
            $email->htmlTemplate("$template.html.twig");
        }
        if ($template = $emailContext->getTextTemplate()) {
            $email->textTemplate("$template.text.twig");
        }
        foreach ($emailContext->getBcc() as $bcc) {
            $address = Address::create($bcc->getEmail());
            $email->addBcc($address);
        }
        foreach ($emailContext->getFrom() as $from) {
            $address = Address::create($from->getEmail());
            $email->addFrom($address);
        }
        foreach ($emailContext->getReplyTo() as $replyTo) {
            $address = Address::create($replyTo->getEmail());
            $email->addReplyTo($address);
        }
        foreach ($emailContext->getTo() as $to) {
            $address = Address::create($to->getEmail());
            $email->addTo($address);
        }

        $this->message = $email;
    }
}
