<?php
/**
 *  LabelRepository
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    05.08.2020
 * Time:    22:28
 */
namespace CoolRunner\Shipping\Model;

use CoolRunner\Shipping\Api\DroppointManagementInterface;
use CoolRunner\Shipping\Helper\CurlData;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use CoolRunner\Shipping\Model\ResourceModel\Labels as ResourceLabel;
use CoolRunner\Shipping\Model\ResourceModel\Labels\Collection as LabelsCollection;
use CoolRunner\Shipping\Model\ResourceModel\Labels\CollectionFactory as LabelsCollectionFactory;
/**
 * Class LabelRepository
 * https://www.rakeshjesadiya.com/get-shipment-by-order-id-magento-2/#:~:text=You%20can%20get%20the%20Shipment,Shipment%20by%20sales%20order%20id.
 *
 * @package CoolRunner\Shipping
 */
class LabelRepository {
    /**
     * @var LabelsFactory
     */
    protected $_modelFactory;
    /**
     * @var ResourceLabel
     */
    protected $_resource;
    /**
     * @var LabelsCollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var CurlData
     */
    protected $_helper;
    /**
     * @var DroppointManagementInterface
     */
    protected $_droppointManagement;

    /**
     * LabelRepository constructor.
     *
     * @param LabelsFactory                $labelsFactory
     * @param LabelsCollectionFactory      $collectionFactory
     * @param ResourceLabel                $resource
     * @param DroppointManagementInterface $droppointManagement
     * @param CurlData                     $helper
     */
    public function __construct(
        LabelsFactory $labelsFactory,
        LabelsCollectionFactory $collectionFactory,
        ResourceLabel $resource,
        DroppointManagementInterface $droppointManagement,
        CurlData $helper
    ) {
        $this->_modelFactory = $labelsFactory;
        $this->_resource     = $resource;
        $this->_collectionFactory = $collectionFactory;
        $this->_helper = $helper;
        $this->_droppointManagement = $droppointManagement;
    }

