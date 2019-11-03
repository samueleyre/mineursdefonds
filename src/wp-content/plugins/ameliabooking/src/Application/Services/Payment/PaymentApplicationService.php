<?php

namespace AmeliaBooking\Application\Services\Payment;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;

/**
 * Class PaymentApplicationService
 *
 * @package AmeliaBooking\Application\Services\Payment
 */
class PaymentApplicationService
{

    private $container;

    /**
     * PaymentApplicationService constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $params
     * @param int   $itemsPerPage
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public function getPaymentsData($params, $itemsPerPage)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        $paymentsData = $paymentRepository->getFiltered($params, $itemsPerPage);

        $bookingIds = [];

        foreach ($paymentsData as $payment) {
            if (!$payment['appointmentId']) {
                $bookingIds[] = $payment['customerBookingId'];
            }
        }

        /** @var Collection $events */
        $events = $eventRepository->getByBookingIds($bookingIds);

        /** @var Event $event */
        foreach ($events->getItems() as $event) {
            /** @var CustomerBooking $booking */
            foreach ($event->getBookings()->getItems() as $booking) {
                if (array_key_exists($booking->getId()->getValue(), $paymentsData)) {
                    $paymentsData[$booking->getId()->getValue()]['bookingStart'] =
                        $event->getPeriods()->getItem(0)->getPeriodStart()->getValue()->format('Y-m-d H:i:s');

                    /** @var Provider $provider */
                    foreach ($event->getProviders()->getItems() as $provider) {
                        $paymentsData[$booking->getId()->getValue()]['providers'][] = [
                            'id' => $provider->getId()->getValue(),
                            'fullName' => $provider->getFullName(),
                            'email' => $provider->getEmail()->getValue(),
                        ];
                    }

                    $paymentsData[$booking->getId()->getValue()]['eventId'] = $event->getId()->getValue();
                    $paymentsData[$booking->getId()->getValue()]['name'] = $event->getName()->getValue();
                }
            }
        }

        return $paymentsData;
    }
}
