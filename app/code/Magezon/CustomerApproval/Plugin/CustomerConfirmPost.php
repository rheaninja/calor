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

use Magento\Customer\Controller\Account\Confirm;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;
use Magezon\CustomerApproval\Helper\Data;

class CustomerConfirmPost
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
    public $resultFactory;

    /**
     * @var \Magezon\CustomerApproval\Helper\Data
     */
    public $dataHelper;

    /**
     * @var \Magezon\CustomerApproval\Model\Email
     */
    protected $email;
    
    /**
     * [__construct description]
     * @param \Magento\Framework\Message\ManagerInterface       $messageManager 
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect       
     * @param \Magento\Framework\Controller\ResultFactory       $resultFactory  
     * @param \Magezon\CustomerApproval\Helper\Data             $dataHelper     
     * @param \Magezon\CustomerApproval\Model\Email             $email          
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magezon\CustomerApproval\Helper\Data $dataHelper,
        \Magezon\CustomerApproval\Model\Email $email
    ) {
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->resultFactory = $resultFactory;
        $this->dataHelper = $dataHelper;
        $this->email = $email;
    }

    /**
     * @param \Magento\Contact\Controller\Index\Post $subject
     * @throws \Exception
     */
    public function afterExecute(Confirm $confirmPost, $result)
    {
        if ($this->dataHelper->isEnabled() && $this->dataHelper->isAutoApproval() == ListStatus::STATUS_REJECTED) {
            $customerId             = null;
            $request                = $confirmPost->getRequest();
            $customerId             = $request->getParam('id');
            $customer               = $this->customerSession->getCustomer();
            $emailPost              = $customer->getData('email');

            if ($customerId) {
                $customerStatus = $customer->getData(Data::IS_APPROVED);
            }

            if ($customerId && $customerStatus != ListStatus::STATUS_APPROVED) {
                if (!empty($emailPost)) {
                    $this->email->sendEmailToCustomer($customerStatus, $emailPost, $customer);
                }

                $this->messageManager->addNoticeMessage($this->dataHelper->getPendingMessage());

                $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())->setLastCustomerId($customerId);
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('customer/account/login');

                return $resultRedirect;
            }
        }

        return $result;
    }
}
