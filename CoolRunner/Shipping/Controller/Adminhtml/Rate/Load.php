<?php
/**
 *  Load
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    15:04
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use CoolRunner\Shipping\Helper\CurlData;
use CoolRunner\Shipping\Model\RateRepository;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Load
 *
 * @package CoolRunner\Shipping\Controller\Adminhtml\Rate
 */
class Load extends BackendAppAction implements HttpPostActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';
    /**
     * @var CurlData
     */
    protected $_helper;
    /**
     * @var RateRepository
     */
    protected $_rateRepository;

    /**
     * Load constructor.
     *
     * @param BackendAppAction\Context $context
     * @param CurlData                 $helper
     * @param RateRepository           $rateRepository
     */
    public function __construct(BackendAppAction\Context $context, CurlData $helper, RateRepository $rateRepository) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_rateRepository = $rateRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute() {
        /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if(isset($data['country_id']) && isset($data['carrier_code'])) {
            $countryId   = $data['country_id'];
            $carrierCode = $data['carrier_code'];
            $methodCode  = $data['method'];
            $response = $this->_helper->getRates($countryId);
            $countOfRates = 0;
            $sortOrder    = 1;
            if(isset($response[$countryId]) && isset($response[$countryId][$carrierCode])){
                //var_dump($response[$countryId][$carrierCode]);
                foreach($response[$countryId][$carrierCode] as $method => $methodRates){
                    if($methodCode && $methodCode!=$method){ continue; }
                    $sortOrder++;
                    foreach($methodRates as $_rate){
                        $rateModel = $this->_rateRepository->getModelObject();
                        $rateModel->setTitle($_rate['title'])
                            ->setCarrier($carrierCode)
                            ->setMethod($method)
                            ->setService($_rate['services'][0]['code']??'')
                            ->setDestCountryId($countryId)
                            ->setIsActive(false)
                            ->setConditionName('package_weight')
                            ->setConditionFrom($_rate['weight']['from'])
                            ->setConditionTo($_rate['weight']['to'])
                            ->setMaxHeight($_rate['max_size']['height'])
                            ->setMaxWidth($_rate['max_size']['width'])
                            ->setMaxLength($_rate['max_size']['length'])
                            ->setPrice($_rate['prices']['incl_tax'])
                            ->setCost($_rate['prices']['incl_tax'])
                            ->setIsAutoloaded(true)
                            ->setSortOrder($sortOrder);

                        try {
                            $this->_rateRepository->save($rateModel);
                            $countOfRates++;
                        }
                        catch (CouldNotSaveException $e){
                            $this->messageManager->addErrorMessage(__('Something goes wrong. Cannot save Rate - %1',$e->getMessage()));
                        }
                    }
                }
            }
            if($countOfRates > 0){
                $this->messageManager->addSuccessMessage(__('Rates were succesfully imported.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->messageManager->addErrorMessage(__('There are no rates.'));
        return $resultRedirect->setPath('*/*/loadForm');
    }
}
