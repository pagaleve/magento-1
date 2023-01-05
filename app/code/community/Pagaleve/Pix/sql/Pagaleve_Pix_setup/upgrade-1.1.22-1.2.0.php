<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 13:17:29
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-01-05 10:49:42
 */

$installer = new Mage_Sales_Model_Resource_Setup('pagarme_setup');
$installer->startSetup();

// Order Payment
$entity = 'order_payment';
$attributes = [
    'pagaleve_checkout_id' => [
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 255
    ],
    'pagaleve_payment_id' => [
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 255
    ],
    'pagaleve_capture_id' => [
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 255
    ],
    'pagaleve_expiration_date' => ['type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP],
    'pagaleve_checkout_url' => [
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 255
    ],
    'pagaleve_refund_id' => [
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 255
    ],
];

foreach ($attributes as $attribute => $options) {
    $installer->addAttribute($entity, $attribute, $options);
}

$installer->endSetup();