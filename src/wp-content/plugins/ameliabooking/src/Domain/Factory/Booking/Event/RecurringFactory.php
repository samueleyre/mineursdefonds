<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Booking\Event;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\WholeNumber;
use AmeliaBooking\Domain\ValueObjects\Recurring;
use AmeliaBooking\Domain\ValueObjects\String\Cycle;

/**
 * Class RecurringFactory
 *
 * @package AmeliaBooking\Domain\Factory\Booking\Event
 */
class RecurringFactory
{

    /**
     * @param $data
     *
     * @return Recurring
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        $recurring = new Recurring(new Cycle($data['cycle']));

        if (isset($data['order'])) {
            $recurring->setOrder(new WholeNumber($data['order']));
        }

        if (isset($data['until'])) {
            $recurring->setUntil(new DateTimeValue(DateTimeService::getCustomDateTimeObject($data['until'])));
        }

        return $recurring;
    }
}
