<?php
/*
 * @Author: Warley Elias
 * @Email: warley.elias@pentagrama.com.br
 * @Date: 2023-06-01 12:21:33
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-06-01 15:28:36
 */

class Pagaleve_Pix_Model_Payment_Upfront extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'pagaleve_upfront';
    protected $_formBlockType = 'Pagaleve_Pix/upfront_form';
    protected $_infoBlockType = 'Pagaleve_Pix/upfront_info';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canManageRecurringProfiles = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $this->_place($payment);
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function _place(Mage_Sales_Model_Order_Payment $payment)
    {
        try {
            //Make PagaLeve Checkout
            $_order = $payment->getOrder();
            $_pagaleveCheckout = Mage::getModel('Pagaleve_Pix/request_checkout');
            $transaction = $_pagaleveCheckout->makeCheckout($_order, true);
            Mage::log($transaction, null, 'mylog.log');

            $payment->setPagaleveCheckoutId($transaction['id']);
            $payment->setPagaleveCheckoutUrl($transaction['checkout_url']);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'pagaleve.log');
            Mage::throwException($e->getMessage());
        }

        return $this;
    }
}
