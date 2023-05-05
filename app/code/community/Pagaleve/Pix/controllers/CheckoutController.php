<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 16:43:56
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-04 16:37:37
 */

class Pagaleve_Pix_CheckoutController extends Mage_Core_Controller_Front_Action {
    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    public function approveAction() {
        try {
            $_pagaleveCheckout = Mage::getModel('Pagaleve_Pix/request_checkout');
            $_pagalevePayment = Mage::getModel('Pagaleve_Pix/request_payment');
            $_helper = Mage::helper('Pagaleve_Pix');
            $session = $this->getOnepage()->getCheckout();
            $order = $session->getLastRealOrder();
            if ($order) {
                $payment = $order->getPayment();
                $checkoutData = $_pagaleveCheckout->getCheckout($payment->getPagaleveCheckoutId());
                if (is_array($checkoutData) && isset($checkoutData['state'])) {
                    if ($checkoutData['state'] == 'AUTHORIZED') {
                        $paymentData = $_pagalevePayment->makePayment($order);
                        if (is_array($paymentData) && count($paymentData) > 0) {
                            $payment->setPagalevePaymentId($paymentData['id'])
                                ->save();
                            $_helper->makeInvoice($order, $paymentData);
                        }
                    }
                }
            }
        } catch(Exception $e) {
            Mage::log($e->getMessage(), null, 'pagaleve.log');
        }
        $this->_redirect('checkout/onepage/success?passthrough=true');
    }

    public function cancelAction() {
        $this->_redirect('/');
    }

    public function abandonAction() {
        $orderId = $this->getRequest()->getParam('orderId');
        //create a new cart using an order
        $order = Mage::getModel('sales/order')->load($orderId);
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $quote->setIsActive(1)->save();
        Mage::getSingleton('checkout/session')->replaceQuote($quote);
        $this->_redirect('checkout/onepage');
    }
}