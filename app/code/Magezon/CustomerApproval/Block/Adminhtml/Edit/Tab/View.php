<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at http://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_CustomerApproval
 * @copyright Copyright (C) 2021 Magezon (http://magezon.com)
 */

namespace Magezon\CustomerApproval\Block\Adminhtml\Edit\Tab;

use Magezon\CustomerApproval\Helper\Data;
use Magento\Framework\App\ObjectManager;

class View extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo
{
    /**
     * @return mixed|string
     */
    public function getApprovedLabel()
    {
        $objectManager = ObjectManager::getInstance();
        $statusLabel = '';
        if ($value = $this->getCustomer()->getCustomAttribute(Data::IS_APPROVED)) {
            $value = $value->getValue();
            $statusLabel = $objectManager->create(Data::class)->getStatusLabel($value);
        }
        
        return $statusLabel;
    }
}
