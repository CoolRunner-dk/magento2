<?php
namespace CoolRunner\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    protected $urlBuilder;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, UrlInterface $urlBuilder, array $components = [], array $data = [])
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    // This function doesnt work yet
    // TODO: Add neccesary links for handling orders
    public function prepareDataSource(array $dataSource)
    {
        // Check if items is set in action
        if ($dataSource['data']['items']) {
            // Loop order and handle these
            foreach ($dataSource['data']['items'] as & $item) {
                // Check if there is an entity_id set
                if (isset($item['entity_id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['entity_id']
                                ]
                            ),
                            'label' => __('View')
                        ],
                        'create_label' => [
                            'href' => $this->urlBuilder->getUrl(
                                'coolrunner/order/createLabels',
                                [
                                    $urlEntityParamName => $item['entity_id']
                                ]
                            ),
                            'label' => __('CoolRunner: Create label')
                        ],
                        'print_label' => [
                            'href' => $this->urlBuilder->getUrl(
                                'coolrunner/order/printLabels',
                                [
                                    $urlEntityParamName => $item['entity_id']
                                ]
                            ),
                            'label' => __('CoolRunner: Print label')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
