<?php

namespace AmeliaBooking\Application\Services\User;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\AbstractBooking;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\CustomerBookingEventPeriodRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;

/**
 * Class CustomerApplicationService
 *
 * @package AmeliaBooking\Application\Services\User
 */
class CustomerApplicationService
{
    private $container;

    /**
     * ProviderApplicationService constructor.
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
     * @param array $customers
     *
     * @return array
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function removeAllExceptCurrentUser($customers)
    {
        /** @var Provider $currentUser */
        $currentUser = $this->container->get('logged.in.user');

        if ($currentUser === null) {
            return [];
        }

        if ($currentUser->getType() === 'customer'
            && !$this->container->getPermissionsService()->currentUserCanReadOthers(Entities::APPOINTMENTS)
        ) {
            if ($currentUser->getId() === null) {
                return [];
            }

            $currentUserId = $currentUser->getId()->getValue();
            foreach ($customers as $key => $provider) {
                if ($provider['id'] !== $currentUserId) {
                    unset($customers[$key]);
                }
            }
        }

        return array_values($customers);
    }

    /**
     * Create a new user if doesn't exists. For adding appointment from the front-end.
     *
     * @param array         $userData
     * @param CommandResult $result
     *
     * @return AbstractUser
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getNewOrExistingCustomer($userData, $result)
    {
        /** @var AbstractUser $user */
        $loggedInUser = $this->container->get('logged.in.user');

        if ($loggedInUser) {
            if ($loggedInUser->getType() === AbstractUser::USER_ROLE_ADMIN) {
                $userData['type'] = AbstractUser::USER_ROLE_ADMIN;
            } elseif ($loggedInUser->getType() === AbstractUser::USER_ROLE_MANAGER) {
                $userData['type'] = AbstractUser::USER_ROLE_MANAGER;
            }
        }

        $user = UserFactory::create($userData);

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        // Check if email already exists
        $userWithSameMail = $userRepository->getByEmail($user->getEmail()->getValue());

        if ($userWithSameMail) {
            /** @var SettingsService $settingsService */
            $settingsService = $this->container->get('domain.settings.service');

            // If email already exists, check if First Name and Last Name from request are same with the First Name
            // and Last Name from $userWithSameMail. If these are not same return error message.
            if ($settingsService->getSetting('roles', 'inspectCustomerInfo') &&
                ($userWithSameMail->getFirstName()->getValue() !== $user->getFirstName()->getValue() ||
                    $userWithSameMail->getLastName()->getValue() !== $user->getLastName()->getValue())
            ) {
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setData(['emailError' => true]);
            }

            return $userWithSameMail;
        }

        return $user;
    }

    /**
     * @param AbstractUser $user
     * @param Collection   $reservations
     *
     * @return void
     *
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public function removeBookingsForOtherCustomers($user, $reservations)
    {
        $isCustomer = $user === null || ($user && $user->getType() === AbstractUser::USER_ROLE_CUSTOMER);

        /** @var AbstractBooking  $reservation */
        foreach ($reservations->getItems() as $reservation) {
            /** @var CustomerBooking  $booking */
            foreach ($reservation->getBookings()->getItems() as $key => $booking) {
                if ($isCustomer &&
                    (!$user || ($user && $user->getId()->getValue() !== $booking->getCustomerId()->getValue()))
                ) {
                    $reservation->getBookings()->deleteItem($key);
                }
            }
        }
    }

    /**
     * @param Customer $customer
     * @param bool     $isNewCustomer
     *
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function setWPUserForCustomer($customer, $isNewCustomer)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $createNewUser = $settingsService->getSetting('roles', 'automaticallyCreateCustomer');

        if ($createNewUser && $isNewCustomer && $customer && $customer->getEmail()) {
            /** @var UserApplicationService $userAS */
            $userAS = $this->container->get('application.user.service');

            try {
                if ($customer->getExternalId()) {
                    $userAS->setWpUserIdForExistingUser($customer->getId()->getValue(), $customer);
                } else {
                    $userAS->setWpUserIdForNewUser($customer->getId()->getValue(), $customer);
                }
            } catch (\Exception $e) {
            }
        }

        if ($createNewUser && $customer && $customer->getExternalId()) {
            $customer->setExternalId(new Id($customer->getExternalId()->getValue()));
        }
    }

    /**
     * @param AbstractUser|Customer $customer
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function delete($customer)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerBookingEventPeriodRepository $bookingEventPeriodRepository */
        $bookingEventPeriodRepository = $this->container->get('domain.booking.customerBookingEventPeriod.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var CustomerBookingExtraRepository $customerBookingExtraRepository */
        $customerBookingExtraRepository = $this->container->get('domain.booking.customerBookingExtra.repository');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var Collection $appointments */
        $appointments = $appointmentRepository->getFiltered([
            'customerId' => $customer->getId()->getValue()
        ]);

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointment) {
            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $bookingId => $booking) {
                if ($booking->getCustomer()->getId()->getValue() !== $customer->getId()->getValue()) {
                    continue;
                }

                if (
                    !$bookingEventPeriodRepository->deleteByEntityId($bookingId, 'customerBookingId') ||
                    !$paymentRepository->deleteByEntityId($bookingId, 'customerBookingId') ||
                    !$bookingRepository->delete($bookingId)
                ) {
                    return false;
                }
            }
        }

        return $userRepository->delete($customer->getId()->getValue());
    }
}
