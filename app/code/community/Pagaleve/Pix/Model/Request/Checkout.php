<?php
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 17:06:43
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-04 12:14:27
 */

class Pagaleve_Pix_Model_Request_Checkout extends Mage_Core_Model_Abstract
{
    protected function prepareRequestParams($_order)
    {
        $_helper = Mage::helper('Pagaleve_Pix');
        $_billingAddress = $_order->getBillingAddress();

        $content = [
            'provider' => 'MAGENTO_1',
            'metadata' => [
                'transactionId' => $_order->getIncrementId(),
                'merchantName' => $_order->getStore()->getName(),
                'version' => $_helper->getVersion()
            ],
            'order' => [
                'reference' => $_order->getIncrementId(),
                'tax' => 0,
                'amount' => $_helper->formatAmount($_order->getGrandTotal()),
            ],
            'reference' => $_order->getStore()->getName() . ' - ' . $_order->getIncrementId(),
            'shopper' => [
                'first_name' => $_billingAddress->getFirstname(),
                'last_name' => $_billingAddress->getLastname(),
                'phone' => $_helper->formatPhone($_billingAddress->getTelephone()),
                'email' => $_billingAddress->getEmail(),
                'cpf' => $_order->getCustomerTaxvat(),
                'billing_address' => [
                    'name' => $_billingAddress->getFirstname() . ' ' . $_billingAddress->getLastname(),
                    'city' => $_billingAddress->getCity(),
                    'state' => $_billingAddress->getRegionCode(),
                    'zip_code' => $_billingAddress->getPostcode(),
                    'street' => $_billingAddress->getStreet(1),
                    'number' => $_billingAddress->getStreet(2),
                    'neighborhood' => $_billingAddress->getStreet(4),
                    'complement' => $_billingAddress->getStreet(3),
                    'phone_number' => $_helper->formatPhone($_billingAddress->getTelephone())
                ]
            ],
            'webhook_url' => Mage::getUrl('pagaleve/webhook'),
            'approve_url' => Mage::getUrl('pagaleve/checkout/approve'),
            'cancel_url' => Mage::getUrl('pagaleve/checkout/cancel')
        ];

        $items = [];
        foreach ($_order->getAllVisibleItems() as $item) {
            $items[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQtyOrdered(),
                'price' => $_helper->formatAmount($item->getPrice()),
                'reference' => $item->getSku()
            ];
        }

        $content['order']['items'] = $items;

        return $content;
    }

    public function makeCheckout($_order)
    {
        $requestParams = $this->prepareRequestParams($_order);
        $_pagalevePixApi = Mage::getModel('Pagaleve_Pix/api');
        $transaction = $_pagalevePixApi->makeCheckout($requestParams);
        return $transaction;
    }

    public function getCheckout($pagaleveCheckoutId) {
        $_pagalevePixApi = Mage::getModel('Pagaleve_Pix/api');
        return $_pagalevePixApi->getCheckoutData($pagaleveCheckoutId);
    }
}
