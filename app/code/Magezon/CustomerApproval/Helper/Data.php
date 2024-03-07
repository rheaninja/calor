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

namespace Magezon\CustomerApproval\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_APPROVED = 'is_approved';
    const SELECT_TYPE = 'select';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magezon\CustomerApproval\Model\Attribute\Source\ListStatus
     */
    protected $listStatus;

    /**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magezon\CustomerApproval\Model\Attribute\Source\ListStatus
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magezon\CustomerApproval\Model\Attribute\Source\ListStatus $listStatus
    ) {
        parent::__construct($context);
        $this->storeManager       = $storeManager;
        $this->listStatus         = $listStatus;
    }

    /**
     * @param string $key
     * @param null|int $_store
     * @return null|string
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->storeManager->getStore($store);
        $result = $this->scopeConfig->getValue('customerapproval/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store );
        return $result;
    }

    /**
     * @param $path
     * @param $param
     *
     * @return string
     */
    public function getUrl($path, $param)
    {
        return $this->_getUrl($path, $param);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isEnabled()
    {
        return $this->getConfig('general/enabled');
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isAutoApproval()
    {
        return $this->getConfig('general/auto_approval');
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getPendingMessage()
    {
        return $this->getConfig('general/pending_message') ?: __('Please give us time to verify your account.');
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getRejectedMessage()
    {
        return $this->getConfig('general/disapproval_message') ?: __('Something went wrong with your account! Please contact us for more information.');
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return string
     */
    public function getEmailAdmin($storeId = null)
    {
        return $this->getConfig('admin_email_settings/recipients', $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailTemplateAdmin($storeId = null)
    {
        return $this->getConfig('admin_email_settings/email_template', $storeId) ?: 'customerapproval_admin_email_settings_email_template';
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailTemplateApproval($storeId = null)
    {
        return $this->getConfig('customer_email_settings/approval_email_template', $storeId) ?: 'customerapproval_customer_email_settings_approval_email_template';
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailTemplateRejected($storeId = null)
    {
        return $this->getConfig('customer_email_settings/rejected_email_template', $storeId) ?: 'customerapproval_customer_email_settings_rejected_email_template';
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailTemplatePending($storeId = null)
    {
        return $this->getConfig('customer_email_settings/pending_email_template', $storeId) ?: 'customerapproval_customer_email_settings_pending_email_template';
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return string
     */
    public function getStatusLabel($status) {
        $listStatus = $this->listStatus->getAllOptions();
        $arr = array_search($status, array_column($listStatus, 'value'));

        return $listStatus[$arr]['label'];
    }
}
