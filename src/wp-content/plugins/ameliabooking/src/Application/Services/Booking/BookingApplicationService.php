<?php

namespace AmeliaBooking\Application\Services\Booking;

use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;

/**
 * Class BookingApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class BookingApplicationService
{
    private $container;

    /**
     * AppointmentApplicationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $appointment
     * @param array $oldAppointment
     *
     * @return array
     */
    public function getBookingsWithChangedStatus($appointment, $oldAppointment)
    {
        $bookings = [];

        foreach ((array)$appointment['bookings'] as $booking) {
            $oldBookingKey = array_search($booking['id'], array_column($oldAppointment['bookings'], 'id'), true);

            if ($oldBookingKey === false ||
                $booking['status'] !== $oldAppointment['bookings'][$oldBookingKey]['status']
            ) {
                $bookings[] = $booking;
            }
        }

        foreach ((array)$oldAppointment['bookings'] as $oldBooking) {
            $newBookingKey = array_search($oldBooking['id'], array_column($appointment['bookings'], 'id'), true);

            if (($newBookingKey === false) && $oldBooking['status'] !== BookingStatus::REJECTED) {
                $oldBooking['status'] = BookingStatus::REJECTED;
                $bookings[] = $oldBooking;
            }
        }

        return $bookings;
    }

    /**
     * @param $bookingsArray
     *
     * @return array
     */
    public function filterApprovedBookings($bookingsArray)
    {
        return array_intersect_key(
            $bookingsArray,
            array_flip(array_keys(array_column($bookingsArray, 'status'), 'approved'))
        );
    }

    /**
     * @param array $bookingsArray
     * @param array $statuses
     *
     * @return mixed
     */
    public function removeBookingsByStatuses($bookingsArray, $statuses)
    {
        foreach ($statuses as $status) {
            foreach ($bookingsArray as $bookingKey => $bookingArray) {
                if ($bookingArray['status'] === $status) {
                    unset($bookingsArray[$bookingKey]);
                }
            }
        }

        return $bookingsArray;
    }

    /**
     * @param array $appointmentData
     *
     * @return array|null
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getAppointmentData($appointmentData)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        // Convert UTC slot to slot in TimeZone based on Settings
        if ($appointmentData['bookings'][0]['utcOffset'] !== null &&
            $settingsService->getSetting('general', 'showClientTimeZone')) {

            $appointmentData['bookingStart'] = DateTimeService::getCustomDateTimeFromUtc(
                $appointmentData['bookingStart']
            );
        }

        return $appointmentData;
    }
}
