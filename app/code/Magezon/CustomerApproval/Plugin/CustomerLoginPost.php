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

namespace Magezon\CustomerApproval\Plugin;

use Magento\Customer\Controller\Account\LoginPost;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;
use Magezon\CustomerApproval\Helper\Data;

class CustomerLoginPost
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    public $redirect;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    public $ResultFactory;

    /**
     * @var \Magezon\CustomerApproval\Helper\Data
     */
    public $dataHelper;
    
    /**
     * @param \Magento\Framework\Message\ManagerInterface
     * @param \Magento\Customer\Model\Session
     * @param \Magento\Framework\App\Response\RedirectInterface
     * @param \Magento\Framework\Controller\ResultFactory
     * @param \Magezon\CustomerApproval\Helper\Data
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magezon\CustomerApproval\Helper\Data $dataHelper
    ) {
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->resultFactory = $resultFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Contact\Controller\Index\Post $subject
     * @throws \Exception
     */
    public function afterExecute(LoginPost $loginPost, $result)
    {
        if ($this->dataHelper->isEnabled()) {
            $customerId = null;
            $login = $loginPost->getRequest()->getPost('login');
            if (!empty($login['username'])) {
                $customer = $this->customerSession->getCustomer();
                $customerId = $customer->getId();
                $status = $customer->getData(Data::IS_APPROVED);
            }
            
            if ($status != ListStatus::STATUS_APPROVED) {
                if ($status == ListStatus::STATUS_PENDING) {
                    $this->messageManager->addNoticeMessage($this->dataHelper->getPendingMessage());
                }
                if ($status == ListStatus::STATUS_REJECTED) {
                    $this->messageManager->addNoticeMessage($this->dataHelper->getRejectedMessage());
                }
                $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())->setLastCustomerId($customerId);

                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('customer/account/login');

                return $resultRedirect;
            }
        }

        return $result;
    }
}
