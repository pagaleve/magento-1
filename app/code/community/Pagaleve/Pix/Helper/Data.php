<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   payment
 * @package    Multikomerce_Redecard
 * @copyright  Copyright (c) 2011 MagentoNet (www.magento.net.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     MagentoNet <contato@magento.net.br>
 */

class Pagaleve_Pix_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getVersion() {
        return (string) Mage::getConfig()->getNode()->modules->Pagaleve_Pix->version;
    }

    public function formatAmount($amount) {
        return $this->onlyNumbers(round($amount, 2) * 100);
    }

    public function onlyNumbers($string) {
        return (int) preg_replace('/[^0-9]/', '', $string);
    }

    public function formatPhone($phone) {
        $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
        return $formattedPhone;
    }

    protected function getCaptureIdByPaymentData($paymentData) {
        if (
            isset($paymentData['authorization']['captures'])
            && count($paymentData['authorization']['captures']) >= 1
        ) {
            $result = reset($paymentData['authorization']['captures']);
            return $result['id'] ?? '';
        }
        return '';
    }

    public function makeInvoice($_order, $paymentData) {
        $invoice = Mage::getModel('sales/service_order', $_order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $_order->setCustomerNoteNotify(false);
        $_order->setIsInProcess(true);

        $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
        $_order->setState($orderState, $orderState, '', true);
        
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
        
        $invoice->sendEmail(true, '');

        $captureId = $this->getCaptureIdByPaymentData($paymentData);
        $payment = $_order->getPayment();
        if (isset($paymentData['id']) && $paymentData['id']) {
            $payment->setPagalevePaymentId($paymentData['id']);
            if ($captureId) {
                $payment->setPagaleveCaptureId($captureId);
            }
            if (isset($paymentData['authorization']['expiration'])) {
                $expirationDate = date('Y-m-d H:i:s', strtotime($paymentData['authorization']['expiration']));
                $payment->setPagaleveExpirationDate($expirationDate);
            }
            $payment->save();
        }
        $_order->save();
    }
}
