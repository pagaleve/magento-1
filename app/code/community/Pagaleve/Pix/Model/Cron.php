<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-05 11:38:09
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-06-01 13:58:11
 */

class Pagaleve_Pix_Model_Cron {
    const CONFIG_PAGALEVE_STATUS_NEW = 'payment/Pagaleve_Pix/order_status';
    const CONFIG_PAGALEVE_RETRY_DEADLINE = 'payment/Pagaleve_Pix/retry_deadline';

    const CONFIG_PAGALEVE_UPFRONT_STATUS_NEW = 'payment/pagaleve_upfront/order_status';

    protected function getConfigData($path, $storeId = null) {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig($path, $storeId);
    }

    protected function getOrderCollection() {
        $paymentCode = ['Pagaleve_Pix', 'pagaleve_upfront'];
        $status = [$this->getConfigData(self::CONFIG_PAGALEVE_STATUS_NEW), $this->getConfigData(self::CONFIG_PAGALEVE_UPFRONT_STATUS_NEW)];
        $deadLine = $this->getConfigData(self::CONFIG_PAGALEVE_RETRY_DEADLINE);
        $toDate = date('Y-m-d H:i:s');
        $fromDate = date('Y-m-d H:i:s', strtotime('- ' . $deadLine . ' days'));
        $collection = Mage::getModel('sales/order')->getCollection()
        ->join(
            ['payment' => 'sales/order_payment'],
            'main_table.entity_id=payment.parent_id',
            [
                'payment_method' => 'payment.method',
                'pagaleve_checkout_id' => 'pagaleve_checkout_id',
                'pagaleve_payment_id' => 'pagaleve_payment_id'
            ]
        );
        $collection->addFieldToFilter('payment.method', ['in' => $paymentCode]);
        $collection->addFieldToFilter('created_at',['from' => $fromDate, 'to' => $toDate]);
        $collection->addAttributeToFilter('status', ['in' => $status]);
        //Mage::depura((string)$collection->getSelect());
        return $collection;
    }

    public function execute() {
        $_helper = Mage::helper('Pagaleve_Pix');
        $_pagaleveCheckout = Mage::getModel('Pagaleve_Pix/request_checkout');
        $_pagalevePayment = Mage::getModel('Pagaleve_Pix/request_payment');
        $collection = $this->getOrderCollection();
        
        foreach ($collection as $_order) {
            try {
                if($_order->getPagaleveCheckoutId()) {
                    $checkoutData = $_pagaleveCheckout->getCheckout($_order->getPagaleveCheckoutId());
                    if (is_array($checkoutData) && isset($checkoutData['state'])) {
                        if ($checkoutData['state'] == 'AUTHORIZED') {
                            $paymentData = $_pagalevePayment->makePayment($_order);
                            if (is_array($paymentData) && count($paymentData) > 0) {
                                $_order->getPayment()->setPagalevePaymentId($paymentData['id'])
                                    ->save();
                                $_helper->makeInvoice($_order, $paymentData);
                            }
                        } elseif ($checkoutData['state'] == 'COMPLETED') {
                            $paymentData = $_pagalevePayment->getPayment($_order->getPagalevePaymentId());
                            if (is_array($paymentData) && count($paymentData) > 0) {
                                $_helper->makeInvoice($_order, $paymentData);
                            }
                        } elseif ($checkoutData['state'] == 'EXPIRED' || $checkoutData['state'] == 'CANCELED') {
                            if ($_order->canCancel()) {
                                $_order->cancel()->save();
                            }
                        }
                    }
                }
            } catch(Exception $e) {
                Mage::log($e->getMessage(), null, 'pagaleve.log');
            }
        }
    }
}