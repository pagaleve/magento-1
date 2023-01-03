<?php

class Pagaleve_Pix_Model_Observer
{
    const LOG_FILENAME = 'pagaleve_pix_observe.log';
    const TEST_ENVIRONMENT = 'test';
    const PRODUCTION_ENVIRONMENT = 'prod';
    const ORDER_PAYMENTSTATE_PENDING = 'pending';
    const ORDER_PAYMENTSTATE_PROCCESSING = 'processing';
    const ORDER_PAYMENTSTATE_CANCELED = 'canceled';

    public function execute()
    {
        $orderCollection= Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter("status", "pending");


        foreach ($orderCollection as $orders) {
            if($orders->getPayment()->getMethod() != "Pagaleve_Pix") {
                continue;
            }

            $orderId = $orders->getIncrementId();

            if ($this->paymentStateIsPending($orders->getStatus())) {
                $response = $this->getOrderStatus($orders);
                $responseJson = json_decode($response, true);
                $status = $responseJson{"state"};

                if ($status == "AUTHORIZED") {
                    $response = $this->getOrderPayments($orders);
                    $responseJson = json_decode($response, true);
                    $status = $responseJson{"state"};
                    if ($status == "CANCELED" || $status == "EXPIRED") {
                        $this->destroyOrder($orders);
                        continue;
                    }

                    if ($status == "COMPLETED") {
                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($orders->getIncrementId(), array());
                        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
                    }
                }


                $response = $this->getOrderStatus($orders);
                if (!$response) {
                    continue;
                }


                $responseJson = json_decode($response, true);
                $verifyStatus = $responseJson{"state"} != "COMPLETED";


                $status = $responseJson{"state"};

                if ($verifyStatus) {
                    if ($status == "CANCELED" || $status == "EXPIRED") {
                        $this->destroyOrder($orders);
                        continue;
                    }
                } else {

                    if ($status == "COMPLETED") {
                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($orders->getIncrementId(), array());

                        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
                    }
                    if (($status == "ACCEPTED" || $status == "NEW" || $status == "AUTHORIZED")) {
                        continue;
                    }

                }
            }
        }
    }



    public function getOrderStatus($order)
    {
        $accessToken = $this->getAccessToken();
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $orderId = $order->getIncrementId();

        $action = 'state';
        $baseUrl = Mage::getBaseUrl();
        $baseUrl = str_replace('/index.php', '', $baseUrl);
        $params = [
            'access_token' => $accessToken,
            'checkout_id' =>  $orderId,
            'url_base' => $baseUrl

        ];

        $url = $this->getUrlAmbiente($action, $params);

        return $this->getUrlRequest($url);
    }

    public function getOrderPayments($order)
    {
        $accessToken = $this->getAccessToken();
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $orderId = $order->getIncrementId();

        $action = 'payments';
        $params = [
            'access_token' => $accessToken,
            'orderId' => $order->getQuoteId(),
            'orderPaymentId' =>$orderId,
        ];

        $url = $this->getUrlAmbiente($action, $params);
        $baseUrl = Mage::getBaseUrl();
        $baseUrl = str_replace('/index.php', '', $baseUrl);
        $payload = json_encode(array(
            "order_id" => $order->getQuoteId(),
            "store_accesstoken" => $accessToken,
            'url_base' => $baseUrl
        ));

        return $this->urlRequest($url, $payload);
    }

    public function getAmbiente()
    {

        Mage::log(Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente'), null, 'ambiente1.log', true);

        $ambiente = null;

        if(Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente') == "1") {
            $ambiente = self::PRODUCTION_ENVIRONMENT;
        } else {
            $ambiente = self::TEST_ENVIRONMENT;
        }

        return $ambiente;
    }

    public function getAccessToken()
    {
        $variable = $this->getAmbiente() === self::TEST_ENVIRONMENT ? 'token_homolog' : 'token_prod';
        $access_token = Mage::getStoreConfig("payment/Pagaleve_Pix/{$variable}");

        return $access_token;
    }

    public function getUrlAmbiente($action, $params = [])
    {

        $ambiente = Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente');
        Mage::log($ambiente, null, 'ambiente.log', true);
        if ($ambiente == 0) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_homolog');
            $baseUrl = "https://ve3zdjmt4h.execute-api.us-east-1.amazonaws.com/test/";
        }
        if ($ambiente == 1) {
            $access_token = Mage::getStoreConfig('payment/Pagaleve_Pix/token_prod');
            $baseUrl = "https://ke8lffc2u8.execute-api.us-east-1.amazonaws.com/prod2/";
        }

        $params = http_build_query($params);
        $url = "$baseUrl/$action?$params";

        return $url;
    }

