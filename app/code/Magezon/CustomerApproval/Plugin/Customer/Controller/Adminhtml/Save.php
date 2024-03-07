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

namespace Magezon\CustomerApproval\Plugin\Customer\Controller\Adminhtml;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magezon\CustomerApproval\Helper\Data;

class Save
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * @var \Magezon\CustomerApproval\Model\Email
     */
    protected $email;

    /**
     * @var \Magezon\CustomerApproval\Helper\Data
     */
    public $dataHelper;

    /**
     * [__construct description]
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory 
     * @param \Magezon\CustomerApproval\Model\Email   $email           
     * @param Data                                    $dataHelper      
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magezon\CustomerApproval\Model\Email $email,
        Data $dataHelper

    ) {
        $this->customerFactory = $customerFactory;
        $this->email = $email;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Contact\Controller\Index\Post $subject
     * @throws \Exception
     */
    public function aroundExecute($subject, $proceed)
    {
        $customerType = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
        $originalRequestData = $subject->getRequest()->getPostValue($customerType);
        $customerId = $originalRequestData['entity_id'];
        $customer = $this->customerFactory->create()->load($customerId);
        $currentStatus = $customer->getData(Data::IS_APPROVED);
        $postStatus = $originalRequestData[Data::IS_APPROVED];

        $result = $proceed();

        if ($this->dataHelper->isEnabled() && $result && $postStatus != $currentStatus ) {
            if (!empty($originalRequestData['email'])) {
                $this->email->sendEmailToCustomer($postStatus, $originalRequestData['email'], $customer);
            }
        }

        return $result;
    }
}
