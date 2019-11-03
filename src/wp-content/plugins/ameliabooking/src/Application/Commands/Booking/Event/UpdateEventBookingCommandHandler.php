<?php

namespace AmeliaBooking\Application\Commands\Booking\Event;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;

/**
 * Class UpdateEventBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Event
 */
class UpdateEventBookingCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'status'
    ];

    /**
     * @param UpdateEventBookingCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     */
    public function handle(UpdateEventBookingCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWriteStatus(Entities::EVENT)) {
            throw new AccessDeniedException('You are not allowed to update booking');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        /** @var CustomerBooking $customerBooking */
        $customerBooking = $customerBookingRepository->getById((int)$command->getField('id'));

        /** @var Collection $events */
        $events = $eventRepository->getByBookingIds([$customerBooking->getId()->getValue()]);

        /** @var Event $event */
        $event = $events->getItem($events->keys()[0]);

        if (!$customerBooking instanceof CustomerBooking) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update booking');

            return $result;
        }

        $customerBooking->setStatus(new BookingStatus($command->getField('status')));

        $customerBookingRepository->update($customerBooking->getId()->getValue(), $customerBooking);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully booking');
        $result->setData([
            'type'                     => Entities::EVENT,
            Entities::EVENT            => $event->toArray(),
            Entities::BOOKING          => $customerBooking->toArray(),
            'appointmentStatusChanged' => false
        ]);

        return $result;
    }
}
