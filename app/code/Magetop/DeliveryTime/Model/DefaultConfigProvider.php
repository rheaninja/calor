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

namespace Magetop\DeliveryTime\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magetop\DeliveryTime\Helper\Data as MpDtHelper;
use Zend_Serializer_Exception;

/**
 * Class DefaultConfigProvider
 * @package Magetop\DeliveryTime\Model
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MpDtHelper
     */
    protected $mpDtHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param MpDtHelper $mpDtHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MpDtHelper $mpDtHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->mpDtHelper   = $mpDtHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->mpDtHelper->isEnabled()) {
            return [];
        }

        return ['mpDtConfig' => $this->getMpDtConfig()];
    }

    /**
     * @return array
     * @throws Zend_Serializer_Exception
     */
    private function getMpDtConfig()
    {
        return [
            'isEnabledDeliveryTime'      => $this->mpDtHelper->isEnabledDeliveryTime(),
            'isEnabledHouseSecurityCode' => $this->mpDtHelper->isEnabledHouseSecurityCode(),
            'isEnabledDeliveryComment'   => $this->mpDtHelper->isEnabledDeliveryComment(),
            'deliveryDateFormat'         => $this->mpDtHelper->getDateFormat(),
            'deliveryDaysOff'            => $this->mpDtHelper->getDaysOff(),
            'deliveryDateOff'            => $this->mpDtHelper->getDateOff(),
            'deliveryTime'               => $this->mpDtHelper->getDeliveryTIme()
        ];
    }
}
