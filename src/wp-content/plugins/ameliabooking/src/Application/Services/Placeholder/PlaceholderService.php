<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\CustomField\CustomField;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;

/**
 * Class PlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
abstract class PlaceholderService implements PlaceholderServiceInterface
{
    /** @var Container */
    protected $container;

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
     * @param string $text
     * @param array  $data
     *
     * @return mixed
     */
    public function applyPlaceholders($text, $data)
    {
        $placeholders = array_map(
            function ($placeholder) {
                return "%{$placeholder}%";
            },
            array_keys($data)
        );

        return str_replace($placeholders, array_values($data), $text);
    }

    /**
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getPlaceholdersDummyData()
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $companySettings = $settingsService->getCategorySettings('company');

        return array_merge([
            'company_address'        => $companySettings['address'],
            'company_name'           => $companySettings['name'],
            'company_phone'          => $companySettings['phone'],
            'company_website'        => $companySettings['website'],
            'customer_email'         => 'customer@domain.com',
            'customer_first_name'    => 'John',
            'customer_last_name'     => 'Doe',
            'customer_full_name'     => 'John Doe',
            'customer_phone'         => '193-951-2600',
        ], $this->getEntityPlaceholdersDummyData());
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
    public function getPlaceholdersData($appointment, $bookingKey = null, $token = null)
    {
        $data = $this->getEntityPlaceholdersData($appointment, $bookingKey, $token);

        $data = array_merge($data, $this->getBookingData($appointment, $bookingKey, $token));
        $data = array_merge($data, $this->getCompanyData());
        $data = array_merge($data, $this->getCustomersData($appointment, $bookingKey));
        $data = array_merge($data, $this->getCustomFieldsData($appointment, $bookingKey));

        return $data;
    }

    /**
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getCompanyData()
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $companySettings = $settingsService->getCategorySettings('company');

        return [
            'company_address' => $companySettings['address'],
            'company_name'    => $companySettings['name'],
            'company_phone'   => $companySettings['phone'],
            'company_website' => $companySettings['website']
        ];
    }

    /**
     * @param        $appointment
     * @param null   $bookingKey
     * @param null   $token
     *
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    private function getBookingData($appointment, $bookingKey = null, $token = null)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $appointmentPrice = 0;
        // If notification is for provider: Appointment price will be sum of all bookings prices
        // If notification is for customer: Appointment price will be price of his booking
        if ($bookingKey === null) {
            foreach ((array)$appointment['bookings'] as $customerBooking) {
                $persons = isset($customerBooking['aggregatedPrice']) && $customerBooking['aggregatedPrice'] ?
                    $customerBooking['persons'] : 1;

                $appointmentPrice += (int)$customerBooking['price'] * $persons;

                foreach ($customerBooking['extras'] as $extra) {
                    $appointmentPrice += $extra['price'] * $extra['quantity'] * $persons;
                }

                if (!empty($customerBooking['coupon']['discount'])) {
                    $appointmentPrice =
                        (1 - $customerBooking['coupon']['discount'] / 100) * $appointmentPrice;
                }

                if (!empty($customerBooking['coupon']['deduction'])) {
                    $appointmentPrice -= $customerBooking['coupon']['deduction'];
                }
            }
        } else {
            $persons = isset($appointment['bookings'][$bookingKey]['aggregatedPrice']) &&
            $appointment['bookings'][$bookingKey]['aggregatedPrice'] ?
                $appointment['bookings'][$bookingKey]['persons'] : 1;

            $appointmentPrice = $appointment['bookings'][$bookingKey]['price'] * $persons;

            foreach ($appointment['bookings'][$bookingKey]['extras'] as $extra) {
                $appointmentPrice +=
                    $extra['price'] * $extra['quantity'] * $persons;
            }

            if (!empty($appointment['bookings'][$bookingKey]['coupon']['discount'])) {
                $appointmentPrice =
                    (1 - $appointment['bookings'][$bookingKey]['coupon']['discount'] / 100) * $appointmentPrice;
            }

            if (!empty($appointment['bookings'][$bookingKey]['coupon']['deduction'])) {
                $appointmentPrice -= $appointment['bookings'][$bookingKey]['coupon']['deduction'];
            }
        }

        return [
            "{$appointment['type']}_cancel_url" => $bookingKey !== null ?
                AMELIA_ACTION_URL . '/bookings/cancel/' . $appointment['bookings'][$bookingKey]['id'] .
                ($token ? '&token=' . $token : '') . "&type={$appointment['type']}" : '',
            "{$appointment['type']}_price"      => $helperService->getFormattedPrice($appointmentPrice),
        ];
    }

    /**
     * @param array $appointment
     * @param null  $bookingKey
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    private function getCustomersData($appointment, $bookingKey = null)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        // If the data is for employee
        if ($bookingKey === null) {
            $customers = [];
            $customerInformationData = [];

            foreach ((array)$appointment['bookings'] as $customerBooking) {
                $customer = $userRepository->getById($customerBooking['customerId']);

                if ($customerBooking['status'] !== BookingStatus::CANCELED && $customerBooking['status'] !== BookingStatus::REJECTED) {
                    if ($customerBooking['info']) {
                        $customerInformationData[] = json_decode($customerBooking['info'], true);
                    } else {
                        $customerInformationData[] = [
                            'firstName' => $customer->getFirstName()->getValue(),
                            'lastName'  => $customer->getLastName()->getValue(),
                            'phone'     => $customer->getPhone() ? $customer->getPhone()->getValue() : '',
                        ];
                    }

                    $customers[] = $customer;
                }
            }

            $phones = '';
            foreach ($customerInformationData as $key => $info) {
                if ($info['phone']) {
                    $phones .= $info['phone'] . ', ';
                } else {
                    $phones .= $customers[$key]->getPhone() ? $customers[$key]->getPhone()->getValue() . ', ' : '';
                }
            }

            return [
                'customer_email'      => implode(', ', array_map(function ($customer) {
                    /** @var Customer $customer */
                    return $customer->getEmail()->getValue();
                }, $customers)),
                'customer_first_name' => implode(', ', array_map(function ($info) {
                    return $info['firstName'];
                }, $customerInformationData)),
                'customer_last_name'  => implode(', ', array_map(function ($info) {
                    return $info['lastName'];
                }, $customerInformationData)),
                'customer_full_name'  => implode(', ', array_map(function ($info) {
                    return $info['firstName'] . ' ' . $info['lastName'];
                }, $customerInformationData)),
                'customer_phone'      => substr($phones, 0, -2)
            ];
        }

        // If data is for customer
        /** @var Customer $customer */
        $customer = $userRepository->getById($appointment['bookings'][$bookingKey]['customerId']);

        $info = json_decode($appointment['bookings'][$bookingKey]['info']);

        if ($info && $info->phone) {
            $phone = $info->phone;
        } else {
            $phone = $customer->getPhone() ? $customer->getPhone()->getValue() : '';
        }

        return [
            'customer_email'      => $customer->getEmail()->getValue(),
            'customer_first_name' => $info ? $info->firstName : $customer->getFirstName()->getValue(),
            'customer_last_name'  => $info ? $info->lastName : $customer->getLastName()->getValue(),
            'customer_full_name'  => $info ? $info->firstName . ' ' . $info->lastName : $customer->getFullName(),
            'customer_phone'      => $phone
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
    private function getCustomFieldsData($appointment, $bookingKey = null)
    {
        $customFieldsData = [];

        $bookingCustomFieldsKeys = [];

        if ($bookingKey === null) {
            foreach ($appointment['bookings'] as $booking) {
                $bookingCustomFields = json_decode($booking['customFields']);

                if ($bookingCustomFields) {
                    foreach ($bookingCustomFields as $bookingCustomFieldKey => $bookingCustomField) {
                        $bookingCustomFieldsKeys[(int)$bookingCustomFieldKey] = true;

                        if ($bookingCustomField && isset($bookingCustomField->value)) {
                            if (array_key_exists(
                                'custom_field_' . $bookingCustomFieldKey,
                                $customFieldsData
                            )) {
                                $customFieldsData['custom_field_' . $bookingCustomFieldKey]
                                    .= is_array($bookingCustomField->value)
                                    ? '; ' . implode('; ', $bookingCustomField->value) :
                                    '; ' . $bookingCustomField->value;
                            } else {
                                $customFieldsData['custom_field_' . $bookingCustomFieldKey] =
                                    is_array($bookingCustomField->value)
                                        ? implode('; ', $bookingCustomField->value) : $bookingCustomField->value;
                            }
                        }
                    }
                }
            }
        } else {
            $bookingCustomFields = json_decode($appointment['bookings'][$bookingKey]['customFields']);

            if ($bookingCustomFields) {
                foreach ($bookingCustomFields as $bookingCustomFieldKey => $bookingCustomField) {
                    if ($bookingCustomField && isset($bookingCustomField->value)) {
                        $bookingCustomFieldsKeys[(int)$bookingCustomFieldKey] = true;

                        $customFieldsData['custom_field_' . $bookingCustomFieldKey] = is_array($bookingCustomField->value)
                            ? implode('; ', $bookingCustomField->value) : $bookingCustomField->value;
                    }
                }
            }
        }

        /** @var CustomFieldRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('domain.customField.repository');

        if ($customFieldRepository) {
            /** @var Collection $customFields */
            $customFields = $customFieldRepository->getAll();

            /** @var CustomField $customField */
            foreach ($customFields->getItems() as $customField) {
                if (!array_key_exists($customField->getId()->getValue(), $bookingCustomFieldsKeys)) {
                    $customFieldsData['custom_field_' . $customField->getId()->getValue()] = '';
                }
            }
        }

        return $customFieldsData;
    }
}