    public function getUrl($action, $params = [])
    {
        $baseUrl = Mage::getStoreConfig('payment/Pagaleve_Pix/url_'.$this->getAmbiente());
        $params = http_build_query($params);
        $url = "$baseUrl/$action?$params";
        return $url;
    }


    public function getUrlRequest($url)
    {
        $httpHeaderValues = ['Content-Type: application/json'];

        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (curl_error $error) {
            $message = json_encode($error->getMessage());

            return false;
        }
    }

    public function urlRequest($url, $payload)
    {
        $httpHeaderValues = ['Content-Type: application/json'];

        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaderValues);
            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        } catch (curl_error $error) {
            $message = json_encode($error->getMessage());
            return false;
        }
    }

    public function  hasPayments($responseJson)
    {
        return $this->hasError($responseJson);
    }

    public function hasError($responseJson)
    {
        if ($responseJson{'error_meta'} || $responseJson{'state'}) {
            if ( $responseJson{'state'} == "CANCELED") {
                return true;
            }
            if (!$responseJson{'payment_id'}) {
                $bodyJson = json_decode($responseJson{'error_meta'}{0}{'body'}, true);

                if ($bodyJson{'statusCode'} !== 200) {
                    return true;
                }
            }
        }

        return false;
    }

    public function destroyOrder($order)
    {
        Mage::register('isSecureArea', 1);
        $order->cancel()->save();
        Mage::unregister('isSecureArea');

        Mage::log("Order ID {$order->getId()} destroied", null, self::LOG_FILENAME, true);
    }

    public function paymentStateIsPending($order)
    {
        return $order === self::ORDER_PAYMENTSTATE_PENDING;
    }

    public function paymentStateIsProcessing($order)
    {
        return $order === self::ORDER_PAYMENTSTATE_PROCCESSING;
    }

    public function paymentStateIsCanceled($order)
    {
        return $order === self::ORDER_PAYMENTSTATE_CANCELED;
    }

    public function createInvoice($order)
    {
        if ($order->canInvoice()) {
            $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());

            return Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
        }

        return null;
    }

    public function refund( $observer)
    {

       if (!$this->existsLastQuoteId($observer)) {
           return;
        }

        if (!$this->paymentMethodIsPagalevePix($observer->getEvent()->getCreditmemo()->getOrder()->getPayment()->getMethod())) {
            echo "teste";

            // return;
        }

        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $response = $this->refundOrder($order);
        if (!$response) {
            die(" err");
        }

        $responseJson = json_decode($response, true);

        if ($this->hasError($responseJson)) {
            //...
        }

        if (!$this->refundSuccess($responseJson)) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Problem refunding the order, check refund amount or contact Pagaleve_Pix'
            );
        }
    }

    public function existsLastQuoteId($observer)
    {
        $lastQuoteId = $observer->getEvent()->getCreditmemo()->getOrder()->getQuoteId();
        return $lastQuoteId;

    }

    public function getQuote($quoteId)
    {
          echo $quoteId;
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        return $quote;
    }

    public function getOrder($orderId)
    {

        $order = Mage::getModel('sales/order')->load($orderId);

        return $order;
    }

    public function paymentMethodIsPagalevePix($payment)
    {
        $paymentMethod = $payment;

        return $paymentMethod === 'Pagaleve_Pix';
    }

    public function refundOrder($order)
    {
        $accessToken = $this->getAccessToken();
        $orderId = $order->getIncrementId();

        $action = 'refund';
        $params = [
            'access_token' => $accessToken,
            'orderPaymentId' => $orderId,

        ];

        $url = $this->getUrl($action, $params);

         $baseUrl = Mage::getBaseUrl();
         $baseUrl = str_replace('/index.php', '', $baseUrl);

        $payload = json_encode([
            'amount' => $this->getAmount($order),
            'reason' => 'REQUESTED_BY_CUSTOMER',
            'description' => Mage::getSingleton('adminhtml/session')->getCommentText(),
            'url_base' => $baseUrl
        ]);

        return $this->urlRequest($url, $payload);
    }


    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    public function getAmount($order)
    {
        $amount = $order->getGrandTotal();
        $refund = $order->getTotalRefunded();

        if ($refund !== 0) {
            $amount = $refund;
        }

        return round((float)$amount * 100);
    }

    public function refundSuccess($responseJson)
    {
        return isset($responseJson{'success'});
    }
}