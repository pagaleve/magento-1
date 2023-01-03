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

class Pagaleve_Pix_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'Pagaleve_Pix';
    protected $_formBlockType = 'Pagaleve_Pix/form';
    protected $_infoBlockType = 'Pagaleve_Pix/info';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    //protected $_canCapture = true;
    
    //protected $_order = null;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        return $this;
    }


 /**
     * Prepare info instance for save
     * Prepara a instancia info para receber os dados do cartão
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {       
        // $quote = Mage::getSingleton("checkout/cart")->getQuote();
        // Mage::log($quote, null, 'quotePrepareObserver.log', true);
        // $payment = $quote->getPayment();
        return $this;
    }
    /**
     *  Retorna pedido
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        Mage::log("hey2", null, 'hey2.log', true);
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     *  Associa pedido
     *
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $this->_order = $order;
        } elseif (is_numeric($order)) {
            $this->_order = Mage::getModel('sales/order')->load($order);
        } else {
            $this->_order = null;
        }
        
        return $this;
    }

    public function savePayment() {
        return Mage::getUrl('pagaleve/pay/redirect', $params);
    }

   public function getOrderPlaceRedirectUrl($orderId = 0)
	{
        $params = array();
        $params['_secure'] = true;
        
        if ($orderId != 0 && is_numeric($orderId)) {
            $params['order_id'] = $orderId;
        }
       
        Mage::log($orderId, null, 'orderIdPaymentPHP.log', true);
        
        return Mage::getUrl('pagaleve/pay/redirect', $params);
    }
        
}
