<?php

namespace Dyson\SinglePageCheckout\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            /** @var AdapterInterface $connection */
            $connection = $installer->getConnection();

            $quote_table = $installer->getTable('quote_address');
            $sales_order_address_table = $installer->getTable('sales_order_address');

            $column_name = 'dialcode';
            $column_definition = [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 50,
                'comment' => 'Dialcode'
            ];

            if (!$connection->tableColumnExists($quote_table, $column_name)) {
                // Add dialcode column on quote_table and sales_order_table if they
                // don't exist at all.
                $connection->addColumn($quote_table, $column_name, $column_definition);
            }
            else {
                // Just to be sure, if it does exist change it to required
                // definition (there is a reason for this due to previous
                // dialcode column declaration that predates this code change).
                $connection->changeColumn($quote_table, $column_name, $column_name, $column_definition);
            }

            // Do it again for sales order table.
            if (!$connection->tableColumnExists($sales_order_address_table, $column_name)) {
                $connection->addColumn($sales_order_address_table, $column_name, $column_definition);
            }
            else {
                $connection->changeColumn($sales_order_address_table, $column_name, $column_name, $column_definition);
            }

        }

        $installer->endSetup();
    }
}
