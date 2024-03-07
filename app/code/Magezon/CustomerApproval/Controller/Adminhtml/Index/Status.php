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

namespace Magezon\CustomerApproval\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;

class Status extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Magezon_CustomerApproval::massaction';

    /**
     * @var \Magezon\CustomerApproval\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\CustomerApproval\Model\Email
     */
    protected $email;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Ui\Component\MassAction\Filter
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     * @param \Magezon\CustomerApproval\Model\Email
     * @param \Magezon\CustomerApproval\Helper\Data
     * @param Data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magezon\CustomerApproval\Model\Email $email,
        \Magezon\CustomerApproval\Helper\Data $dataHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->email = $email;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->dataHelper->isEnabled()) {
            $type = $this->getRequest()->getParam('type');
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            foreach ($collection as $customer) {
                $customer->setIsApproved($type);
                $customer->save();
                $this->email->sendEmailToCustomer($type, $customer->getEmail(), $customer);
            }

            $status = __('Rejected');
            if ($type == ListStatus::STATUS_APPROVED) {
                $status = __('Approved');
            }
            $message = __('A total of %1 record(s) have been %2.', $collection->count(), $status);

            $this->messageManager->addSuccessMessage($message);

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        }

        return $resultRedirect->setPath('customer');
    }
}