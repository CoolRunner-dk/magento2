<?php
namespace CoolRunner\Shipping\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ranges
 */
class PriceRules extends AbstractFieldArray
{
    private $methodRenderer;
    private $conditionRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('method', [
            'label' => __('Metodekode'),
            'renderer' => $this->getMethodRenderer()
        ]);

        $this->addColumn('condition', [
            'label' => __('Betingelse'),
            'renderer' => $this->getConditionRenderer()
        ]);

        $this->addColumn('condition_from', ['label' => __('Fra'), 'class' => 'required-entry']);
        $this->addColumn('condition_to', ['label' => __('Til'), 'class' => 'required-entry']);
        $this->addColumn('price', ['label' => __('Pris'), 'class' => 'required-entry']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Tilføj prisregel');
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

        if ($columnName == "condition_from" or $columnName == "condition_to") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-number';
            $this->_columns[$columnName]['style'] = 'width:60px';
        }

        if ($columnName == "methodname") {
            $this->_columns[$columnName]['style'] = 'width:250px';
        }

        return parent::renderCellTemplate($columnName);
    }
}
