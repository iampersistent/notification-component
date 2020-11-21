<?php
declare(strict_types=1);

namespace Notification\Command;

use Notification\Notification\GenericNotification;
use Notification\Recipient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SendTestEmailCommand extends Command
{
    protected static $defaultName = 'notification:send-test-email';
    /** @var \Notification\Notification\GenericNotification */
    private $notification;

    public function __construct(GenericNotification $notification)
    {
        $this->notification = $notification;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $recipient = (new Recipient())
            ->setEmail($email);

        $this->notification
            ->setSubject('Test Email')
            ->setBody('This is a test')
            ->addRecipient($recipient)
            ->send();

        return Command::SUCCESS;
    }
}
