<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Bookable;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\Picture;
use AmeliaBooking\Domain\ValueObjects\String\Color;
use AmeliaBooking\Domain\ValueObjects\String\Description;
use AmeliaBooking\Domain\ValueObjects\String\Name;

/**
 * Class AbstractBookable
 *
 * @package AmeliaBooking\Domain\Entity\Bookable
 */
class AbstractBookable
{
    /** @var Id */
    private $id;

    /** @var  Name */
    protected $name;

    /** @var Description */
    protected $description;

    /** @var  Color */
    protected $color;

    /** @var  Price */
    protected $price;

    /** @var  Picture */
    protected $picture;

    /** @var PositiveInteger */
    protected $position;

    /** @var Collection */
    private $extras;

    /** @var Collection */
    private $coupons;

    /**
     * AbstractBookable constructor.
     *
     * @param Name        $name
     * @param Price       $price
     */
    public function __construct(
        Name $name,
        Price $price
    ) {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @return Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Id $id
     */
    public function setId(Id $id)
    {
        $this->id = $id;
    }

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Description $description
     */
    public function setDescription(Description $description)
    {
        $this->description = $description;
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Color $color
     */
    public function setColor(Color $color)
    {
        $this->color = $color;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Price $price
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;
    }

    /**
     * @return Picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param Picture $picture
     */
    public function setPicture(Picture $picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return PositiveInteger
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param PositiveInteger $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return Collection
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param Collection $extras
     */
    public function setExtras(Collection $extras)
    {
        $this->extras = $extras;
    }

    /**
     * @return Collection
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * @param Collection $coupons
     */
    public function setCoupons(Collection $coupons)
    {
        $this->coupons = $coupons;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'               => null !== $this->getId() ? $this->getId()->getValue() : null,
            'name'             => $this->getName()->getValue(),
            'description'      => null !== $this->getDescription() ? $this->getDescription()->getValue() : null,
            'color'            => null !== $this->getColor() ? $this->getColor()->getValue() : null,
            'price'            => $this->getPrice()->getValue(),
            'pictureFullPath'  => null !== $this->getPicture() ? $this->getPicture()->getFullPath() : null,
            'pictureThumbPath' => null !== $this->getPicture() ? $this->getPicture()->getThumbPath() : null,
            'extras'           => $this->getExtras() ? $this->getExtras()->toArray() : [],
            'coupons'          => $this->getCoupons() ? $this->getCoupons()->toArray() : [],
            'position'         => null !== $this->getPosition() ? $this->getPosition()->getValue() : null
        ];
    }
}
