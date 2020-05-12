<?php
declare(strict_types=1);

namespace CoolRunner\Shipping\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class MethodColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['value' => 'testproduct1', 'label' => 'Test produkt 1'],
            ['value' => 'testproduct2', 'label' => 'Test produkt 2']
        ];
    }
}
