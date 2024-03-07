<?php

namespace Selesti\ImportOrders\Model\Order;

use Magento\Framework\DataObject;

/**
 * Class Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Processor
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Phrase\Renderer\CompositeFactory
     */
    protected $rendererCompositeFactory;

    /**
     * @var \Magento\Sales\Model\AdminOrder\CreateFactory
     */
    protected $createOrderFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory
     */
    protected $shipmentLoaderFactory;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory
     */
    protected $creditmemoLoaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var \Magento\Backend\Model\Session\QuoteFactory
     */
    protected $sessionQuoteFactory;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $currentSession;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Phrase\Renderer\CompositeFactory $rendererCompositeFactory
     * @param \Magento\Sales\Model\AdminOrder\CreateFactory $createOrderFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerFactory
     * @param \Magento\Backend\Model\Session\QuoteFactory $sessionQuoteFactory
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceManagement
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoaderFactory
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Phrase\Renderer\CompositeFactory $rendererCompositeFactory,
        \Magento\Sales\Model\AdminOrder\CreateFactory $createOrderFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerFactory,
        \Magento\Backend\Model\Session\QuoteFactory $sessionQuoteFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceManagement,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoaderFactory,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->rendererCompositeFactory = $rendererCompositeFactory;
        $this->createOrderFactory = $createOrderFactory;
        $this->customerRepository = $customerFactory;
        $this->sessionQuoteFactory = $sessionQuoteFactory;
        $this->transactionFactory = $transactionFactory;
        $this->orderFactory = $orderFactory;
        $this->invoiceManagement = $invoiceManagement;
        $this->shipmentLoaderFactory = $shipmentLoaderFactory;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->storeManager = $storeManager;
        $this->creditmemoManagement = $creditmemoManagement;
    }

    /**
     * @param array $orderData
     * @return void
     */
    public function createOrder($orderData,$connection)
    {          
       //  print_r($orderData);exit;
         if($orderData['customer_email'] == ''){
             return false;
         }
         /** @var check Customer Id $customer */
         $customer = $connection->select()->from(['c' => 'customer_entity'])
            ->where('c.email = ?', $orderData['customer_email']);
         $customerId = $connection->fetchOne($customer);
         if (!$customerId) {             
            /** Create Customer If not created **/
            $customerId = $this->createCustomer($orderData,$connection);
            
         }  
         /** @var add shipping address in customer addresses */
         $this->createCustomerAddress($orderData, $connection, $customerId);
         
         if(!$customerId || $customerId == ''){
            return false;
         }

         /** @var get default billing address */
         $defaultBillingAddress = $this->getDefaultBillingAddress($customerId,$connection);
         $defaultCustomerData = $this->getCustomerData($customerId,$connection);

         $orderData['otherData']['OrderDate'] = date('Y-m-d H:i:s',strtotime($orderData['otherData']['OrderDate']));
         /** Insert data into sales_order table **/
         if(!empty($orderData['otherData'])){      
               echo 'Creating order started for #'.$orderData['order_id']."\n";        
               
                $shipVia = $orderData['otherData']['OrderTrackString']; 
                $shipMethod  = 'flatrate_flatrate';
               
                $ordOrgData = $orderData['otherData'];
               $connection->beginTransaction();
               $orData = array();
                $state = 'complete';
                $status = 'complete';
               
               $orData['state'] = $state;
               $orData['status'] = $status;
               $orData['shipping_description'] = $shipVia; 
               $orData['store_id'] = 1; 
               $orData['customer_id'] = $customerId;
               $orData['base_grand_total'] = $ordOrgData['Total'];
               $orData['base_shipping_amount'] = '0.00';
               $orData['base_subtotal'] = $ordOrgData['Total'];
               $orData['base_tax_amount'] = '0.00';
               $orData['coupon_code'] = '';
               $orData['base_discount_amount'] = '0.00';
               $orData['discount_amount'] = '0.00';
               $orData['grand_total'] = $ordOrgData['Total'];
               $orData['shipping_amount'] = '0.00';
               $orData['subtotal'] = $ordOrgData['Total'];
               $orData['tax_amount'] = '0.00';
               $orData['total_qty_ordered'] = 1;
               $orData['customer_is_guest'] = NULL;
               $orData['customer_group_id'] = 1;
               $orData['email_sent'] = 1;
               $orData['send_email'] = 1;
               $orData['shipping_address_id'] = NULL;
               $orData['billing_address_id'] = NULL;
               $orData['weight'] = '1';
               $orData['increment_id'] = $ordOrgData['OrderNumber'];
               $orData['customer_email'] = $orderData['customer_email'];
               $orData['customer_firstname'] = $defaultCustomerData['firstname'] != '' ? $defaultCustomerData['firstname'] : $ordOrgData['FirstName'];
               $orData['customer_lastname'] = $defaultCustomerData['lastname'] != '' ? $defaultCustomerData['lastname'] : $ordOrgData['LastName'];
               $orData['global_currency_code'] = 'GBP';
               $orData['base_currency_code'] = 'GBP';
               $orData['order_currency_code'] = 'GBP';
               $orData['shipping_method'] = $shipMethod;
               $orData['store_currency_code'] = 'GBP';
               $orData['created_at'] = $ordOrgData['OrderDate'];
               $orData['updated_at'] = $ordOrgData['OrderDate'];
               // $orData['source_code'] = 'default';               
               $connection->insert('sales_order', $orData);
               $connection->commit();


               $order = $connection->select()->from(['o' => 'sales_order'])
                            ->where('o.increment_id = ?', $ordOrgData['OrderNumber']);
               $orderLastId = $connection->fetchOne($order);               

               /** Insert data into sales_order_grid **/ 
               $connection->beginTransaction();
               $gridData = array();
               $defaultCustomerFname = $defaultCustomerData['firstname'] != '' ? $defaultCustomerData['firstname'] : ($defaultBillingAddress['firstname'] != '' ? $defaultBillingAddress['firstname'] : $ordOrgData['FirstName']);
               $defaultCustomerLname = $defaultCustomerData['lastname'] != '' ? $defaultCustomerData['lastname'] : ($defaultBillingAddress['lastname'] != '' ? $defaultBillingAddress['lastname'] : $ordOrgData['LastName']);
               $billfname = $defaultBillingAddress['firstname'] != '' ? $defaultBillingAddress['firstname'] : $ordOrgData['FirstName'];
               $billlname = $defaultBillingAddress['lastname'] != '' ? $defaultBillingAddress['lastname'] : $ordOrgData['LastName'];
               $shipfname = $ordOrgData['FirstName'] != '' ? $ordOrgData['FirstName'] : $ordOrgData['FirstName'];
               $shiplname = $ordOrgData['LastName'] != '' ? $ordOrgData['LastName'] : $ordOrgData['LastName'];
               $gridData['entity_id'] = $orderLastId;
               $gridData['status'] = 'complete';
               $gridData['store_id'] = 1;
               $gridData['customer_id'] = $customerId;
               $gridData['base_grand_total'] = $ordOrgData['Total'];
               $gridData['base_total_paid'] = $ordOrgData['Total'];
               $gridData['grand_total'] = $ordOrgData['Total'];
               $gridData['total_paid'] = $ordOrgData['Total'];
               $gridData['increment_id'] = $ordOrgData['OrderNumber'];
               $gridData['base_currency_code'] = 'GBP';
               $gridData['order_currency_code'] = 'GBP';
               $gridData['shipping_name'] = $shipfname.' '.$shiplname;
               $gridData['billing_name'] = $billfname.' '.$billlname;
               $gridData['created_at'] = $ordOrgData['OrderDate'];
               $gridData['updated_at'] = $ordOrgData['OrderDate'];
               $gridData['billing_address'] = NULL;
               $gridData['shipping_address'] = NULL;
               $gridData['shipping_information'] = $shipVia;
               $gridData['customer_email'] = $orderData['customer_email'];
               $gridData['customer_group'] = 1;
               $gridData['subtotal'] = $ordOrgData['Total'];
               $gridData['customer_name'] = $defaultCustomerFname.' '.$defaultCustomerLname;
               $gridData['payment_method'] = 'No Payment Information Required'; 
               $connection->insert('sales_order_grid', $gridData);
               $connection->commit();   
         }  

         /** Insert data into sales_order_address (shipping,billing) table **/
         if($orderLastId){
            
              $shipState = $ordOrgData['County'] != '' ? $ordOrgData['County'] : $ordOrgData['County'];
              $shipcntry = 'GB';
              $shiReg = $connection->select()->from(['d' => 'directory_country_region'],['*'])
                            ->where('d.code = ?', $shipState);
              $shiReg = $connection->fetchRow($shiReg);

             /** Shipping Address **/
             $connection->beginTransaction();
             $shipData = array();
             $shipData['parent_id'] = $orderLastId;
             $shipData['region_id'] = NULL;
             $shipData['customer_id'] = $customerId;
             $shipData['region'] =  isset($shiReg['default_name']) ? $shiReg['default_name'] : $shipState;
             $shipData['postcode'] =  $ordOrgData['Postcode'] != '' ? $ordOrgData['Postcode'] : $ordOrgData['Postcode'];
             $shipData['lastname'] =  $ordOrgData['LastName'] != '' ? $ordOrgData['LastName'] : $ordOrgData['LastName'];
             $shipData['street'] =  $ordOrgData['AddressLine1'].', '.$ordOrgData['AddressLine2'].', '.$ordOrgData['AddressLine3'];
             $shipData['city'] =  $ordOrgData['City'] != '' ? $ordOrgData['City'] : $ordOrgData['City'];
             $shipData['email'] = $ordOrgData['Email'] != '' ? $ordOrgData['Email'] : $ordOrgData['Email'];
             $shipData['telephone'] =  '';
             $shipData['country_id'] =  'GB';
             $shipData['firstname'] =  $ordOrgData['FirstName'] != '' ? $ordOrgData['FirstName'] : $ordOrgData['FirstName'];
             $shipData['address_type'] = 'shipping';
             $shipData['company'] =  $ordOrgData['Company'] != '' ? $ordOrgData['Company'] : $ordOrgData['Company'];
             $connection->insert('sales_order_address', $shipData);
             $connection->commit();

             /** Billing Address **/

             $billState = $defaultBillingAddress['region'] != '' ? $defaultBillingAddress['region'] : $ordOrgData['County'];
             $billcntry = 'GB';
             $billReg = $connection->select()->from(['d' => 'directory_country_region'],['*'])
                            ->where('d.code = ?', $billState);
             $billReg = $connection->fetchRow($billReg);        

             $connection->beginTransaction();
             $billData = array();
             $billData['parent_id'] = $orderLastId;
             $billData['region_id'] = NULL;
             $billData['customer_id'] = $customerId;
             $billData['region'] =  isset($billReg['default_name']) ? $billReg['default_name'] : $billState;
             $billData['postcode'] =  $defaultBillingAddress['postcode'] != '' ? $defaultBillingAddress['postcode'] : $ordOrgData['Postcode'];
             $billData['lastname'] =  $defaultBillingAddress['lastname'] != '' ? $defaultBillingAddress['lastname'] : $ordOrgData['LastName'];
             $billData['street'] = $defaultBillingAddress['street'] != '' ? $defaultBillingAddress['street'] : $ordOrgData['AddressLine1'].', '.$ordOrgData['AddressLine2'].', '.$ordOrgData['AddressLine3'];
             $billData['city'] =  $defaultBillingAddress['city'] != '' ? $defaultBillingAddress['city'] : $ordOrgData['City'];
             $billData['email'] = $ordOrgData['Email'] != '' ? $ordOrgData['Email'] : $ordOrgData['Email'];
             $billData['telephone'] =  $defaultBillingAddress['telephone'] != '' ? $defaultBillingAddress['telephone'] : '';
             $billData['country_id'] =  'GB';
             $billData['firstname'] =  $defaultBillingAddress['firstname'] != '' ? $defaultBillingAddress['firstname'] : $ordOrgData['FirstName'];
             $billData['address_type'] = 'billing';
             $billData['company'] =  $defaultBillingAddress['company'] != '' ? $defaultBillingAddress['company'] : $ordOrgData['Company'];
             $connection->insert('sales_order_address', $billData);
             $connection->commit();
         }
         /** Insert data into sales_order_item table **/
         if(!empty($orderData['product_data'])){
                foreach($orderData['product_data'] as $item){

                    $product_entity = $connection->select()->from(['p' => 'catalog_product_entity'],'p.entity_id')
                    ->where('p.sku = ?', $item['sku']);
                    
                    $productId = $connection->fetchOne($product_entity);
                    if(!$productId || $productId == ''){
                        return false;
                    }

                    $product_entity_decimal = $connection->select()->from(['ed' => 'catalog_product_entity_decimal'],'value')
                    ->where('ed.entity_id = ? && ed.attribute_id = 77', $productId);

                    $productPrice = $connection->fetchOne($product_entity_decimal);

                        $connection->beginTransaction();
                        $itemData = array();
                        $itemData['order_id'] = $orderLastId;
                        $itemData['store_id'] = 1;
                        $itemData['created_at'] = $ordOrgData['OrderDate'];
                        $itemData['updated_at'] = $ordOrgData['OrderDate'];
                        $itemData['product_id'] = NULL;
                        $itemData['product_type'] = 'simple';
                        $itemData['weight'] = $item['weight'];
                        $itemData['sku'] = $item['sku'];
                        $itemData['name'] = $item['name'];
                        $itemData['qty_ordered'] = $item['qty'];
                        $itemData['price'] = $productPrice;
                        $itemData['base_price'] = $productPrice;
                        $itemData['price_incl_tax'] = $productPrice;
                        $itemData['base_price_incl_tax'] = $productPrice;
                        $itemData['original_price'] = $productPrice;
                        $itemData['base_original_price'] = $productPrice;
                        $itemData['row_total'] = $item['qty'] * $productPrice;
                        $itemData['base_row_total'] = $item['qty'] * $productPrice;
                        $itemData['row_total_incl_tax'] = $item['qty'] * $productPrice;
                        $itemData['base_row_total_incl_tax'] = $item['qty'] * $productPrice;
                        $connection->insert('sales_order_item', $itemData);
                        $connection->commit();
                }
         }

         /** Insert data into sales_order_payment table **/
         if($orderLastId){
                $connection->beginTransaction();
                $payData = array(); 
                $payData['parent_id'] = $orderLastId;
                $payData['base_shipping_captured'] = 0.00;
                $payData['shipping_captured'] = 0.00;
                $payData['base_amount_paid'] = $ordOrgData['Total'];
                $payData['base_amount_authorized'] = $ordOrgData['Total'];
                $payData['base_amount_paid_online'] = $ordOrgData['Total'];
                $payData['base_shipping_amount'] = 0.00;
                $payData['shipping_amount'] = 0.00;
                $payData['amount_paid'] = $ordOrgData['Total'];
                $payData['amount_authorized'] = $ordOrgData['Total'];
                $payData['base_amount_ordered'] = $ordOrgData['Total'];
                $payData['amount_ordered'] = $ordOrgData['Total'];
                $payData['additional_information'] = '';
                $payData['method'] = 'checkmo';
                $payData['cc_type'] = '';  
                $connection->insert('sales_order_payment', $payData);
                $connection->commit();   
                echo 'INSERTED INTO sales_order_payment - '.$ordOrgData['OrderNumber']."\n";           
         }

         echo 'Order #'.$ordOrgData['OrderNumber'].' Successfully created'."\n";
         if (!empty($orderData) && $orderLastId && $state == 'complete') {            
              /** Create Invoice **/
              echo 'Creating invoice started for #'.$ordOrgData['OrderNumber']."\n";
              $this->createInvoice($orderLastId,$connection);
              echo 'Invoice created for #'.$ordOrgData['OrderNumber'].' successfully'."\n";

              /** Create Shipment **/
              echo 'Creating shipment started for #'.$ordOrgData['OrderNumber']."\n";
              $this->createShipment($orderLastId,$connection);
              echo 'Shipment created for #'.$ordOrgData['OrderNumber'].' successfully'."\n";
              echo "\n";echo "\n";echo "\n";
         }
          
         return true;
    }

    protected function getCustomerData($customerId,$connection){
        /** get default billing address id */
        $customerEntity = $connection->select()->from(['c' => 'customer_entity'],['*'])->where('c.entity_id = ?', $customerId);

        $customerData = $connection->fetchRow($customerEntity);

        return $customerData;
    }

    protected function getDefaultBillingAddress($customerId,$connection){
        /** get default billing address id */
        $defaultCustomerEntity = $connection->select()->from(['c' => 'customer_entity'],'c.default_billing')->where('c.entity_id = ?', $customerId);

        $defaultBillingAddressId = $connection->fetchOne($defaultCustomerEntity);

        /** get default billing address */
        $defaultBillingAddress = $connection->select()->from(['ca' => 'customer_address_entity'],['*'])->where('ca.entity_id = ?', $defaultBillingAddressId);

        $defaultBillingAddressRow = $connection->fetchRow($defaultBillingAddress);

        return $defaultBillingAddressRow;
    }

    protected function createCustomerAddress($orderData,$connection,$customerId){
            
        $customerAddressData = array();
        $connection->beginTransaction();
        $customerAddressData['parent_id'] = $customerId; 
        $customerAddressData['is_active'] = 1;
        $customerAddressData['created_at'] = date('Y-m-d H:i:s',strtotime($orderData['otherData']['OrderDate']));
        $customerAddressData['city'] = $orderData['otherData']['City'];
        $customerAddressData['company'] = $orderData['otherData']['Company'];
        $customerAddressData['country_id'] = 'GB';
        $customerAddressData['firstname'] = $orderData['otherData']['FirstName'];
        $customerAddressData['lastname'] = $orderData['otherData']['LastName'];
        $customerAddressData['postcode'] = $orderData['otherData']['Postcode'];
        $customerAddressData['region']  = $orderData['otherData']['County'];
        $customerAddressData['street'] = $orderData['otherData']['AddressLine1'].', '.$orderData['otherData']['AddressLine2'].', '.$orderData['otherData']['AddressLine3'];
        $connection->insert('customer_address_entity', $customerAddressData);
        $connection->commit();
        
        echo 'CUSTOMER Address CREATED WITH EMAIL-'.$orderData['customer_email']."\n";
        return;
    }

    protected function createCustomer($orderData,$connection){ 

             /* Insert Data into customer_entity table */
             $customerData = array();
             $connection->beginTransaction();
             $customerData['website_id'] = 1;
             $customerData['email'] = $orderData['customer_email'];
             $customerData['group_id'] = 1;
             $customerData['store_id'] = 1;
             $customerData['created_at'] = date('Y-m-d H:i:s',strtotime($orderData['otherData']['OrderDate']));
             $customerData['is_active'] = 1;
             $customerData['created_in'] = 'Default Store View';
             $customerData['firstname'] = $orderData['otherData']['FirstName'] != '' ? $orderData['otherData']['FirstName'] : $orderData['otherData']['FirstName'];
            $customerData['lastname'] = $orderData['otherData']['LastName'] != '' ? $orderData['otherData']['LastName'] : $orderData['otherData']['LastName'];
             // $customerData['source_code'] = 'default';
             $connection->insert('customer_entity', $customerData);
             $connection->commit(); 

             $customer = $connection->select()->from(['c' => 'customer_entity'])->where('c.email = ?', $orderData['customer_email']);
             $customerId = $connection->fetchOne($customer);

             echo 'CUSTOMER CREATED WITH EMAIL-'.$orderData['customer_email']."\n";
	     return $customerId;
    }

    /**
     * @param int $orderId
     * @param array $invoiceData
     * @return bool | \Magento\Sales\Model\Order\Invoice
     */
    protected function createInvoice($orderId, $connection)
    {
        $orderData = $connection->select()->from(['s' => 'sales_order'])
                            ->where('s.entity_id = ?', $orderId);
        $orderData = $connection->fetchRow($orderData);
        
        $billingData = $connection->select()->from(['s' => 'sales_order_address'])
                            ->where("s.parent_id = ? && s.address_type = 'billing'", $orderId);
        $billingData = $connection->fetchRow($billingData);

               /** Insert data into sales_invoice table **/
               $invoiceData = array();
               $connection->beginTransaction();
               $invoiceData['store_id'] = 1; 
               $invoiceData['state'] = 2;
               $invoiceData['base_grand_total'] = $orderData['base_grand_total'];
               $invoiceData['base_shipping_amount'] = $orderData['base_shipping_amount'];
               $invoiceData['base_subtotal'] = $orderData['base_subtotal'];
               $invoiceData['base_tax_amount'] = $orderData['base_tax_amount'];
               $invoiceData['base_discount_amount'] = $orderData['discount_amount'];
               $invoiceData['discount_amount'] = $orderData['base_discount_amount'];
               $invoiceData['grand_total'] = $orderData['grand_total'];
               $invoiceData['shipping_amount'] = $orderData['shipping_amount'];
               $invoiceData['subtotal_incl_tax'] = $orderData['subtotal']+$orderData['base_tax_amount'];
               $invoiceData['base_subtotal_incl_tax'] = $orderData['subtotal']+$orderData['base_tax_amount'];
               $invoiceData['subtotal'] = $orderData['subtotal'];
               $invoiceData['tax_amount'] = $orderData['tax_amount'];
               $invoiceData['total_qty'] = $orderData['total_qty_ordered'];               
               $invoiceData['email_sent'] = 1;
               $invoiceData['send_email'] = 1;
               $invoiceData['order_id'] = $orderId;
               $invoiceData['increment_id'] = $orderData['increment_id'];
               $invoiceData['base_subtotal'] = $orderData['subtotal'];
               $invoiceData['store_currency_code'] = 'GBP';
               $invoiceData['order_currency_code'] = 'GBP';
               $invoiceData['base_currency_code'] = 'GBP';
               $invoiceData['global_currency_code'] = 'GBP';
               $connection->insert('sales_invoice', $invoiceData);
               $connection->commit();

               $invoiceId = $connection->select()->from(['si' => 'sales_invoice'])
                            ->where('si.order_id = ?', $orderId);
               $invoiceId = $connection->fetchOne($invoiceId);
               /** Insert data into sales_invoice_grid table **/
               $connection->beginTransaction();  
               $invoiceGridData = array();
               $invoiceGridData['entity_id'] = $invoiceId;
               $invoiceGridData['increment_id'] = $orderData['increment_id'];
               $invoiceGridData['store_id'] = 1;
               $invoiceGridData['state'] = 2;
               $invoiceGridData['order_id'] = $orderId;
               $invoiceGridData['order_increment_id'] = $orderData['increment_id'];
               $invoiceGridData['order_created_at'] = $orderData['created_at'];
               $invoiceGridData['customer_name'] = $orderData['customer_firstname'].' '.$orderData['customer_lastname'];
               $invoiceGridData['billing_name'] = $billingData['firstname'].' '.$billingData['lastname'];
               $invoiceGridData['customer_email'] = $orderData['customer_email'];
               $invoiceGridData['customer_group_id'] = $orderData['customer_group_id'];
               $invoiceGridData['store_currency_code'] = $orderData['store_currency_code'];
               $invoiceGridData['order_currency_code'] = $orderData['order_currency_code'];
               $invoiceGridData['subtotal'] = $orderData['subtotal'];
               $invoiceGridData['grand_total'] = $orderData['grand_total'];
               $invoiceGridData['base_grand_total'] = $orderData['base_grand_total'];
               $invoiceGridData['created_at'] = $orderData['created_at'];
               $invoiceGridData['updated_at'] = $orderData['updated_at'];
               $connection->insert('sales_invoice_grid', $invoiceGridData);
               $connection->commit(); 
            

		$orderItemData = $connection->select()->from(['i' => 'sales_order_item'])
		                    ->where('i.order_id = ?', $orderId);
		$orderItemData = $connection->fetchAll($orderItemData);   
		foreach($orderItemData as $item){
                        /** Insert Data into sales_invoice_item table **/
		        $connection->beginTransaction();  
                        $invoiceItemData = array();
                        $invoiceItemData['parent_id'] = $invoiceId;           
                        $invoiceItemData['product_id'] = NULL;
                        $invoiceItemData['order_item_id'] = $item['item_id'];
                        $invoiceItemData['sku'] = $item['sku'];
                        $invoiceItemData['name'] = $item['name'];
                        $invoiceItemData['qty'] = $item['qty_ordered'];
                        $invoiceItemData['price'] = $item['price'];
                        $invoiceItemData['price_incl_tax'] = $item['price'];
                        $invoiceItemData['base_price'] = $item['base_price'];
                        $invoiceItemData['base_price_incl_tax'] = $item['base_price'];
                        $invoiceItemData['row_total'] = $item['row_total'];
                        $invoiceItemData['row_total_incl_tax'] = $item['row_total'];
                        $invoiceItemData['base_row_total'] = $item['qty_ordered'] * $item['price'];
                        $invoiceItemData['base_row_total_incl_tax'] = $item['qty_ordered'] * $item['price'];
                        $connection->insert('sales_invoice_item', $invoiceItemData);
                        $connection->commit();
                        
                        /** Update Order Item Table **/
                        $connection->beginTransaction();  
                        $orderItemData = array();
                        $orderItemData['qty_invoiced'] = $item['qty_ordered'];
                        $orderItemData['row_invoiced'] = $item['row_total'];
                        $orderItemData['base_row_invoiced'] = $item['row_total'];
                        $updateItem = $connection->quoteInto('item_id =?', $item['item_id']);
                        $connection->update('sales_order_item', $orderItemData,$updateItem);
                        $connection->commit();
		}
                        /** Update Order Table **/
                        $connection->beginTransaction();  
                        $orderOrgData = array();
                        $orderOrgData['base_discount_invoiced'] = $orderData['base_discount_amount'];
                        $orderOrgData['base_shipping_invoiced'] = $orderData['base_shipping_invoiced'];
                        $orderOrgData['base_subtotal_invoiced'] = $orderData['base_subtotal_invoiced'];
                        $orderOrgData['base_total_invoiced'] = $orderData['base_subtotal'];
                        $orderOrgData['base_total_paid'] = $orderData['base_subtotal'];
                        $orderOrgData['discount_invoiced'] = $orderData['base_discount_amount'];
                        $orderOrgData['shipping_invoiced'] = $orderData['base_shipping_invoiced'];
                        $orderOrgData['subtotal_invoiced'] = $orderData['base_subtotal'];
                        $orderOrgData['total_invoiced'] = $orderData['base_grand_total'];
                        $orderOrgData['total_paid'] = $orderData['base_grand_total'];
                        $orderOrgData['total_due'] = 0;
                        $orderOrgData['base_total_due'] = 0; 
                        $updateOrder = $connection->quoteInto('entity_id =?', $orderData['entity_id']);                       
                        $connection->update('sales_order', $orderOrgData,$updateOrder);
                        $connection->commit();
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment
     * @return void
     */
    protected function createShipment($orderId, $connection)
    {
        $orderData = $connection->select()->from(['s' => 'sales_order'])
                            ->where('s.entity_id = ?', $orderId);
        $orderData = $connection->fetchRow($orderData); 

        $billingData = $connection->select()->from(['s' => 'sales_order_address'])
                            ->where("s.parent_id = ? && s.address_type = 'billing'", $orderId);
        $billingData = $connection->fetchRow($billingData);

        $shippingData = $connection->select()->from(['s' => 'sales_order_address'])
                            ->where("s.parent_id = ? && s.address_type= 'shipping'", $orderId);
        $shippingData = $connection->fetchRow($shippingData);
  
        /** Insert data into sales_shipment table **/
        $shipmentData = array();
        $connection->beginTransaction();
        $shipmentData['store_id'] = 1;
        $shipmentData['total_weight'] = '';
        $shipmentData['total_qty'] = $orderData['total_qty_ordered'];      
        $shipmentData['email_sent'] = 1;
        $shipmentData['send_email'] = 1;
        $shipmentData['order_id'] = $orderId;
        $shipmentData['customer_id'] = $orderData['customer_id'];
        $shipmentData['shipping_address_id'] = $orderData['shipping_address_id'];
        $shipmentData['billing_address_id'] = $orderData['billing_address_id'];
        $shipmentData['shipment_status'] = $orderData['status'];
        $shipmentData['increment_id'] = $orderData['increment_id'];
        $shipmentData['created_at'] = $orderData['created_at'];
        $shipmentData['updated_at'] = $orderData['updated_at'];
        $connection->insert('sales_shipment', $shipmentData);
        $connection->commit();

        $shipmentId = $connection->select()->from(['si' => 'sales_shipment'])
                            ->where('si.order_id = ?', $orderId);
        $shipmentId = $connection->fetchOne($shipmentId);
       /** Insert data into sales_shipment_grid table **/
       $connection->beginTransaction();  
       $shipmentGridData = array();
       $shipmentGridData['entity_id'] = $shipmentId;
       $shipmentGridData['increment_id'] = $orderData['increment_id'];
       $shipmentGridData['store_id'] = 1;
       $shipmentGridData['order_id'] = $orderId;
       $shipmentGridData['order_increment_id'] = $orderData['increment_id'];
       $shipmentGridData['total_qty'] = $orderData['total_qty_ordered'];      
       $shipmentGridData['order_created_at'] = $orderData['created_at'];
       $shipmentGridData['order_status'] = $orderData['status'];
       $shipmentGridData['shipment_status'] = $orderData['status'];
       $shipmentGridData['billing_name'] = $billingData['firstname'].' '.$billingData['lastname'];
       $shipmentGridData['shipping_name'] = $shippingData['firstname'].' '.$shippingData['lastname'];
       $shipmentGridData['customer_email'] = $orderData['customer_email'];
       $shipmentGridData['customer_group_id'] = $orderData['customer_group_id'];
       $shipmentGridData['shipping_information'] = $orderData['shipping_description'];
       $shipmentGridData['created_at'] = $orderData['created_at'];
       $shipmentGridData['updated_at'] = $orderData['updated_at'];
       $connection->insert('sales_shipment_grid', $shipmentGridData);
       $connection->commit(); 

       $connection->beginTransaction();
       $shipmentSourceData = array();
       $shipmentSourceData['shipment_id'] = $shipmentId;
       $shipmentSourceData['source_code'] = 'default';
       $connection->insert('inventory_shipment_source', $shipmentSourceData);
       $connection->commit();

        $orderItemData = $connection->select()->from(['i' => 'sales_order_item'])
		                    ->where('i.order_id = ?', $orderId);
	$orderItemData = $connection->fetchAll($orderItemData);   
	foreach($orderItemData as $item){
                /** Insert Data into sales_shipment_item table **/
	        $connection->beginTransaction();  
                $shipmentItemData = array();
                $shipmentItemData['parent_id'] = $shipmentId;           
                $shipmentItemData['product_id'] = NULL;
                $shipmentItemData['order_item_id'] = $item['item_id'];
                $shipmentItemData['sku'] = $item['sku'];
                $shipmentItemData['name'] = $item['name'];
                $shipmentItemData['qty'] = $item['qty_ordered'];
                $shipmentItemData['price'] = $item['price']; 
                $shipmentItemData['weight'] = $item['weight'];
                $connection->insert('sales_shipment_item', $shipmentItemData);
                $connection->commit();
                
                /** Update Order Item Table **/
                $connection->beginTransaction();  
                $orderItemData = array();
                $orderItemData['qty_shipped'] = $item['qty_ordered'];
                $updateItem = $connection->quoteInto('item_id =?', $item['item_id']);
                $connection->update('sales_order_item', $orderItemData,$updateItem);
                $connection->commit();
	}   

        return true;     
    }
}