<?php

namespace CoolRunner\Shipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @inheritDoc
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Install coolrunner products
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('coolrunner_products')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('coolrunner_products'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Country'
                )
                ->addColumn(
                    'carrier',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Carrier'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Type'
                )
                ->addColumn(
                    'products',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'Products'
                )->setComment('CoolRunner products table');

            $installer->getConnection()->createTable($table);
        }

        // Install coolrunner labels (Not installing?)
        if (!$installer->tableExists('coolrunner_shipping_labels')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('coolrunner_shipping_labels'))
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'ordernumber',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Ordernumber'
                )
                ->addColumn(
                    'package_number',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Packagenumber from CoolRunner'
                )
                ->addColumn(
                    'link_self',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link to shipment'
                )
                ->addColumn(
                    'link_label',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link to label'
                )
                ->addColumn(
                    'link_tracking',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link for tracking'
                )
                ->addColumn(
                    'unique_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Unique id for PCN label'
                )
                ->addColumn(
                    'price_incl_tax',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Price paid for label incl tax'
                )
                ->addColumn(
                    'price_excl_tax',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Price paid for label excl tax'
                )
                ->addColumn(
                    'carrier',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Carrier for shipment'
                )
                ->addColumn(
                    'product',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    'service',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )
                ->addColumn(
                    'date',
                    Table::TYPE_TIMESTAMP,
                    255,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                )->setComment('CoolRunner shipments table');

            $installer->getConnection()->createTable($table);

        }

        $installer->endSetup();
    }
}
