<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_GiftCard
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\DeliveryTime\Model\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Magetop\DeliveryTime\Api\Data\DeliveryTimeInterfaceFactory;
use Magetop\DeliveryTime\Api\DeliveryTimeManagementInterface;
use Magetop\DeliveryTime\Helper\Data;
use Magetop\DeliveryTime\Model\Api\Data\DeliveryTime;

/**
 * Class DeliveryTimeManagement
 * @package Magetop\DeliveryTime\Model\Api
 */
class DeliveryTimeManagement implements DeliveryTimeManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var DeliveryTimeInterfaceFactory
     */
    private $deliveryTimeFactory;

    /**
     * DeliveryTimeManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param DeliveryTimeInterfaceFactory $deliveryTimeFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        DeliveryTimeInterfaceFactory $deliveryTimeFactory
    ) {
        $this->cartRepository      = $cartRepository;
        $this->deliveryTimeFactory = $deliveryTimeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $quote = $this->cartRepository->get($cartId);

        $mpDtData = Data::jsonDecode($quote->getData('mp_delivery_information'));

        /** @var DeliveryTime $deliveryTime */
        $deliveryTime = $this->deliveryTimeFactory->create();

        return $deliveryTime->setData($mpDtData);
    }
}
