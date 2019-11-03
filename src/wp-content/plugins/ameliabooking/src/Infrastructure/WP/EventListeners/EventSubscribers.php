<?php
/**
 * Subscribe to domain events
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners;

use AmeliaBooking\Domain\Events\DomainEventBus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentEventsListener;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event\EventEventsListener;
use AmeliaBooking\Infrastructure\WP\EventListeners\User\UserEventsListener;

/**
 * Class EventSubscribers
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners
 */
class EventSubscribers
{
    /**
     * Subscribe WP infrastructure to domain events
     *
     * @param DomainEventBus $eventBus
     * @param Container      $container
     */
    public static function subscribe($eventBus, $container)
    {
        $userListener = new UserEventsListener();
        $eventBus->addListener('user.added', $userListener);
        $eventBus->addListener('user.deleted', $userListener);

        $appointmentListener = new AppointmentEventsListener($container);
        $eventBus->addListener('AppointmentAdded', $appointmentListener);
        $eventBus->addListener('AppointmentDeleted', $appointmentListener);
        $eventBus->addListener('AppointmentEdited', $appointmentListener);
        $eventBus->addListener('AppointmentStatusUpdated', $appointmentListener);
        $eventBus->addListener('AppointmentTimeUpdated', $appointmentListener);
        $eventBus->addListener('BookingAdded', $appointmentListener);
        $eventBus->addListener('BookingCanceled', $appointmentListener);

        $eventListener = new EventEventsListener($container);
        $eventBus->addListener('EventStatusUpdated', $eventListener);
        $eventBus->addListener('EventEdited', $eventListener);
    }
}
