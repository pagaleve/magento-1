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

class Pagaleve_Pix_Model_Source_Environment
{
	public function toOptionArray ()
	{
		$options = array();
        $options['0'] = Mage::helper('adminhtml')->__('Homologation');
        $options['1'] = Mage::helper('adminhtml')->__('Production');

		return $options;
	}

}
