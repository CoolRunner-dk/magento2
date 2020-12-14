<?php
 /**
 * Create
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    15:56
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use Magento\Backend\App\Action as BackendAppAction;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Create extends BackendAppAction implements HttpGetActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';

   /**
    * @var \Magento\Backend\Model\View\Result\Forward
    */
   protected $_resultForwardFactory;

   /**
    * @param BackendAppAction\Context $context
    * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    */
   public function __construct(
       BackendAppAction\Context $context,
       \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
   ) {
       $this->_resultForwardFactory = $resultForwardFactory;
       parent::__construct($context);
   }

   /**
    * Forward to edit
    *
    * @return \Magento\Backend\Model\View\Result\Forward
    */
   public function execute() {

       /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
       $resultForward = $this->_resultForwardFactory->create();
       return $resultForward->forward('edit');
   }
}
