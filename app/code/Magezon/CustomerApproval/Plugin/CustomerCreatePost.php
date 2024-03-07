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

use Magento\Customer\Controller\Account\CreatePost;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;
use Magezon\CustomerApproval\Helper\Data;

class CustomerCreatePost
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
     * @param \Magento\Framework\Message\ManagerInterface
     * @param \Magento\Customer\Model\Session
     * @param \Magento\Framework\App\Response\RedirectInterface
     * @param \Magento\Framework\Controller\ResultFactory
     * @param \Magezon\CustomerApproval\Helper\Data
     * @param \Magezon\CustomerApproval\Model\Email
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magezon\CustomerApproval\Helper\Data $dataHelper,
        \Magezon\CustomerApproval\Model\Email $email
    ) {
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->resultFactory = $resultFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->dataHelper = $dataHelper;
        $this->email = $email;
    }

    /**
     * @param \Magento\Contact\Controller\Index\Post $subject
     * @throws \Exception
     */
    public function afterExecute(CreatePost $createPost, $result)
    {
        $customer = $this->customerSession->getCustomer();
        if ($this->dataHelper->isEnabled() && $this->dataHelper->isAutoApproval() == ListStatus::STATUS_REJECTED) {
            $customerId             = null;
            $request                = $createPost->getRequest();
            $name                   = $request->getParam('firstname');
            $emailPost              = $request->getParam('email');
            $emailAdmin             = $this->dataHelper->getEmailAdmin();

            if ($emailPost) {
                $customerId = $customer->getId();
            }

            if (!empty($emailAdmin)) {
                $this->email->sendEmailToAdmin($emailPost);
            }

            $this->messageManager->addNoticeMessage($this->dataHelper->getPendingMessage());

            if ($customerId) {
                if (!empty($emailPost)) {
                    $this->email->sendEmailToCustomer(ListStatus::STATUS_PENDING, $emailPost, $customer);
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
