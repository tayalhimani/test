<?php
/**
 * City table creation
 * Copyright (C) 2019
 *
 * This file is part of Dyson/AmastyCheckoutExtension.
 *
 * Dyson/AmastyCheckoutExtension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


 namespace Dyson\AmastyCheckoutExtension\Setup;

 use Magento\Framework\DB\Adapter\AdapterInterface;
 use Magento\Framework\DB\Ddl\Table;
 use Magento\Framework\Setup\ModuleContextInterface;
 use Magento\Framework\Setup\SchemaSetupInterface;
 use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $table = $setup->getConnection()
            ->newTable($setup->getTable('dyson_city'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
                'Entity ID'
            )
            ->addColumn(
                'country_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                [],
                'country_code'
            )
            ->addColumn(
                'region_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [],
                'region_id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => false,'nullable' => false,'primary' => false,'unsigned' => true],
                'store_id'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'city'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('dyson_city'),
                    'country_code',
                    $setup->getTable('directory_country'),
                    'country_id'
                ),
                'country_code',
                $setup->getTable('directory_country'),
                'country_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('dyson_city'),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'city table creation'
            );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {

            /** @var AdapterInterface $connection */
            $connection = $installer->getConnection();

            $quote_table = $installer->getTable('quote_address');
            $sales_order_address_table = $installer->getTable('sales_order_address');

            $column_name = 'dialcode';
            $column_definition = [
                'type' =>  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
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
