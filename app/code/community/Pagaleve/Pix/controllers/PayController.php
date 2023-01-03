<?php

class Pagaleve_Pix_PayController extends Mage_Core_Controller_Front_Action
{
    /**
     * Pega o método principal
     */
    public function getPix()
    {
        return Mage::getSingleton('Pagaleve_Pix/checkout');
    }

    /**
     * Retorna o Checkout
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Retorna ID da store, através do ID do pedido
     *
     */
    function getOrderStoreId($orderId)
    {
        return Mage::getModel('sales/order')->load($orderId)->getStore()->getID();
    }

    //busca os dados da compra
    public function getOrder()
    {
        if ($this->_order == null) {
            $this->_order = Mage::getModel('sales/order')->load(
                Mage::getSingleton('checkout/session')->getLastOrderId()
            );
        }

        return $this->_order;
    }

    /**
     * Redireciona o cliente a Pagaleve_Pix na finalização do pedido
     *
     */
    public function redirectAction()
    {
        $session = $this->getCheckout(); //pega os dados do checkout

     //   Mage::log($session, null, 'session.log', true);

        $order = $this->getOrder(); //pega os dados da compra
       // Mage::log($order, null, 'getorder.log', true);
        $quoteId = $order->getQuoteId();
      //  Mage::log($quoteId, null, 'quoteid.log', true);
        $payment = $order->getPayment(); //pega os dados do pagamento
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $orderId = $quote->getReservedOrderId();
      //  Mage::log($orderId, null, 'orderid.log', true);

        if (empty($orderId)) {
            $orderId = $session->getLastOrderId();
        }




        //pega id da loja
        $storeId = $this->getOrderStoreId($orderId);


        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            $items[] = array(
                'name' => $item->getName(),
                'quantity' => (int)$item->getQtyOrdered(),
                'price' => round((float)number_format($item->getPrice(), 2) * 100 * ((int)$item->getQtyOrdered())),
                'reference' => $quoteId
            );
        }

       // Mage::log($items, null, 'items.log', true);

        //chama model do webservice da pagaleve
        $webservice = Mage::getModel('Pagaleve_Pix/Pix');
        //Mage::log($webservice, null, 'webserivice.log', true);

        $metadata = array(
            "transaction_id" => $quoteId,
            "merchant_name" => $order->getStoreName(),
        );

       // Mage::log($metadata, null, 'metadata.log', true);

        $orderData = array(
            "reference" => $orderId,
            "tax" => (float)number_format($order->getTaxAmount(), 2) * 100,
            "amount" => round((float)$order->getGrandTotal() * 100),
            "items" => $items
        );

       // Mage::log($orderData, null, 'metadata.log', true);
        $telephono = null;

        if(!empty($order->getBillingAddress()->getTelephone())){
            $telephono = $order->getBillingAddress()->getTelephone();
        } else {
            $telephono = $order->getBillingAddress()->getFax();
        }

      //  Mage::log($telephono, null, 'telefone.log', true);

        $shopper = array(
            "first_name" => $order->getCustomerFirstname(),
            "last_name" => $order->getCustomerLastname(),
            "phone" => $order->getBillingAddress()->getTelephone(),
            "email" => $order->getCustomerEmail(),
            "cpf" => $order->getCustomerTaxvat(),
            "billing_address" => $order->getBillingAddress(),
            "billing_address" => array(
                "name" => $order->getBillingAddress()->getFirstName(),
                "city" => $order->getBillingAddress()->getCity(),
                "state" => $order->getBillingAddress()->getRegion(),
                "zip_code" => $order->getBillingAddress()->getPostcode(),
                "neighborhood" => "",
                "number" => "",
                "complement" => "",
                "phone_number" => $telephono,
                "street" => $order->getBillingAddress()->getStreet1(),
            )
        );

      //  Mage::log($shopper, null, 'shopper.log', true);
        //$orderId = $quote->getReservedOrderId();

        $ambiente = Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente');

