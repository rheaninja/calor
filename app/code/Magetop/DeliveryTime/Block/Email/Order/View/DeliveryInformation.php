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

namespace Magetop\DeliveryTime\Block\Email\Order\View;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


use Magetop\DeliveryTime\Helper\Data as MpDtHelper;

/**
 * Class Comment
 * @package Magetop\DeliveryTime\Block\Order\View
 */
class DeliveryInformation extends Template
{
    /**
     * @type Registry|null
     */
    protected $registry = null;

    /**
     * @var MpDtHelper
     */
    protected $mpDtHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param MpDtHelper $mpDtHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        MpDtHelper $mpDtHelper,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null
    ) {
        $this->registry   = $registry;
        $this->mpDtHelper = $mpDtHelper;
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Get delivery information
     *
     * @return DataObject
     */
    public function getDeliveryInformation()
    {
        $result = [];

        if ($order = $this->getOrder()) {
            $deliveryInformation = $order->getMpDeliveryInformation();

            if (is_array(json_decode($deliveryInformation, true))) {
                $result = json_decode($deliveryInformation, true);
            } else {
                $values = explode(' ', $deliveryInformation);
                if (sizeof($values) > 1) {
                    $result['deliveryDate'] = $values[0];
                    $result['deliveryTime'] = $values[1];
                }

                $result['houseSecurityCode'] = $order->getOscOrderHouseSecurityCode();
            }
        }

        return new DataObject($result);
    }

    /**
     * Get current order
     *
     * @return mixed
     */
    // public function getOrder()
    // {
    //     return $this->registry->registry('current_order');
    // }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     * @since 102.1.0
     */
    public function getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }
}
