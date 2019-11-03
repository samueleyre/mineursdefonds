<?php

namespace AmeliaBooking\Application\Services\Reservation;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Coupon\CouponApplicationService;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\BookingCancellationException;
use AmeliaBooking\Domain\Common\Exceptions\BookingUnavailableException;
use AmeliaBooking\Domain\Common\Exceptions\CouponInvalidException;
use AmeliaBooking\Domain\Common\Exceptions\CouponUnknownException;
use AmeliaBooking\Domain\Common\Exceptions\CustomerBookedException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBookingExtra;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Factory\Payment\PaymentFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\PaymentStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentType;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;

/**
 * Class AbstractReservationService
 *
 * @package AmeliaBooking\Application\Services\Reservation
 */
abstract class AbstractReservationService implements ReservationServiceInterface
{
    protected $container;

    /**
     * AbstractReservationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param CommandResult $result
     * @param array         $appointmentData
     * @param bool          $inspectTimeSlot
     * @param bool          $inspectCoupon
     * @param bool          $save
     *
     * @return array|null
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Exception
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function process($result, $appointmentData, $inspectTimeSlot, $inspectCoupon, $save)
    {
        /** @var CouponApplicationService $couponAS */
        $couponAS = $this->container->get('application.coupon.service');
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        if (isset($appointmentData['payment']) &&
            isset($appointmentData['payment']['gateway']) === PaymentType::ON_SITE &&
            !$settingsService->getSetting('payments', 'onSite')) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setData(['paymentError' => true]);

