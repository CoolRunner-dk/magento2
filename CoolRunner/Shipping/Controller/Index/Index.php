<?php
namespace CoolRunner\Shipping\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $_pageFactory;

    protected $_labelsFactory;

    public function __construct(Context $context, PageFactory $pageFactory, \CoolRunner\Shipping\Model\LabelsFactory $labelsFactory)
    {
        $this->_pageFactory = $pageFactory;
        $this->_labelsFactory = $labelsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        // Might be used later?
    }
}
