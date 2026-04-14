<?php

class Pagaleve_Pix_Block_Adminhtml_System_Config_Logo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $src = Mage::getBaseUrl('skin') . 'adminhtml/default/default/pagaleve/images/pagaleve-logo-completo.png';
        return '<img src="' . $src . '" alt="Pagaleve" style="max-width:200px;" />';
    }
}
