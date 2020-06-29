<?php
namespace CoolRunner\Shipping\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Ranges
 */
class Methods extends AbstractFieldArray
{
    private $methodRenderer;
    private $conditionRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('method', [
            'label' => __('Metodekode'),
            'renderer' => $this->getMethodRenderer()
        ]);

        $this->addColumn('methodname', ['label' => __('Metodenavn'), 'class' => 'required-entry']);
        $this->addColumn('price', ['label' => __('Pris'), 'class' => 'required-entry']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Tilføj forsendelsesmetode');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $method = $row->getMethod();
        if ($method !== null) {
            $options['option_' . $this->getMethodRenderer()->calcOptionHash($method)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getMethodRenderer()
    {
        if (!$this->methodRenderer) {
            $this->methodRenderer = $this->getLayout()->createBlock(
                MethodColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->methodRenderer;
    }

    private function getConditionRenderer()
    {
        if (!$this->conditionRenderer) {
            $this->conditionRenderer = $this->getLayout()->createBlock(
                ConditionColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->conditionRenderer;
    }

    public function renderCellTemplate($columnName)
    {
        if ($columnName == "price") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-number';
            $this->_columns[$columnName]['style'] = 'width:40px';
        }

        if ($columnName == "methodname") {
            $this->_columns[$columnName]['style'] = 'width:250px';
        }


        return parent::renderCellTemplate($columnName);
    }
}
