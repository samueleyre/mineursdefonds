<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Settings;

use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Infrastructure\Common\Container;

/**
 * Class SettingsService
 *
 * @package AmeliaBooking\Application\Services\Settings
 */
class SettingsService
{
    /** @var Container */
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
     * @return array
     * @throws \Exception
     * @throws \Interop\Container\Exception\ContainerException +
     */
    public function getGlobalDaysOff()
    {
        $daysOff = [];

        /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');

        $settingsDaysOff = $settingsDS->getCategorySettings('daysOff');

        foreach ($settingsDaysOff as $settingsDayOff) {
            $dayOffPeriod = new \DatePeriod(
                DateTimeService::getCustomDateTimeObject($settingsDayOff['startDate']),
                new \DateInterval('P1D'),
                DateTimeService::getCustomDateTimeObject($settingsDayOff['endDate'])->modify('+1 day')
            );

            /** @var \DateTime $dayOffDate */
            foreach ($dayOffPeriod as $dayOffDate) {
                if ($settingsDayOff['repeat']) {
                    $dayOffDateFormatted = $dayOffDate->format('m-d');
                    $daysOff[$dayOffDateFormatted] = $dayOffDateFormatted;
                } else {
                    $dayOffDateFormatted = $dayOffDate->format('Y-m-d');
                    $daysOff[$dayOffDateFormatted] = $dayOffDateFormatted;
                }
            }
        }

        return $daysOff;
    }
}