        if ($ambiente == 0) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_homolog');
        }
        if ($ambiente == 1) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_prod');
        }

        //Mage::log($ambiente, null, 'ambiente.log', true);

        $baseUrl = Mage::getBaseUrl();
        $approve_url = "{$baseUrl}pagaleve/pay/success?quoteId={$quoteId}";

        $cancel_url = "{$baseUrl}pagaleve/pay/failure?quoteId={$quoteId}";
        Mage::log($approve_url, null, 'approve_url.log', true);
        Mage::log($cancel_url, null, 'cancel_url.log', true);
        $autorizacao = $webservice->setTransacao(
            $access_token,
            $approve_url,
            $cancel_url,
            $metadata,
            $orderData,
            $orderId,
            $shopper,
            $baseUrl,
            "https://ve3zdjmt4h.execute-api.us-east-1.amazonaws.com/test/payments"//$webhook_url
        );
       // Mage::log($autorizacao, null, 'autorization.log', true);
        $redirectURL = json_decode($autorizacao, true);
              $this->setFlag('', 'no-dispatch', true);
        $this->getResponse()->setRedirect(
            Mage::helper('core/url')->addRequestParam(
                Mage::helper('customer')->getLoginUrl(),
                array('context' => 'checkout')
            )
        );


         // Tratado com Handel para evitar isso
        /*Mage::register('isSecureArea', 1);
        $order->cancel()->save();
        $order->delete();
        Mage::unregister('isSecureArea');*/


      //  Mage::log($redirectURL, null, 'redirectUrl.log', true);

        $this->_redirectUrl($redirectURL{'redirect_url'}, array('_secure' => true));
    }

    /**
     * Exibe informações de conclusão do pagamento
     *
     */
    public function successAction()
    {
        $session = $this->getCheckout();
        if (!$session->getLastQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $quoteId = $this->getRequest()->getParam('quoteId');
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        $orderId = $quote->getReservedOrderId();

        $order = $this->getOrder();

        //Set payment method for the quote
        $quote->getPayment()->importData(array('method' => 'Pagaleve_Pix'));
    

        $ambiente = Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente');

/*
        Mage::log($ambiente, null, 'ambiente.log', true);
        try {
        if ($ambiente == 0) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_homolog');
            Mage::log($access_token, null, 'accessToken.log', true);
            $url = "https://ve3zdjmt4h.execute-api.us-east-1.amazonaws.com/test/payments?access_token=$access_token&orderId=$quoteId&orderPaymentId=$orderId";
            Mage::log($url, null, 'newurg', true);
        }
        if ($ambiente == 1) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_prod');
            Mage::log($access_token, null, 'entreiprod.log', true);
            $url = "https://ke8lffc2u8.execute-api.us-east-1.amazonaws.com/prod2/payments?access_token=$access_token&orderId=$quoteId&orderPaymentId=$orderId";
            Mage::log($url, null, 'accessTokenProd.log', true);
        }
        } catch (Exception $e) {
            print_r($e);
            Mage::logException($e);
        }
        Mage::log($url, null, 'urlSuccess.log', true);

        $payload = json_encode(array(
            "order_id" => $lastQuoteId,
            "store_accesstoken" => $access_token,
            "url_base" => Mage::getBaseUrl()
        ));

        try {
            $curl = curl_init($url);
            // Set the CURLOPT_RETURNTRANSFER option to true
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // Set the CURLOPT_POST option to true for POST request
            curl_setopt($curl, CURLOPT_POST, true);
            // Set the re/quest data as JSON using json_encode function
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            // Set custom headers for RapidAPI Auth and Content-Type header
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            // Execute cURL request with all previous settings
            $response = curl_exec($curl);
            Mage::log($response, null, 'response_success.log', true);
            // Close cURL session
            curl_close($curl);
        } catch (curl_error $fault) {

            return false;
        }

        $responseJson = json_decode($response, true);


        if ($responseJson{'error_meta'}) {
            $bodyJson = json_decode($responseJson{'error_meta'}{0}{'body'}, true);
            Mage::log($bodyJson{'statusCode'}, null, 'bodyJson.log', true);

            if (!$responseJson{'payment_id'}) {
                if ($bodyJson{'statusCode'} != 200) {
                    Mage::register('isSecureArea', 1);
                    $order->cancel()->save();
                    Mage::unregister('isSecureArea');

                    $this->_redirect('pagaleve/pay/failure/');
                    return;
                }
            }
        }*/

/*
        if($order->canInvoice()) {

            $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());

            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
        }*/

       /* $invoiceId = Mage::getModel('sales/order_invoice_api')->create($quote->getReservedOrderId(), array());
        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);*/

     /*   $orderItems = $service->getOrder()->getAllItems();

        $service->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();*/
        $redirect = Mage::getStoreConfig('payment/Pagaleve_Pix/approve_url');


        Mage::log($redirect, null, 'redirect.log', true);
        Mage::dispatchEvent(
            'checkout_onepage_controller_success_action',
            array('order_ids' => array($order->getIncrementId()))
        );
        $this->_redirect($redirect);
    }

    public function failureAction()
    {
        $payment_fail = Mage::getStoreConfig('payment/Pagaleve_Pix/payment_fail');
        Mage::log($payment_fail, null, 'payment_Fail.log', true);

        $ambiente = Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente');
        Mage::log($ambiente, null, 'ambiente.log', true);

        if ($payment_fail == 0) {
            if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
                if ($quoteId = $this->getRequest()->getParam('quoteId')) {
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    $quote->setIsActive(true)->save();
                }
                Mage::getSingleton('core/session');
                $this->_redirect('checkout/cart');
                return;
            }
        }

        $session = $this->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();

        $texto = "<h1>Ocorreu um problema com a confirmação de pagamento.</h1><br>";
        $texto .= "<br>";
        $texto .= "Para maiores informações entre em contato.<br>";

        $block = $this->getLayout()->createBlock('Mage_Core_Block_Text');
        $block->setText($texto);
        $this->getLayout()->getBlock('content')->append($block);

        //Now showing it with rendering of layout
        $this->renderLayout();
        $this->_redirect('checkout/onepage/failure');
    }
}
