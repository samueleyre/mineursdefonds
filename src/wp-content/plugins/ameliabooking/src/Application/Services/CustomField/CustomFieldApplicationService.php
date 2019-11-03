<?php

namespace AmeliaBooking\Application\Services\CustomField;

use AmeliaBooking\Domain\Entity\CustomField\CustomField;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldOptionRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldServiceRepository;

/**
 * Class CustomFieldApplicationService
 *
 * @package AmeliaBooking\Application\Services\Coupon
 */
class CustomFieldApplicationService
{
    private $container;

    /**
     * CouponApplicationService constructor.
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
     * @param CustomField $customField
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function delete($customField)
    {
        /** @var CouponRepository $couponRepository */
        $customFieldRepository = $this->container->get('domain.customField.repository');

        /** @var CustomFieldServiceRepository $customFieldServiceRepository */
        $customFieldServiceRepository = $this->container->get('domain.customFieldService.repository');

        /** @var CustomFieldOptionRepository $customFieldOptionRepository */
        $customFieldOptionRepository = $this->container->get('domain.customFieldOption.repository');

        return
            $customFieldServiceRepository->deleteByEntityId($customField->getId()->getValue(), 'customFieldId') &&
            $customFieldOptionRepository->deleteByEntityId($customField->getId()->getValue(), 'customFieldId') &&
            $customFieldRepository->delete($customField->getId()->getValue());
    }
}
