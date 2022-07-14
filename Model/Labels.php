<?php
namespace CoolRunner\Shipping\Model;
use CoolRunner\Shipping\Api\Data\LabelInterface;
use CoolRunner\Shipping\Helper\CurlData;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Labels
 * @method $this setOrderId(int $orderId)
 * @method $this setOrderIncrementId(string $incrementId)
 * @method $this setPackageNumber(string $packageNumber)
 * @method $this setPriceExclTax(float $price)
 * @method $this setPriceInclTax(float $price)
 * @method $this setUniqueId(string $pcnId)
 * @method $this setCarrier(string $code)
 * @method $this setProduct(string $code)
 * @method $this setService(string $code)
 * @method int getOrderId()
 * @method string getPackageNumber()
 * @method string getCreatedAt()
 * @method string getCarrier()
 * @method string getProduct()
 * @method string getService()
 * @method float getPriceExclTax()
 * @method float getPriceInclTax()
 *
 * @package CoolRunner\Shipping
 */
class Labels extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, LabelInterface {
    /**
     *
     */
    const CACHE_TAG = 'coolrunner_shipping_labels';

    /**
     * @var string
     */
    protected $_cacheTag = 'coolrunner_shipping_labels';

    /**
     * @var string
     */
    protected $_eventPrefix = 'coolrunner_shipping_labels';
    /**
     * @var CurlData
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_labelContent = '';

    /**
     * Labels constructor.
     *
     * @param CurlData                                           $helper
     * @param Context                                            $context
     * @param \Magento\Framework\Registry                        $registry
     * @param ResourceModel\AbstractResource|null                $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array                                              $data
     */
    public function __construct(
        CurlData $helper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_helper = $helper;
    }

    /**
     *
     */
    protected function _construct() {
        $this->_init('CoolRunner\Shipping\Model\ResourceModel\Labels');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getLabelContent() {
        if($this->getPackageNumber() && !$this->_labelContent){
            $this->_labelContent = $this->_helper->getShippingLabelContent($this->getPackageNumber());
        }
        return $this->_labelContent;
    }
}
