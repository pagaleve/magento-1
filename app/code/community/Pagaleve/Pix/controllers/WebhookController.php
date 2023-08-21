<?php
/*
 * @Author: Warley Elias
 * @Email: warley.elias@pentagrama.com.br
 * @Date: 2023-08-18 17:04:20
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-08-21 14:15:36
 */

class Pagaleve_Pix_WebhookController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        //get body request
        $body = $this->getRequest()->getRawBody();
        $postData = json_decode($body, true);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        if (empty($postData)) {
            Mage::log('Pagaleve: No data received', null, 'pagaleve_webhook.log');
            $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'No data received']));
            return;
        }
        if (!isset($postData['id'])) {
            Mage::log('Pagaleve: No checkout id received', null, 'pagaleve_webhook.log');
            $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'No checkout id received']));
            return;
        }
        if (!isset($postData['state'])) {
            Mage::log('Pagaleve: No state received', null, 'pagaleve_webhook.log');
            $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'No state received']));
            return;
        }

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
        $collection->addFieldToFilter('payment.pagaleve_checkout_id', $postData['id']);

        if(count($collection) > 0) {
            $_order = $collection->getFirstItem();
            $_helper = Mage::helper('Pagaleve_Pix');
            $_pagalevePayment = Mage::getModel('Pagaleve_Pix/request_payment');
            try {
                if ($postData['state'] == 'AUTHORIZED') {
                    $paymentData = $_pagalevePayment->makePayment($_order);
                    if (is_array($paymentData) && count($paymentData) > 0) {
                        $_order->getPayment()->setPagalevePaymentId($paymentData['id'])
                            ->save();
                        $_helper->makeInvoice($_order, $paymentData);
                    } else {
                        Mage::log('Pagaleve: Payment create error', null, 'pagaleve_webhook.log');
                        $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'Payment create error']));
                        return;
                    }
                } elseif ($postData['state'] == 'COMPLETED') {
                    $paymentData = $_pagalevePayment->getPayment($_order->getPagalevePaymentId());
                    if (is_array($paymentData) && count($paymentData) > 0) {
                        $_helper->makeInvoice($_order, $paymentData);
                    } else {
                        Mage::log('Pagaleve: Payment get error', null, 'pagaleve_webhook.log');
                        $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'Payment get error']));
                        return;
                    }
                } elseif ($postData['state'] == 'EXPIRED' || $postData['state'] == 'CANCELED') {
                    if ($_order->canCancel()) {
                        $_order->cancel()->save();
                    } else {
                        Mage::log('Pagaleve: Order can not be canceled', null, 'pagaleve_webhook.log');
                        $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'Order can not be canceled']));
                        return;
                    }
                }
            } catch(Exception $e) {
                Mage::log($e->getMessage(), null, 'pagaleve_webhook.log');
                $this->getResponse()->setBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
                return;
            }
        } else {
            Mage::log('Pagaleve: Order not found', null, 'pagaleve_webhook.log');
            $this->getResponse()->setBody(json_encode(['success' => false, 'message' => 'Order not found']));
            return;
        }

        //return a json response
        $this->getResponse()->setBody(json_encode(['success' => true]));
    }
}