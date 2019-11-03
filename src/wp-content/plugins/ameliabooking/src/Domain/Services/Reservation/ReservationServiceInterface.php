<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Services\Reservation;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Common\Exceptions\BookingCancellationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\AbstractBookable;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\AbstractBooking;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;

/**
 * Interface ReservationServiceInterface
 *
 * @package AmeliaBooking\Domain\Services\Reservation
 */
interface ReservationServiceInterface
{
    /**
     * @param int    $bookingId
     * @param string $requestedStatus
     * @param string $token
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws BookingCancellationException
     */
    public function updateStatus($bookingId, $requestedStatus, $token);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param int       $bookingId
     * @param array     $paymentData
     * @param float     $amount
     * @param \DateTime $dateTime
     *
     * @return boolean
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function addPayment($bookingId, $paymentData, $amount, $dateTime);

    /**
     * @param array $appointmentData
     * @param bool  $inspectTimeSlot
     * @param bool  $save
     *
     * @return array|null
     *
     * @throws \AmeliaBooking\Domain\Common\Exceptions\BookingUnavailableException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\CustomerBookedException
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Exception
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function book($appointmentData, $inspectTimeSlot, $save);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param CommandResult $result
     * @param array         $appointmentData
     * @param bool          $inspectTimeSlot
     * @param bool          $inspectCoupon
     * @param bool          $save
     *
     * @return array|null
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Exception
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function process($result, $appointmentData, $inspectTimeSlot, $inspectCoupon, $save);

    /**
     * @param CustomerBooking  $booking
     * @param AbstractBookable $bookable
     *
     * @return float
     *
     * @throws InvalidArgumentException
     */
    public function getPaymentAmount($booking, $bookable);

    /**
     * @param AbstractBooking  $reservation
     * @param CustomerBooking  $booking
     * @param AbstractBookable $bookable
     *
     * @return array
     */
    public function getBookingPeriods($reservation, $booking, $bookable);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param array $data
     *
     * @return AbstractBookable
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getBookable($data);

    /**
     * @param Service|Event $bookable
     *
     * @return boolean
     */
    public function isAggregatedPrice($bookable);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param CustomerBooking  $booking
     * @param AbstractBookable $bookable
     * @param AbstractBooking  $reservation
     * @param string           $paymentGateway
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function getInfo($bookable, $booking, $reservation, $paymentGateway);

    /**
     * @param int $id
     *
     * @return Appointment|Event
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws InvalidArgumentException
     */
    public function getReservationById($id);

    /**
     * @param int $id
     *
     * @return Appointment|Event
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws InvalidArgumentException
     */
    public function getReservationByBookingId($id);

    /**
     * @param Appointment|Event $reservation
     * @param Service|Event $bookable
     * @param \DateTime $dateTime
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function isBookable($reservation, $bookable, $dateTime);

    /**
     * @param CustomerBooking $booking
     * @param string          $token
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws AccessDeniedException
     */
    function inspectToken($booking, $token);

    /**
     * @param \DateTime $bookingStart
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws BookingCancellationException
     */
    function inspectMinimumCancellationTime($bookingStart);
}
