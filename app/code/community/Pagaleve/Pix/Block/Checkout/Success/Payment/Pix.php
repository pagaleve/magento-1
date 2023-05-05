<?php

/**
 * @Author: Warley Elias
 * @Date:   2021-01-12 17:29:10
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-03 17:07:23
 */

class Pagaleve_Pix_Block_Checkout_Success_Payment_Pix extends Pagaleve_Pix_Block_Checkout_Success_Payment_Default {
	public function isTransparentCheckoutEnabled() {
		$helper = Mage::helper('Pagaleve_Pix');
		return $helper->isTransparentCheckoutEnabled();
	}

	public function getMethodCode() {
		return $this->getPayment()->getMethod();
	}

	public function getRetrieveAbandonedCartUrl($orderId) {
		return Mage::getUrl('pagaleve/checkout/abandon', ['orderId' => $orderId]);
	}
}