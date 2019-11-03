<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Booking\Appointment;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\AbstractCustomerBooking;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\IntegerValue;
use AmeliaBooking\Domain\ValueObjects\String\Token;

/**
 * Class CustomerBooking
 *
 * @package AmeliaBooking\Domain\Entity\Booking\Appointment
 */
class CustomerBooking extends AbstractCustomerBooking
{
    /** @var Id */
    private $appointmentId;

    /** @var IntegerValue */
    private $persons;

    /** @var Collection */
    private $payments;

    /** @var Token */
    private $token;

    /** @var IntegerValue */
    private $utcOffset;

    /** @var  BooleanValueObject */
    protected $aggregatedPrice;

    /**
     * CustomerBooking constructor.
     *
     * @param Id            $customerId
     * @param BookingStatus $status
     * @param IntegerValue  $persons
     */
    public function __construct(
        Id $customerId,
        BookingStatus $status,
        IntegerValue $persons
    ) {
        parent::__construct($customerId, $status);
        $this->persons = $persons;
    }

    /**
     * @return Id
     */
    public function getAppointmentId()
    {
        return $this->appointmentId;
    }

    /**
     * @param Id $appointmentId
     */
    public function setAppointmentId(Id $appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    /**
     * @return IntegerValue
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param IntegerValue $persons
     */
    public function setPersons(IntegerValue $persons)
    {
        $this->persons = $persons;
    }

    /**
     * @return Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Collection $payments
     */
    public function setPayments(Collection $payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return IntegerValue
     */
    public function getUtcOffset()
    {
        return $this->utcOffset;
    }

    /**
     * @param IntegerValue $utcOffset
     */
    public function setUtcOffset(IntegerValue $utcOffset)
    {
        $this->utcOffset = $utcOffset;
    }

    /**
     * @return BooleanValueObject
     */
    public function getAggregatedPrice()
    {
        return $this->aggregatedPrice;
    }

    /**
     * @param BooleanValueObject $aggregatedPrice
     */
    public function setAggregatedPrice(BooleanValueObject $aggregatedPrice)
    {
        $this->aggregatedPrice = $aggregatedPrice;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'appointmentId'   => null !== $this->getAppointmentId() ? $this->getAppointmentId()->getValue() : null,
                'persons'         => $this->getPersons()->getValue(),
                'token'           => $this->getToken() ? $this->getToken()->getValue() : null,
                'payments'        => null !== $this->getPayments() ? $this->getPayments()->toArray() : null,
                'utcOffset'       => null !== $this->getUtcOffset() ? $this->getUtcOffset()->getValue() : null,
                'aggregatedPrice' => $this->getAggregatedPrice() ? $this->getAggregatedPrice()->getValue() : null,
            ]
        );
    }
}
