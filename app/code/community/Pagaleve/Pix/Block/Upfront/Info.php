<?php


class Pagaleve_Pix_Block_Upfront_Info extends Pagaleve_Pix_Block_Info {
    /**
     * Init default template for block
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pagaleve/info.phtml');
    }
}