            return null;
        }

        $appointmentData['bookings'][0]['info'] = json_encode([
            'firstName' => $appointmentData['bookings'][0]['customer']['firstName'],
            'lastName'  => $appointmentData['bookings'][0]['customer']['lastName'],
            'phone'     => $appointmentData['bookings'][0]['customer']['phone'],
        ]);

        /** @var Customer $user */
        $user = null;

        $newUserId = null;

        // Create a new user if doesn't exists. For adding appointment from the front-end.
        if (!$appointmentData['bookings'][0]['customerId'] && !$appointmentData['bookings'][0]['customer']['id']) {
            /** @var CustomerApplicationService $customerAS */
            $customerAS = $this->container->get('application.user.customer.service');

            /** @var UserRepository $userRepository */
            $userRepository = $this->container->get('domain.users.repository');

            $user = $customerAS->getNewOrExistingCustomer($appointmentData['bookings'][0]['customer'], $result);

            if ($result->getResult() === CommandResult::RESULT_ERROR) {
                return null;
            }

            if ($save && !$user->getId()) {
                if (!($newUserId = $userRepository->add($user))) {
                    $result->setResult(CommandResult::RESULT_ERROR);
                    $result->setData(['emailError' => true]);

                    return null;
                }

                $user->setId(new Id($newUserId));
            }

            if ($user->getId()) {
                $appointmentData['bookings'][0]['customerId'] = $user->getId()->getValue();
                $appointmentData['bookings'][0]['customer']['id'] = $user->getId()->getValue();
            }
        }

        /** @var Coupon $coupon */
        $coupon = null;

        // Inspect if coupon is existing and valid if sent from the front-end.
        if ($appointmentData['couponCode']) {
            try {
                $coupon = $couponAS->processCoupon(
                    $appointmentData['couponCode'],
                    $appointmentData['serviceId'],
                    ($user && $user->getId()) ?
                        $user->getId()->getValue() : $appointmentData['bookings'][0]['customer']['id'],
                    $inspectCoupon
                );
            } catch (CouponUnknownException $e) {
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setMessage($e->getMessage());
                $result->setData([
                    'couponUnknown' => true
                ]);

                return null;
            } catch (CouponInvalidException $e) {
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setMessage($e->getMessage());
                $result->setData([
                    'couponInvalid' => true
                ]);

                return null;
            }

            if ($coupon) {
                $appointmentData['bookings'][0]['coupon'] = $coupon->toArray();
            }
        }

        try {
            $bookingData = $this->book($appointmentData, $inspectTimeSlot, $save);
        } catch (CustomerBookedException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['customer_already_booked']);
            $result->setData([
                'customerAlreadyBooked' => true
            ]);

            return null;
        } catch (BookingUnavailableException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['time_slot_unavailable']);
            $result->setData([
                'timeSlotUnavailable' => true
            ]);

            return null;
        }

        $bookingData['isNewUser'] = $newUserId !== null;

        return $bookingData;
    }

    /**
     * @param CustomerBooking  $booking
     * @param Service|Event $bookable
     *
     * @return float
     *
     * @throws InvalidArgumentException
     */
    public function getPaymentAmount($booking, $bookable)
    {
        $price = (float)$bookable->getPrice()->getValue() *
            ($this->isAggregatedPrice($bookable) ? $booking->getPersons()->getValue() : 1);

        foreach ((array)$booking->getExtras()->keys() as $extraKey) {
            /** @var CustomerBookingExtra $customerBookingExtra */
            $customerBookingExtra = $booking->getExtras()->getItem($extraKey);

            $extraId = $customerBookingExtra->getExtraId()->getValue();

            /** @var Extra $extra */
            $extra = $bookable->getExtras()->getItem($extraId);

            $price += (float)$extra->getPrice()->getValue() *
                ($this->isAggregatedPrice($bookable) ? $booking->getPersons()->getValue() : 1) *
                $customerBookingExtra->getQuantity()->getValue();
        }

        if ($booking->getCoupon()) {
            $price -= $price / 100 *
                ($booking->getCoupon()->getDiscount()->getValue() ?: 0) +
                ($booking->getCoupon()->getDeduction()->getValue() ?: 0);
        }

        return $price;
    }

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
    function addPayment($bookingId, $paymentData, $amount, $dateTime)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        $paymentStatus = PaymentStatus::PENDING;

        switch ($paymentData['gateway']) {
            case (PaymentType::WC):
                $paymentStatus = $paymentData['status'];
                break;
            case (PaymentType::PAY_PAL):
                $paymentStatus = PaymentStatus::PAID;
                break;
            case (PaymentType::STRIPE):
                $paymentStatus = PaymentStatus::PAID;
                break;
        }

        $paymentAmount = $paymentData['gateway'] === PaymentType::ON_SITE ? 0 : $amount;

        $payment = PaymentFactory::create([
            'customerBookingId' => $bookingId,
            'amount'            => $paymentAmount,
            'status'            => $paymentStatus,
            'gateway'           => $paymentData['gateway'],
            'dateTime'          => ($paymentData['gateway'] === PaymentType::ON_SITE) ?
                $dateTime->format('Y-m-d H:i:s') : DateTimeService::getNowDateTimeObject()->format('Y-m-d H:i:s'),
            'gatewayTitle'      => isset($paymentData['gatewayTitle']) ? $paymentData['gatewayTitle'] : ''
        ]);

        if (!$payment instanceof Payment) {
            throw new InvalidArgumentException('Unknown type');
        }

        return $paymentRepository->add($payment);
    }

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
    function inspectToken($booking, $token)
    {
        $loggedInUser = $this->container->get('logged.in.user');

        if (($token !== null && $token !== $booking->getToken()->getValue()) ||
            (
                $token === null && $loggedInUser &&
                $loggedInUser->getId() !== null &&
                $loggedInUser->getId()->getValue() !== $booking->getCustomerId()->getValue()
            )
        ) {
            throw new AccessDeniedException('You are not allowed to update booking status');
        }

        return true;
    }

    /**
     * @param \DateTime $bookingStart
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws BookingCancellationException
     */
    function inspectMinimumCancellationTime($bookingStart)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $minimumCancelTime = $settingsService->getCategorySettings('general')['minimumTimeRequirementPriorToCanceling'];

        if (DateTimeService::getNowDateTimeObject() >=
            DateTimeService::getCustomDateTimeObject(
                $bookingStart->format('Y-m-d H:i:s'))->modify("-{$minimumCancelTime} second")
        ) {
            throw new BookingCancellationException('You are not allowed to update booking status');
        }

        return true;
    }
}
