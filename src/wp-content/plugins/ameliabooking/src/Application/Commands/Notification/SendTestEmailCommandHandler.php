<?php

namespace AmeliaBooking\Application\Commands\Notification;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Services\Notification\MailgunService;
use AmeliaBooking\Infrastructure\Services\Notification\PHPMailService;
use AmeliaBooking\Infrastructure\Services\Notification\SMTPService;

/**
 * Class SendTestEmailCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Notification
 */
class SendTestEmailCommandHandler extends CommandHandler
{
    public $mandatoryFields = [
        'notificationTemplate',
        'recipientEmail'
    ];

    /**
     * @param SendTestEmailCommand $command
     *
     * @return CommandResult
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function handle(SendTestEmailCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::NOTIFICATIONS)) {
            throw new AccessDeniedException('You are not allowed to send test email');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var PHPMailService|SMTPService|MailgunService $mailService */
        $mailService = $this->getContainer()->get('infrastructure.mail.service');
        /** @var EmailNotificationService $notificationService */
        $notificationService = $this->getContainer()->get('application.emailNotification.service');
        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->getContainer()->get("application.placeholder.{$command->getField('type')}.service");

        $notification = $notificationService->getByNameAndType($command->getField('notificationTemplate'), 'email');
        $dummyData = $placeholderService->getPlaceholdersDummyData();

        $subject = $placeholderService->applyPlaceholders(
            $notification->getSubject()->getValue(),
            $dummyData
        );

        $content = $placeholderService->applyPlaceholders(
            $notification->getContent()->getValue(),
            $dummyData
        );
        /** @var SettingsService $bccEmail */
        $bccEmail = $this->getContainer()->get('domain.settings.service')->getSetting('notifications','bccEmail');
        ($bccEmail !== '') ? $bcc = $bccEmail : $bcc = false;

        $mailService->send($command->getField('recipientEmail'), $subject, $content, $bcc);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Test email successfully sent');

        return $result;
    }
}
