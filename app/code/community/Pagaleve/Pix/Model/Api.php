<?php 
/*
 * @Author: Warley Elias
 * @Email: warleyelias@gmail.com
 * @Date: 2023-01-04 12:50:36
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-01-05 14:59:33
 */

class Pagaleve_Pix_Model_Api
{
	const ENDPOINT 	= 'https://api.getnet.com.br';
	const ENDPOINT_STAGING  = 'https://api-sandbox.getnet.com.br/';

    const CONFIG_PAGALEVE_ENVIRONMENT = 'payment/Pagaleve_Pix/environment';
    const CONFIG_PAGALEVE_TOKEN_USERNAME = 'payment/Pagaleve_Pix/token_username';
    const CONFIG_PAGALEVE_TOKEN_PASSWORD = 'payment/Pagaleve_Pix/token_password';
    const CONFIG_PAGALEVE_TOKEN_USERNAME_SANDBOX = 'payment/Pagaleve_Pix/token_username_sandbox';
    const CONFIG_PAGALEVE_TOKEN_PASSWORD_SANDBOX = 'payment/Pagaleve_Pix/token_password_sandbox';
    const CONFIG_PAGALEVE_TOKEN_URL = 'payment/Pagaleve_Pix/token_url';
    const CONFIG_PAGALEVE_TOKEN_URL_SANDBOX = 'payment/Pagaleve_Pix/token_url_sandbox';
    const CONFIG_PAGALEVE_CHECKOUT_URL = 'payment/Pagaleve_Pix/checkout_url';
    const CONFIG_PAGALEVE_CHECKOUT_URL_SANDBOX = 'payment/Pagaleve_Pix/checkout_url_sandbox';
    const CONFIG_PAGALEVE_PAYMENT_URL = 'payment/Pagaleve_Pix/payment_url';
    const CONFIG_PAGALEVE_PAYMENT_URL_SANDBOX = 'payment/Pagaleve_Pix/payment_url_sandbox';
    const CONFIG_PAGALEVE_REFUND_URL = 'payment/Pagaleve_Pix/refund_url';
    const CONFIG_PAGALEVE_REFUND_URL_SANDBOX = 'payment/Pagaleve_Pix/refund_url_sandbox';

	protected $_helper;
	protected $_baseUrl;
	protected $_token_username;
	protected $_token_password;
    protected $_token_url;
    protected $_checkout_url;
    protected $_payment_url;
    protected $_refund_url;

	protected function getConfigData($path, $storeId = null) {
		if (null === $storeId) {
			$storeId = Mage::app()->getStore()->getId();
		}
		return Mage::getStoreConfig($path, $storeId);
	}

	public function __construct() {
		$this->_helper = Mage::helper('Pagaleve_Pix');
		if($this->getConfigData(self::CONFIG_PAGALEVE_ENVIRONMENT) == 1) {
			$this->_token_username = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_USERNAME);
			$this->_token_password = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_PASSWORD);
            $this->_token_url = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_URL);
            $this->_checkout_url = $this->getConfigData(self::CONFIG_PAGALEVE_CHECKOUT_URL);
            $this->_payment_url = $this->getConfigData(self::CONFIG_PAGALEVE_PAYMENT_URL);
            $this->_refund_url = $this->getConfigData(self::CONFIG_PAGALEVE_REFUND_URL);
		} else {
            $this->_token_username = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_USERNAME_SANDBOX);
            $this->_token_password = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_PASSWORD_SANDBOX);
            $this->_token_url = $this->getConfigData(self::CONFIG_PAGALEVE_TOKEN_URL_SANDBOX);
            $this->_checkout_url = $this->getConfigData(self::CONFIG_PAGALEVE_CHECKOUT_URL_SANDBOX);
            $this->_payment_url = $this->getConfigData(self::CONFIG_PAGALEVE_PAYMENT_URL_SANDBOX);
            $this->_refund_url = $this->getConfigData(self::CONFIG_PAGALEVE_REFUND_URL_SANDBOX);
		}
	}

    protected function generateUniqueToken() {
        $min = 0;
        $max = mt_getrandmax();
        return random_int($min, $max);
    }

    protected function getClient($uri) {
        $client = new Varien_Http_Client();
        $client->seturi($uri);
        $client->setconfig(['strict' => false, 'timeout' => 30]);

        $client->setheaders(
            [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Idempotency-Key' => $this->generateUniqueToken()
            ]
        );

        return $client;
    }

    protected function getToken() {
        $client = new Varien_Http_Client();
        $client->setUri($this->_token_url);
        $client->setConfig(['strict' => false, 'timeout' => 30]);

        $client->setHeaders(['content-type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost('username', $this->_token_username);
        $client->setParameterPost('password', $this->_token_password);
        $client->setMethod(Zend_Http_Client::POST);

        $request = $client->request();
        if ($request->getStatus() == 200) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result['token'] ?? '';
        }
        return '';
    }

    public function getCheckoutData($pagaleveCheckoutId) {
        $client = $this->getClient($this->_checkout_url . '/' . $pagaleveCheckoutId);
        $client->setMethod(Zend_Http_Client::GET);

        $request = $client->request();
        if ($request->getStatus() == 200) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result;
        }
        return '';
    }

    public function makeCheckout($params) {
        $client = $this->getClient($this->_checkout_url);
        $client->setRawData(json_encode($params), 'application/json');
        $client->setMethod(Zend_Http_Client::POST);

        $request = $client->request();
        if ($request->getStatus() == 201) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result;
        }
        return '';
    }

    public function getPaymenttData($pagalevePaymentId) {
        $client = $this->getClient($this->_payment_url . '/' . $pagalevePaymentId);
        $client->setMethod(Zend_Http_Client::GET);

        $request = $client->request();
        if ($request->getStatus() == 200) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result;
        }
        return '';
    }

    public function makePayment($params) {
        $client = $this->getClient($this->_payment_url);
        $client->setRawData(json_encode($params), 'application/json');
        $client->setMethod(Zend_Http_Client::POST);

        $request = $client->request();
        if ($request->getStatus() == 201) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result;
        }
        return '';
    }

    public function makeRefund($pagalevePaymentId, $params) {
        $uri = sprintf($this->_refund_url, $pagalevePaymentId);
        $client = $this->getClient($uri);
        $client->setRawData(json_encode($params), 'application/json');
        $client->setMethod(Zend_Http_Client::POST);

        $request = $client->request();
        if ($request->getStatus() == 200) {
            $requestBody = $request->getbody();
            $result = json_decode($requestBody, true);
            return $result;
        }
        return '';
    }
}