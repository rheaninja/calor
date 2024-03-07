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

namespace Magezon\CustomerApproval\Plugin\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;
use Magezon\CustomerApproval\Helper\Data;

class AccountManagement
{

    /**
     * @var \Magezon\CustomerApproval\Helper\Data
     */
    public $dataHelper;

    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }


    public function beforeCreateAccount($subject, CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        if ($this->dataHelper->isEnabled() && $this->dataHelper->isAutoApproval() == ListStatus::STATUS_APPROVED) {
            $customer->setCustomAttribute(Data::IS_APPROVED, ListStatus::STATUS_APPROVED);
        }

        return [$customer, $password, $redirectUrl];    
    }
}
