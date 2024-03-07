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
 * @package   Magezon_CustomerApproval
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\CustomerApproval\Ui\Component;

use Magento\Framework\Api\Filter;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\App\ResourceConnection;
use Magezon\CustomerApproval\Helper\Data;

class CustomerDataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param AttributeRepository $attributeRepository
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        AttributeRepository $attributeRepository,
        ResourceConnection $resourceConnection,
        Filter $Filters,
        array $meta = [],
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $attributeRepository,
            $meta,
            $data
        );

        if (isset($request->getParam('filters')[Data::IS_APPROVED])) {
            $Filters->setField(Data::IS_APPROVED);
            $Filters->setValue($request->getParam('filters')[Data::IS_APPROVED]);
            $this->addFilter($Filters);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    { 
        if ($filter->getField() == Data::IS_APPROVED) {
            $resourceConnection = $this->resourceConnection;
            $connection = $resourceConnection->getConnection();

            $tableName  = $resourceConnection->getTableName('customer_entity_int');
            $select = $connection->select() ->from(['ce' => $tableName] )->where('value = ?', $filter->getValue())->group('entity_id');
            $dataCustomers = $connection->fetchAll($select);

            $customers = [];
            foreach ($dataCustomers as $row) {
                $customers[] = $row['entity_id'];
            }

            $filter->setConditionType('in');
            $filter->setField('entity_id');
            $filter->setValue(implode(",",$customers));
            parent::addFilter($filter);
        } else {
            parent::addFilter($filter);
        }
    }
}