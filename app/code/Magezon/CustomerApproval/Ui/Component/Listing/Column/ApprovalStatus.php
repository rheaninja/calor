<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Core
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\CustomerApproval\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magezon\CustomerApproval\Model\Attribute\Source\ListStatus;
use Magezon\CustomerApproval\Helper\Data;

class ApprovalStatus extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ListStatus $listStatus,
        Data $dataHelper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->listStatus = $listStatus;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $availableOptions = $this->listStatus->getAllOptions();
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[Data::IS_APPROVED]) {
                    $label = $this->dataHelper->getStatusLabel($item[Data::IS_APPROVED][0]);
                    $item[Data::IS_APPROVED] = '<span class="mgz-status mgz-status_' . $item[Data::IS_APPROVED][0] .  '">' . $label . '</span>';
                }
            }
        } 
        return $dataSource;
    }
}