    /**
     * @param Labels $model
     *
     * @return Labels
     * @throws CouldNotSaveException
     */
    public function save(Labels $model){
        try {
            $this->_resource->save($model);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $model;
    }

    /**
     * Delete label
     *
     * @param Labels $model
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Labels $model) {
        try {
            $this->_resource->delete($model);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id) {
        return $this->delete($this->getById($id));
    }

    /**
     * @param $id
     *
     * @return Labels
     * @throws NoSuchEntityException
     */
    public function getById($id) {
        $label = $this->_modelFactory->create();
        $this->_resource->load($label, $id);
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('The label with the "%1" ID doesn\'t exist.', $id));
        }
        return $label;
    }

    /**
     * @return Labels
     */
    public function getModelObject(){
        return $this->_modelFactory->create();
    }

    /**
     * @return LabelsCollection
     */
    public function getCollectionObject(){
        return $this->_collectionFactory->create();
    }


    /**
     * @param $shipmentData
     *
     * @return Labels
     * @throws LocalizedException
     */
    public function generatePcnLabelByData($shipmentData){
        $orderId            = $shipmentData['order_id'];
        $orderIncrementId   = $shipmentData['order_increment_id'];

        unset($shipmentData['order_id'],$shipmentData['order_increment_id']);
        $responseData = $this->_helper->generatePcnShipment($shipmentData);

        /** @var Labels $_labelModel */
        $_labelModel = $this->getModelObject();
        if (isset($responseData['shipment_id'])) {
            try {

                $_labelModel->setOrderId($orderId)
                    ->setOrderIncrementId($orderIncrementId)
                    ->setPackageNumber($responseData['package_number'])
                    ->setUniqueId($responseData['unique_id'])
                    ->setPriceInclTax(0)
                    ->setPriceExclTax(0)
                    ->setCarrier($shipmentData['carrier'])
                    ->setProduct($shipmentData['carrier_product'])
                    ->setService($shipmentData['carrier_service']);
                $this->save($_labelModel);
            }
            catch (CouldNotSaveException $e) {
                throw new LocalizedException(__('Something goes wrong. Cannot save label - %1',$e->getMessage()));
            }
        } else {
            // \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($responseData, true));
            throw new LocalizedException(__('Something goes wrong. Cannot create label on coolrunner.dk - %1',$responseData['message']));
        }
        return $_labelModel;
    }

    /**
     * @param array $shipmentData
     *
     * @return Labels
     * @throws LocalizedException
     */
    public function generateNormalLabelByData($shipmentData){
        $orderId            = $shipmentData['order_id'];
        $orderIncrementId   = $shipmentData['order_increment_id'];

        unset($shipmentData['order_id'],$shipmentData['order_increment_id']);
        $responseData = $this->_helper->generateNormalShipment($shipmentData);

        /** @var Labels $_labelModel */
        $_labelModel = $this->getModelObject();
        if (isset($responseData['package_number'])) {
            try {
                $_labelModel->setOrderId($orderId)
                    ->setOrderIncrementId($orderIncrementId)
                    ->setPackageNumber($responseData['package_number'])
                    ->setPriceInclTax($responseData['price']['incl_tax'])
                    ->setPriceExclTax($responseData['price']['excl_tax'])
                    ->setCarrier($responseData['carrier'])
                    ->setProduct($responseData['carrier_product'])
                    ->setService($responseData['carrier_service']);
                $this->save($_labelModel);
            }
            catch (CouldNotSaveException $e) {
                throw new LocalizedException(__('Something goes wrong. Cannot save label - %1',$e->getMessage()));
            }
        } else {
            // \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($responseData, true));
            throw new LocalizedException(__('Something goes wrong. Cannot create label on coolrunner.dk - %1',$responseData['message']));
        }
        return $_labelModel;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param string                        $type
     *
     * @return array
     */
    public function prepareShipmentDataByRequest($request,$type = 'normal'){

        /** @var \Magento\Sales\Model\Order\Shipment $orderShipment */
        $orderShipment      = $request->getOrderShipment();
        $order              = $orderShipment->getOrder();
        $shipmentMethodData = $this->_helper->explodeShippingMethod($request->getCarrierCode().'_'.$request->getShippingMethod());
        $droppointId        = $order->getShippingAddress()->getShippingCoolrunnerPickupId();

        if ($type == 'normal') {
            $shipmentData = [
                "sender" => [
                    "name"      => $request->getShipperContactCompanyName(),
                    "attention" => "",
                    "street1"   => $request->getShipperAddressStreet1(),
                    "street2"   => $request->getShipperAddressStreet2(),
                    "zip_code"  => $request->getShipperAddressPostalCode(),
                    "city"      => $request->getShipperAddressCity(),
                    "country"   => $request->getShipperAddressCountryCode(),
                    "phone"     => $request->getShipperContactPhoneNumber(),
                    "email"     => $request->getShipperEmail()
                ],
                "receiver" => [
                    "name"          => $request->getRecipientContactPersonName(),
                    "attention"     => "",
                    "street1"       => $request->getRecipientAddressStreet1(),
                    "street2"       => $request->getRecipientAddressStreet2(),
                    "zip_code"      => $request->getRecipientAddressPostalCode(),
                    "city"          => $request->getRecipientAddressCity(),
                    "country"       => $request->getRecipientAddressCountryCode(),
                    "phone"         => $request->getRecipientContactPhoneNumber(),
                    "email"         => $request->getRecipientEmail(),
                    "notify_sms"    => $request->getRecipientContactPhoneNumber(),
                    "notify_email"  => $request->getRecipientEmail()
                ],
                "length" => $request->getPackageParams()->getLength()?:"15",
                "width" =>  $request->getPackageParams()->getWidth()?:"15",
                "height" => $request->getPackageParams()->getHeight()?:"6",
                "weight" => $this->_helper->convertWeightToGrams($request->getPackageParams()->getWeight()),
                "carrier" => $shipmentMethodData['carrier'],
                "carrier_product" => $shipmentMethodData['product'],
                "carrier_service" => $shipmentMethodData['service']?? "",
                "reference" => $order->getRealOrderId(),
                "description" => "",
                "comment" => "",
                "label_format" => $this->_helper->getPrintFormat($order->getStoreId()),
                "servicepoint_id" => $droppointId ?? 0
            ];
        }elseif ($type == 'pcn'){

            $orderLines = [];
            foreach ($request->getPackageItems() as $_itemId => $_item) {
                $orderLines[] = ['item_number' => $orderShipment->getItemById($_itemId)->getSku(), 'qty' => number_format($_item['qty'], 0)];
            }

            $shipmentData = [
                "order_number"          => $order->getRealOrderId(),
                "receiver_name"         => $request->getRecipientContactPersonName(),
                "receiver_attention"    => "",
                "receiver_street1"      => $request->getRecipientAddressStreet1(),
                "receiver_street2"      => $request->getRecipientAddressStreet2(),
                "receiver_zipcode"      => $request->getRecipientAddressPostalCode(),
                "receiver_city"         => $request->getRecipientAddressCity(),
                "receiver_country"      => $request->getRecipientAddressCountryCode(),
                "receiver_phone"        => $request->getRecipientContactPhoneNumber(),
                "receiver_email"        => $request->getRecipientEmail(),
                "receiver_notify_sms"   => $request->getRecipientContactPhoneNumber(),
                "receiver_notify_email" => $request->getRecipientEmail(),
                "carrier"               => $shipmentMethodData['carrier'],
                "carrier_product"       => $shipmentMethodData['product'],
                "carrier_service"       => $shipmentMethodData['service']?? "",
                "reference"             => $order->getRealOrderId(),
                "description"           => "",
                "comment"               => "",
                "order_lines"           => $orderLines
            ];

            if($droppointId > 0){
                $droppoint = $this->_droppointManagement->fetchDroppointById($request->getCarrierCode(),$droppointId);
                $shipmentData = array_merge($shipmentData,[
                    "droppoint_id"          => $droppoint->getId()?? 0,
                    "droppoint_name"        => $droppoint->getName()?? "",
                    "droppoint_street1"     => $droppoint->getStreet() ?? "",
                    "droppoint_zipcode"     => $droppoint->getZipCode() ?? "",
                    "droppoint_city"        => $droppoint->getCity() ?? "",
                    "droppoint_country"     => $droppoint->getCountryCode() ?? ""
                ]);
            }

        }
        $shipmentData['order_id'] = $order->getId();
        $shipmentData['order_increment_id'] = $order->getRealOrderId();
        return $shipmentData;
    }
}
