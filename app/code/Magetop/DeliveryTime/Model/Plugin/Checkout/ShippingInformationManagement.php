<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\DeliveryTime\Model\Plugin\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magetop\DeliveryTime\Helper\Data;

/**
 * Class ShippingInformationManagement
 * @package Magetop\DeliveryTime\Model\Plugin\Checkout
 */
class ShippingInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Data
     */
    private $mpDtHelper;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param Data $mpDtHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Data $mpDtHelper
    ) {
        $this->cartRepository = $cartRepository;
        $this->mpDtHelper     = $mpDtHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();

        if (!$extensionAttributes || !$this->mpDtHelper->isEnabled()) {
            return [$cartId, $addressInformation];
        }

        $deliveryInformation = [
            'deliveryDate'      => $extensionAttributes->getMpDeliveryDate(),
            'deliveryTime'      => $extensionAttributes->getMpDeliveryTime(),
            'houseSecurityCode' => $extensionAttributes->getMpHouseSecurityCode(),
            'deliveryComment'   => $extensionAttributes->getMpDeliveryComment()
        ];

        $quote = $this->cartRepository->get($cartId);
        $quote->setData('mp_delivery_information', Data::jsonEncode($deliveryInformation));

        return [$cartId, $addressInformation];
    }
}
