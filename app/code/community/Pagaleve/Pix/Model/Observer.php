<?php 
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-05 11:38:51
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-06-01 15:57:06
 */

class Pagaleve_Pix_Model_Observer {
    public function refund($observer) {
        try {
            $_creditMemo = $observer->getEvent()->getCreditmemo();
            $_order = $_creditMemo->getOrder();
            $_payment = $_order->getPayment();
            //verify method code
            if ($_payment->getMethod() != 'Pagaleve_Pix' &&
                $_payment->getMethod() != 'pagaleve_upfront') {
                return;
            }

            $_pagaleveRefund = Mage::getModel('Pagaleve_Pix/request_refund');
            $refundData = $_pagaleveRefund->makeRefund(
                $_payment->getPagalevePaymentId(),
                $_creditMemo->getGrandTotal(),
                'REQUESTED_BY_CUSTOMER',
                $_creditMemo->getCommentText()
            );

            if (isset($refundData['id']) && $refundData['id']) {
                $_payment->setPagaleveRefundId($refundData['id']);
                $_payment->save();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'pagaleve.log');
            Mage::throwException($e->getMessage());
        }
    }
}