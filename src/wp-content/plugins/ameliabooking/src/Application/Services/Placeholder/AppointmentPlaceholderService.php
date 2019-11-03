<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Coupon\CouponApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\CouponInvalidException;
use AmeliaBooking\Domain\Common\Exceptions\CouponUnknownException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use DateTime;

/**
 * Class AppointmentPlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class AppointmentPlaceholderService extends PlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getEntityPlaceholdersDummyData()
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $companySettings = $settingsService->getCategorySettings('company');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $timestamp = date_create()->getTimestamp();

        return [
            'appointment_date'       => date_i18n($dateFormat, strtotime($timestamp)),
            'appointment_date_time'  => date_i18n($dateFormat . ' ' . $timeFormat, strtotime($timestamp)),
            'appointment_start_time' => date_i18n($timeFormat, $timestamp),
            'appointment_end_time'   => date_i18n($timeFormat, date_create('1 hour')->getTimestamp()),
            'appointment_notes'      => 'Appointment note',
            'appointment_price'      => $helperService->getFormattedPrice(100),
            'employee_email'         => 'employee@domain.com',
            'employee_first_name'    => 'Richard',
            'employee_last_name'     => 'Roe',
            'employee_full_name'     => 'Richard Roe',
            'employee_phone'         => '150-698-1858',
            'location_address'       => $companySettings['address'],
            'location_name'          => 'Location Name',
            'category_name'          => 'Category Name',
            'service_description'    => 'Service Description',
            'service_duration'       => $helperService->secondsToNiceDuration(5400),
            'service_name'           => 'Service Name',
            'service_price'          => $helperService->getFormattedPrice(100)
        ];
    }

    /**
     * @param array  $appointment
     * @param int    $bookingKey
     * @param string $token
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public function getEntityPlaceholdersData($appointment, $bookingKey = null, $token = null)
    {
        $data = [];

        $data = array_merge($data, $this->getAppointmentData($appointment, $bookingKey));
        $data = array_merge($data, $this->getEmployeeData($appointment));
        $data = array_merge($data, $this->getServiceData($appointment));
        $data = array_merge($data, $this->getCouponsData($appointment, $bookingKey));

        return $data;
    }

    /**
     * @param      $appointment
     * @param null $bookingKey
     *
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    private function getAppointmentData($appointment, $bookingKey = null)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        if ($bookingKey !== null && $appointment['bookings'][$bookingKey]['utcOffset'] !== null
            && $settingsService->getSetting('general', 'showClientTimeZone')) {
            $bookingStart = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingStart']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );

            $bookingEnd = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingEnd']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );
        } else {
            $bookingStart = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingStart']);
            $bookingEnd = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingEnd']);
        }

        return [
            'appointment_notes'      => $appointment['internalNotes'],
            'appointment_date'       => date_i18n($dateFormat, $bookingStart->getTimestamp()),
            'appointment_date_time'  => date_i18n($dateFormat . ' ' . $timeFormat, $bookingStart->getTimestamp()),
            'appointment_start_time' => date_i18n($timeFormat, $bookingStart->getTimestamp()),
            'appointment_end_time'   => date_i18n($timeFormat, $bookingEnd->getTimestamp()),
        ];
    }

    /**
     * @param $appointmentArray
     *
     * @return array
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    private function getServiceData($appointmentArray)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var Service $service */
        $service = $serviceRepository->getByIdWithExtras($appointmentArray['serviceId']);
        /** @var Category $category */
        $category = $categoryRepository->getById($service->getCategoryId()->getValue());

        $data = [
            'category_name'       => $category->getName()->getValue(),
            'service_description' => $service->getDescription()->getValue(),
            'service_duration'    => $helperService->secondsToNiceDuration($service->getDuration()->getValue()),
            'service_name'        => $service->getName()->getValue(),
            'service_price'       => $helperService->getFormattedPrice($service->getPrice()->getValue())
        ];

        $bookingExtras = [];

        foreach ((array)$appointmentArray['bookings'] as $booking) {
            foreach ((array)$booking['extras'] as $bookingExtra) {
                $bookingExtras[$bookingExtra['extraId']] = [
                    'quantity' => $bookingExtra['quantity']
                ];
            }
        }

        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        if ($extraRepository) {
            /** @var Collection $extras */
            $extras = $extraRepository->getAllIndexedById();

            /** @var Extra $extra */
            foreach ($extras->getItems() as $extra) {
                $extraId = $extra->getId()->getValue();

                $data["service_extra_{$extraId}_name"] =
                    array_key_exists($extraId, $bookingExtras) ? $extra->getName()->getValue() : '';

                $data["service_extra_{$extraId}_quantity"] =
                    array_key_exists($extraId, $bookingExtras) ? $bookingExtras[$extraId]['quantity'] : '';
            }
        }

        return $data;
    }

    /**
     * @param $appointment
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    private function getEmployeeData($appointment)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');
        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var Provider $user */
        $user = $userRepository->getById($appointment['providerId']);

        if (!($locationId = $appointment['locationId'])) {
            $locationId = $user->getLocationId() ? $user->getLocationId()->getValue() : null;
        }

        /** @var Location $location */
        $location = $locationId ? $locationRepository->getById($locationId) : null;

        return [
            'employee_email'      => $user->getEmail()->getValue(),
            'employee_first_name' => $user->getFirstName()->getValue(),
            'employee_last_name'  => $user->getLastName()->getValue(),
            'employee_full_name'  => $user->getFirstName()->getValue() . ' ' . $user->getLastName()->getValue(),
            'employee_phone'      => $user->getPhone()->getValue(),
            'location_address'    => !$location ?
                $settingsService->getSetting('company', 'address') : $location->getAddress()->getValue(),
            'location_name'       => !$location ?
                $settingsService->getSetting('company', 'address') : $location->getName()->getValue()
        ];
    }

    /**
     * @param array $appointment
     * @param null  $bookingKey
     *
     * @return array
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws QueryExecutionException
     */
    private function getCouponsData($appointment, $bookingKey = null)
    {
        $couponsData = [];

        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        if ($couponRepository && $bookingKey !== null) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            /** @var AppointmentRepository $appointmentRepo */
            $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

            /** @var CouponApplicationService $couponAS */
            $couponAS = $this->container->get('application.coupon.service');

            $serviceId = $appointment['serviceId'];

            /** @var Collection $coupons */
            $coupons = $couponRepository->getAllByCriteria([]);

            $customerId = $appointment['bookings'][$bookingKey]['customerId'];

            /** @var Collection $appointments */
            $appointments = $appointmentRepo->getFiltered([
                'customerId'    => $customerId,
                'status'        => BookingStatus::APPROVED,
                'bookingStatus' => BookingStatus::APPROVED,
                'services'      => [
                    $serviceId
                ]
            ]);

            /** @var Coupon $coupon */
            foreach ($coupons->getItems() as $coupon) {
                $sendCoupon = (
                        !$coupon->getNotificationRecurring()->getValue() &&
                        $appointments->length() === $coupon->getNotificationInterval()->getValue()
                    ) || (
                        $coupon->getNotificationRecurring()->getValue() &&
                        $appointments->length() % $coupon->getNotificationInterval()->getValue() === 0
                    );

                try {
                    if ($sendCoupon && $couponAS->inspectCoupon($coupon, $serviceId, $customerId, true)) {
                        $couponsData["coupon_{$coupon->getId()->getValue()}"] =
                            FrontendStrings::getCommonStrings()['coupon_send_text'] .
                            $coupon->getCode()->getValue() . '<br>' .
                            ($coupon->getDeduction() && $coupon->getDeduction()->getValue() ?
                                BackendStrings::getFinanceStrings()['deduction'] . ' ' .
                                $helperService->getFormattedPrice($coupon->getDeduction()->getValue()) . '<br>'
                                : ''
                            ) .
                            ($coupon->getDiscount() && $coupon->getDiscount()->getValue() ?
                                BackendStrings::getPaymentStrings()['discount_amount'] . ' ' .
                                $coupon->getDiscount()->getValue() . '%'
                                : '');
                    } else {
                        $couponsData["coupon_{$coupon->getId()->getValue()}"] = '';
                    }
                } catch (CouponUnknownException $e) {
                    $couponsData["coupon_{$coupon->getId()->getValue()}"] = '';
                } catch (CouponInvalidException $e) {
                    $couponsData["coupon_{$coupon->getId()->getValue()}"] = '';
                }
            }
        }

        return $couponsData;
    }
}
