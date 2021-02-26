<?php
declare(strict_types=1);

namespace Notification\Factory\Communication;

use Notification\Context\NotificationContext;
use Notification\EmailCommunication;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\Notification\Notification as Communication;

final class EmailCommunicationFactory implements CommunicationFactoryInterface
{
    /** @var \Symfony\Bridge\Twig\Mime\BodyRenderer */
    private $renderer;

    public function __construct(
        BodyRenderer $renderer
    ) {
        $this->renderer = $renderer;
    }

    public function create(NotificationContext $context, string $channel): Communication
    {
        /** @var \Notification\Context\EmailContext $emailContext */
        $emailContext = $context->getMeta('email');
        $emailContext->setBodyContext($context->toArray());

        $communication = (new EmailCommunication($emailContext, [$channel]));
        $this->renderEmail($communication);

        return $communication;
    }

    private function renderEmail(EmailCommunication $communication)
    {
        $email = $communication->getEmail();

        $this->renderer->render($email);
    }
}
