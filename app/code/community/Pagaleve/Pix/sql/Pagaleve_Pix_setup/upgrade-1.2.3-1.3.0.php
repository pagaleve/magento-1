<?php
/*
 * @Author: Warley Elias
 * @Email: warley.elias@pentagrama.com.br
 * @Date: 2023-06-01 15:37:01
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-06-01 15:51:07
 */


$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('sales/order_payment');
$connection = $installer->getConnection();

$connection->modifyColumn(
    $tableName,
    'pagaleve_checkout_url',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
    )
);

$installer->endSetup();