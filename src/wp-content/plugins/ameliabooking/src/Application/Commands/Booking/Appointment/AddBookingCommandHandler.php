<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\AbstractBookable;
use AmeliaBooking\Domain\Entity\Booking\AbstractBooking;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Services\Payment\CurrencyService;
use AmeliaBooking\Infrastructure\Services\Payment\PayPalService;
use AmeliaBooking\Infrastructure\Services\Payment\StripeService;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;

/**
 * Class AddBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class AddBookingCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'bookings',
        'couponCode',
        'payment'
    ];

    /**
     * @param AddBookingCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public function handle(AddBookingCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $type = $command->getField('type') ?: Entities::APPOINTMENT;

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($type);

        $customerBookingRepository->beginTransaction();

        $appointmentData = $bookingAS->getAppointmentData($command->getFields());

        try {
            $bookingData = $reservationService->process($result, $appointmentData, true, true, true);
        } catch (QueryExecutionException $e) {
            $customerBookingRepository->rollback();
            throw $e;
        }

        if ($result->getResult() === CommandResult::RESULT_ERROR) {
            $customerBookingRepository->rollback();
            return $result;
        }

        /** @var AbstractBooking $reservation */
        $reservation = $bookingData[$type];

        /** @var CustomerBooking $booking */
        $booking = $bookingData['booking'];

        /** @var AbstractBookable $bookable */
        $bookable = $bookingData['bookable'];

        $paymentData = $command->getField('payment');

        $paymentAmount = $reservationService->getPaymentAmount($booking, $bookable);

        switch ($paymentData['gateway']) {
            case ('payPal'):
                /** @var PayPalService $paymentService */
                $paymentService = $this->container->get('infrastructure.payment.payPal.service');

                $response = $paymentService->complete([
                    'transactionReference' => $paymentData['data']['transactionReference'],
                    'PayerID'              => $paymentData['data']['PayerId'],
                    'amount'               => $paymentAmount,
                ]);

                if (!$response->isSuccessful()) {
                    $result->setResult(CommandResult::RESULT_ERROR);
                    $result->setMessage(FrontendStrings::getCommonStrings()['payment_error']);
                    $result->setData([
                        'paymentSuccessful' => false
                    ]);

                    $customerBookingRepository->rollback();

                    return $result;
                }

                break;

            case ('stripe'):
                /** @var StripeService $paymentService */
                $paymentService = $this->container->get('infrastructure.payment.stripe.service');

                /** @var CurrencyService $currencyService */
                $currencyService = $this->container->get('infrastructure.payment.currency.service');

                try {
                    $response = $paymentService->execute([
                        'paymentMethodId' => isset($paymentData['data']['paymentMethodId']) ?
                            $paymentData['data']['paymentMethodId'] : null,
                        'paymentIntentId' => isset($paymentData['data']['paymentIntentId']) ?
                            $paymentData['data']['paymentIntentId'] : null,
                        'amount'          => $currencyService->getAmountInFractionalUnit(new Price($paymentAmount)),
                    ]);
                } catch (\Exception $e) {
                    $customerBookingRepository->rollback();

                    $result->setResult(CommandResult::RESULT_ERROR);
                    $result->setMessage(FrontendStrings::getCommonStrings()['payment_error']);
                    $result->setData([
                        'paymentSuccessful' => false
                    ]);

                    return $result;
                }

                if (isset($response['requiresAction'])) {
                    $customerBookingRepository->rollback();

                    $result->setData([
                        'paymentIntentClientSecret' => $response['paymentIntentClientSecret'],
                        'requiresAction'            => $response['requiresAction']
                    ]);

                    return $result;
                }

                if (empty($response['paymentSuccessful'])) {
                    $customerBookingRepository->rollback();

                    $result->setResult(CommandResult::RESULT_ERROR);
                    $result->setMessage(FrontendStrings::getCommonStrings()['payment_error']);
                    $result->setData([
                        'paymentSuccessful' => false
                    ]);

                    return $result;
                }

                break;
        }

        /** @var CustomerApplicationService $customerApplicationService */
        $customerApplicationService = $this->container->get('application.user.customer.service');

        $customerApplicationService->setWPUserForCustomer(
            $booking->getCustomer(),
            $bookingData['isNewUser']
        );

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully added booking');
        $result->setData([
            'type'                     => $type,
            $type                      => $reservation->toArray(),
            Entities::BOOKING          => $booking->toArray(),
            'utcTime'                  => $reservationService->getBookingPeriods($reservation, $booking, $bookable),
            'appointmentStatusChanged' => $bookingData['appointmentStatusChanged']
        ]);

        $customerBookingRepository->commit();

        return $result;
    }
}
