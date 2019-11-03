<?php
/**
 * Handle WP part of appointment-related events
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Infrastructure\Common\Container;
use League\Event\ListenerInterface;
use League\Event\EventInterface;

/**
 * Class AppointmentEventsListener
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentEventsListener implements ListenerInterface
{
    /** @var Container */
    private $container;

    /**
     * AppointmentEventsListener constructor.
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Check if provided argument is the listener
     *
     * @param mixed $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $listener === $this;
    }

    /**
     * @param EventInterface     $event
     * @param CommandResult|null $param
     *
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(EventInterface $event, $param = null)
    {
        // Handling the events
        if ($param->getResult() !== 'error') {
            switch ($event->getName()) {
                case 'AppointmentAdded':
                    AppointmentAddedEventHandler::handle($param, $this->container);
                    break;
                case 'AppointmentDeleted':
                    if (in_array($param->getData()[\AmeliaBooking\Domain\Entity\Entities::APPOINTMENT]["status"], ["canceled", "rejected", "deleted"])) {return;}

                    AppointmentDeletedEventHandler::handle($param, $this->container);
                    break;
                case 'AppointmentEdited':
                    AppointmentEditedEventHandler::handle($param, $this->container);
                    break;
                case 'AppointmentStatusUpdated':
                    if (in_array($param->getData()[\AmeliaBooking\Domain\Entity\Entities::APPOINTMENT]["status"], ["canceled", "rejected", "deleted"])) {return;}

                    AppointmentStatusUpdatedEventHandler::handle($param, $this->container);
                    break;
                case 'AppointmentTimeUpdated':
                    AppointmentTimeUpdatedEventHandler::handle($param, $this->container);
                    break;
                case 'BookingAdded':
                    BookingAddedEventHandler::handle($param, $this->container);
                    break;
                case 'BookingCanceled':
                    if (in_array($param->getData()[\AmeliaBooking\Domain\Entity\Entities::APPOINTMENT]["status"], ["canceled", "rejected", "deleted"])) {return;}

                    // BookingCanceledEventHandler::handle($param, $this->container);
                    break;
            }
        }
    }
}
