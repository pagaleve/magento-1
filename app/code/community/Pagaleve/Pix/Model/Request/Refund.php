<?php 
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-05 12:52:21
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-01-05 14:01:25
 */

class Pagaleve_Pix_Model_Request_Refund extends Mage_Core_Model_Abstract {
    protected function prepareRequestParams($amount, $reason, $description) {
        $_helper = Mage::helper('Pagaleve_Pix');
        return [
            'amount' => $_helper->formatAmount($amount),
            'reason' => $reason,
            'description' => $description
        ];
    }

    public function makeRefund($pagalevePaymentId, $amount, $reason, $description)
    {
        $requestParams = $this->prepareRequestParams($amount, $reason, $description);
        $_pagalevePixApi = Mage::getModel('Pagaleve_Pix/api');
        return $_pagalevePixApi->makeRefund($pagalevePaymentId, $requestParams);
    }
}