<?php
/**
 * @Author: Warley Elias
 * @Date:   2022-01-13 11:24:19
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-03 16:18:04
 */

class Pagaleve_Pix_Block_Checkout_Success_Payment extends Mage_Core_Block_Template {
	protected $_paymentRenders = array();
    protected $_order;

    protected function _construct() {
        parent::_construct();
    }

    public function getOrder(){
        if (is_null($this->_order)) {
            $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $this->_order = Mage::getModel('sales/order')->load($lastOrderId);
        }

        return $this->_order;
    }

    public function getPayment() {
        return $this->getOrder()->getPayment();
    }

    public function addPaymentRender($type, $block, $template) {
        $this->_paymentRenders[$type] = array(
            'block'     => $block,
            'template'  => $template,
            'renderer'  => null
        );

        return $this;
    }

    public function getPaymentRenderer($type) {
        if (!isset($this->_paymentRenders[$type])) {
            return null;
        }

        if (is_null($this->_paymentRenders[$type]['renderer'])) {
            $this->_paymentRenders[$type]['renderer'] = $this->getLayout()
                ->createBlock($this->_paymentRenders[$type]['block'])
                ->setTemplate($this->_paymentRenders[$type]['template'])
                ->setRenderedBlock($this);
        }
        return $this->_paymentRenders[$type]['renderer'];
    }

    public function getPaymentHtml(Varien_Object $payment) {
        if (!$payment) {
            return '';
        }
        
        if ($block = $this->getPaymentRenderer($payment->getMethod())) {
        	$block->setPayment($payment);
        	return $block->toHtml();
        }

        return '';
    }

    protected function _toHtml() {
        return $this->getPaymentHtml($this->getPayment());
    }
}