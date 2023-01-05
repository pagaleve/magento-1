<?php



class Pagaleve_Pix_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pagaleve/form.phtml');
    }
    
    //pega configurações do magento
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }
    
}
