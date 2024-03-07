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

namespace Magetop\DeliveryTime\Block\Adminhtml\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;

/**
 * Class OrderViewTabInfo
 * @package Magetop\DeliveryTime\Block\Adminhtml\Plugin
 */
class OrderViewTabInfo
{
    /**
     * @param Info $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetGiftOptionsHtml(Info $subject, $result)
    {
        $result .= $subject->getChildHtml('mp_delivery_information');

        return $result;
    }
}
