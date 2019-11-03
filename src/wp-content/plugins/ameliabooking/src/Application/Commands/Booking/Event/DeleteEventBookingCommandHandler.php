<?php

namespace AmeliaBooking\Application\Commands\Booking\Event;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;

/**
 * Class DeleteEventBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Event
 */
class DeleteEventBookingCommandHandler extends CommandHandler
{
    /**
     * @param DeleteEventBookingCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public function handle(DeleteEventBookingCommand $command)
    {
        if ($this->getContainer()->get('logged.in.user') === null ||
            !$this->getContainer()->getPermissionsService()->currentUserCanDelete(Entities::EVENTS)) {
            throw new AccessDeniedException('You are not allowed to delete event bookings');
        }

        $result = new CommandResult();

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        /** @var EventApplicationService $eventApplicationService */
        $eventApplicationService = $this->container->get('application.booking.event.service');

        /** @var CustomerBooking $customerBooking */
        $customerBooking = $customerBookingRepository->getById((int)$command->getField('id'));

        if (!$customerBooking instanceof CustomerBooking) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete booking');

            return $result;
        }

        /** @var Collection $events */
        $events = $eventRepository->getByBookingIds([$customerBooking->getId()->getValue()]);

        /** @var Event $event */
        $event = $events->getItem($events->keys()[0]);

        $customerBookingRepository->beginTransaction();

        if (!$eventApplicationService->deleteEventBooking($customerBooking)) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete booking');

            return $result;
        }

        $customerBookingRepository->commit();

        $customerBooking->setStatus(new BookingStatus(BookingStatus::REJECTED));
        $event->setNotifyParticipants(false);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully deleted event booking');
        $result->setData([
            'type'                     => Entities::EVENT,
            Entities::EVENT            => $event->toArray(),
            Entities::BOOKING          => $customerBooking->toArray(),
            'appointmentStatusChanged' => false
        ]);

        return $result;
    }
}
