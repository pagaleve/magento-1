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
 * @package    Pagaleve_Pix
 * @copyright  Copyright (c) 2011 MagentoNet (www.magento.net.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     MagentoNet <contato@magento.net.br>
 */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
class Pagaleve_Pix_Model_Pix extends Mage_Core_Model_Abstract
{      
    		/**
        * A model to serialize attributes
        * @var Varien_Object
        */
        protected $_serializer = null ;
        /**
        * Initialization
        */
        protected function _construct ()
        {
            $this -> _serializer = new Varien_Object ();
            parent :: _construct ();
        }
         /*============================================================================
         * 
         * Solicita autorização do pagamento no webservice da Pagaleve_Pix
         * @approve_url
         * @cancel_url
         * @metadata:
         *    @transactionId
         *    @merchantName
         * @order:
         *    @reference
         *    @tax
         *    @amount
         *    @items: [
         *        @name
         *        @quantity
         *        @price
         *        @reference
         *       ]
         * @reference
         * @shopper:
         *    @first_name
         *    @last_name
         *    @phone
         *    @email
         *    @cpf
         *    @billing_address:
         *        @name
         *        @city
         *        @state
         *        @zip_code
         *        @neighborhood
         *        @number
         *        @complement
         *        @phone_number
         *        @street
         * @webhook_url
         ===========================================================================*/
        public function setTransacao(
            $access_token,
            $approve_url,
            $cancel_url,
            $metadata,
            $order,
            $reference,
            $shopper,
            $baseUrl,
            $webhook_url
          )
        {
               $payload = json_encode(array(
                  "approve_url" => $approve_url,
                  "cancel_url" => $cancel_url,
                  "metadata" => $metadata,
                  "order" => $order,
                  "reference" => $reference,
                  "shopper" => $shopper,
                  "url_base" => $baseUrl,
                  "webhook_url" => $webhook_url
               ));

              $ambiente = Mage::getStoreConfig('payment/Pagaleve_Pix/ambiente');
              Mage::log($ambiente, null, 'ambiente.log', true);

              if ($ambiente == 0) {
                $url = "https://ve3zdjmt4h.execute-api.us-east-1.amazonaws.com/test/checkouts?access_token=".$access_token;
              }
              if ($ambiente == 1) {
                $url = "https://ke8lffc2u8.execute-api.us-east-1.amazonaws.com/prod2/checkouts?access_token=".$access_token;
              }
               
               
               Mage::log($payload, null, 'payload.log', true);
               Mage::log($url, null, 'url.log', true);
                /**
                 * Aqui enviamos a requisição
                 */
                try {
                  $curl = curl_init($url);
                  // Set the CURLOPT_RETURNTRANSFER option to true
                  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                  // Set the CURLOPT_POST option to true for POST request
                  curl_setopt($curl, CURLOPT_POST, true);
                  // Set the re/quest data as JSON using json_encode function
                  curl_setopt($curl, CURLOPT_POSTFIELDS,  $payload);
                  // Set custom headers for RapidAPI Auth and Content-Type header
                  curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                  ]);
                  // Execute cURL request with all previous settings
                  $response = curl_exec($curl);
                  Mage::log($response, null, 'response.log', true);
                  Mage::log($curl, null, 'curl.log', true);
                  // Close cURL session
                  curl_close($curl);
                  return $response;
                } catch( curl_error $fault ){
                    Mage::log($fault, null, 'fault.log', true);
                    print_r($fault->getMessage());
                    die(" err");
                    return false;
                }         
        }
}
?>