<?php
/**
 * @Author: Warley Elias
 * @Date:   2022-01-13 11:24:19
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-03 16:18:41
 */

class Pagaleve_Pix_Block_Checkout_Success_Payment_Default extends Mage_Core_Block_Template {
	public function setPayment(Varien_Object $payment) {
		$this->setData('payment', $payment);
		return $this;
	}

	public function getPayment() {
		return $this->_getData('payment');
	}

	public function getOrder() {
		return $this->getPayment()->getOrder();
	}
}