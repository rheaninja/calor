<?php

namespace Selesti\ImportOrders\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Order
 */
class Import
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var Order\Converter
     */
    protected $converter;

    /**
     * @var Order\Processor
     */
    protected $orderProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected $_dir;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param Order\Converter $converter
     * @param Order\Processor $orderProcessor
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        Order\Converter $converter,
        Order\Processor $orderProcessor,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->converter = $converter;
        $this->orderProcessor = $orderProcessor;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->fileDriver = $fileDriver;
        $this->_dir = $dir;
    }

    /**
     * Check file is exist or not at specific location
     */
    public function checkFileExists($file)
    {
        $fileName = $this->_dir->getRoot().'/pub/selesti/importorders/'.$file;
        if ($this->fileDriver->isExists($fileName)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function execute($file,$connection)
    {   
        
        if ($this->checkFileExists($file)) {
            
            $fileRead = $this->_dir->getRoot().'/pub/selesti/importorders/'.$file;
            
            $rows = $this->csvReader->getData($fileRead);
            
            $header = array_shift($rows);
            // $rows = $connection->fetchAll('select * from OrderDataOct162017');
            
            $isFirst = true;
            $orderData= array();
            
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;   
                $orderData[$row['OrderNumber']][] = $row;             
            }
            // foreach ($rows as $row) {
            //     $orderData[$row['OrderNumber']][] = $row;             
            // }

            
            $finalOrderData = array();
            foreach($orderData as $key => $oData){
                $finalOrderData['order_id'] = $key;                
                $finalOrderData['product_data'] = array();
                $finalOrderData['customer_email'] = '';
                foreach($oData as $iData){
                     $finalOrderData['product_data'][] = array('sku' => $iData['ProductCode'],'qty' => $iData['Qty'],'name' => $iData['ProductName'],'price' => $iData['Total'],'weight' => 1,'default_price' => $iData['Total']);
                     $finalOrderData['customer_email'] = $iData['Email'] != '' ? $iData['Email'] : $iData['Email'];
                     $finalOrderData['otherData'] = $iData;
                }
                $orderLastId = '';
                $order = $connection->select()->from(['o' => 'sales_order'])
		                    ->where('o.increment_id = ?', $key);
		        $orderLastId = $connection->fetchOne($order); 
                if($orderLastId || $finalOrderData['otherData']['OrderTrackString'] == ''){
                   continue;
                }
                $this->orderProcessor->createOrder($finalOrderData,$connection);
            }
            
        }
        return true;
    }
}