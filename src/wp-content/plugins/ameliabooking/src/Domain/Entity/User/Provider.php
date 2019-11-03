<?php

namespace AmeliaBooking\Domain\Entity\User;

use AmeliaBooking\Domain\Entity\Google\GoogleCalendar;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\Description;
use AmeliaBooking\Domain\ValueObjects\String\Email;
use AmeliaBooking\Domain\ValueObjects\String\Name;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\ValueObjects\String\Phone;

/**
 * Class Provider
 *
 * @package AmeliaBooking\Domain\Entity\User
 */
class Provider extends AbstractUser
{
    /** @var Description */
    private $note;

    /** @var Collection */
    private $weekDayList;

    /** @var Collection */
    private $serviceList;

    /** @var Collection */
    private $dayOffList;

    /** @var Collection */
    private $specialDayList;

    /** @var Collection */
    private $appointmentList;

    /** @var Id */
    private $locationId;

    /** @var GoogleCalendar */
    private $googleCalendar;

    /**
     * @param Name        $firstName
     * @param Name        $lastName
     * @param Email       $email
     * @param Description $note
     * @param Phone       $phone
     * @param Collection  $weekDayList
     * @param Collection  $serviceList
     * @param Collection  $dayOffList
     * @param Collection  $specialDayList
     * @param Collection  $appointmentList
     */
    public function __construct(
        Name $firstName,
        Name $lastName,
        Email $email,
        Description $note,
        Phone $phone,
        Collection $weekDayList,
        Collection $serviceList,
        Collection $dayOffList,
        Collection $specialDayList,
        Collection $appointmentList
    ) {
        parent::__construct($firstName, $lastName, $email);
        $this->note = $note;
        $this->phone = $phone;
        $this->weekDayList = $weekDayList;
        $this->serviceList = $serviceList;
        $this->dayOffList = $dayOffList;
        $this->specialDayList = $specialDayList;
        $this->appointmentList = $appointmentList;
    }

    /**
     * Get the user type in a string form
     */
    public function getType()
    {
        return self::USER_ROLE_PROVIDER;
    }

    /**
     * @return Description
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param Description $note
     */
    public function setNote(Description $note)
    {
        $this->note = $note;
    }

    /**
     * @return Collection
     */
    public function getWeekDayList()
    {
        return $this->weekDayList;
    }

    /**
     * @param Collection $weekDayList
     */
    public function setWeekDayList(Collection $weekDayList)
    {
        $this->weekDayList = $weekDayList;
    }

    /**
     * @return Collection
     */
    public function getServiceList()
    {
        return $this->serviceList;
    }

    /**
     * @param Collection $serviceList
     */
    public function setServiceList(Collection $serviceList)
    {
        $this->serviceList = $serviceList;
    }

    /**
     * @return Collection
     */
    public function getDayOffList()
    {
        return $this->dayOffList;
    }

    /**
     * @param Collection $dayOffList
     */
    public function setDayOffList(Collection $dayOffList)
    {
        $this->dayOffList = $dayOffList;
    }

    /**
     * @return Collection
     */
    public function getSpecialDayList()
    {
        return $this->specialDayList;
    }

    /**
     * @param Collection $specialDayList
     */
    public function setSpecialDayList(Collection $specialDayList)
    {
        $this->specialDayList = $specialDayList;
    }

    /**
     * @return Collection
     */
    public function getAppointmentList()
    {
        return $this->appointmentList;
    }

    /**
     * @param Collection $appointmentList
     */
    public function setAppointmentList(Collection $appointmentList)
    {
        $this->appointmentList = $appointmentList;
    }

    /**
     * @return Id
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param Id $locationId
     */
    public function setLocationId(Id $locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * @return GoogleCalendar mixed
     */
    public function getGoogleCalendar()
    {
        return $this->googleCalendar;
    }

    /**
     * @param mixed $googleCalendar
     */
    public function setGoogleCalendar($googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Returns the Provider entity fields in an array form
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'phone'          => $this->phone->getValue(),
                'note'           => $this->note->getValue(),
                'weekDayList'    => $this->weekDayList->toArray(),
                'serviceList'    => $this->serviceList->toArray(),
                'dayOffList'     => $this->dayOffList->toArray(),
                'specialDayList' => $this->specialDayList->toArray(),
                'locationId'     => $this->getLocationId() ? $this->getLocationId()->getValue() : null,
                'googleCalendar' => $this->getGoogleCalendar() ? $this->getGoogleCalendar()->toArray() : null
            ]
        );
    }
}
