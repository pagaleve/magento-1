<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 16:29:12
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-01-04 16:37:34
 */

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Pagaleve_Pix_Checkout_OnepageController extends Mage_Checkout_OnepageController {
    public function successAction() {
        $session = $this->getOnepage()->getCheckout();
        $order = $session->getLastRealOrder();
        $passthroug = $this->getRequest()->getParam('passthrough');
        if ($order->getPayment()->getMethod() == 'Pagaleve_Pix' && !$passthroug) {
            //Redirect to Pagaleve
            $pagaleveCheckoutUrl = $order->getPayment()->getPagaleveCheckoutUrl();
            $this->_redirectUrl($pagaleveCheckoutUrl);
            return;
        }
        return parent::successAction();
    }
}