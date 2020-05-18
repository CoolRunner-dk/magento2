<?php

namespace CoolRunner\Shipping\Setup;

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
        $table = $setup->getConnection()
            ->newTable($setup->getTable('coolrunner_products'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'carrier',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Carrier'
            )
            ->addColumn(
                'products',
                Table::TYPE_TEXT,
                '2M',
                ['nullable' => false, 'default' => ''],
                'Products'
            )->setComment('CoolRunner products table');

        $setup->getConnection()->createTable($table);
    }
}
