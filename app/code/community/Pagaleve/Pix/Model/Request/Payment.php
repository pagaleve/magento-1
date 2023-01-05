<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 17:06:43
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-01-05 12:00:22
 */

class Pagaleve_Pix_Model_Request_Payment extends Mage_Core_Model_Abstract {
    const AUTHORIZE = 'AUTH';
    const AUTHORIZE_AND_CAPTURE = 'CAPTURE';

    protected function prepareRequestParams($_order) {
        $_helper = Mage::helper('Pagaleve_Pix');
        return [
            'amount' => $_helper->formatAmount($_order->getGrandTotal()),
            'checkout_id' => $_order->getPayment()->getPagaleveCheckoutId(),
            'currency' => 'BRL',
            //'intent' => $this->helperConfig->getPaymentAction(),
            'intent' => self::AUTHORIZE_AND_CAPTURE,
            'reference' => $_order->getIncrementId()
        ];
    }

    public function makePayment($_order) {
        $requestParams = $this->prepareRequestParams($_order);
        $_pagalevePixApi = Mage::getModel('Pagaleve_Pix/api');
        return $_pagalevePixApi->makePayment($requestParams);
    }

    public function getPayment($pagalevePaymentId) {
        $_pagalevePixApi = Mage::getModel('Pagaleve_Pix/api');
        return $_pagalevePixApi->getPaymenttData($pagalevePaymentId);
    }
}