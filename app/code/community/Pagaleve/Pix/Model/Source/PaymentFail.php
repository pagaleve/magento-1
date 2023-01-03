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

class Pagaleve_Pix_Model_Source_PaymentFail
{
	public function toOptionArray ()
	{
		$options = array();
        
        $options['0'] = Mage::helper('adminhtml')->__('Pendente - Mantém o carrinho');
        $options['1'] = Mage::helper('adminhtml')->__('Cancelada - Limpa o carrinho');
    
        
		return $options;
	}

}