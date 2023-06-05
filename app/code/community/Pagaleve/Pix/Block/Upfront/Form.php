<?php
/*
 * @Author: Warley Elias
 * @Email: warley.elias@pentagrama.com.br
 * @Date: 2023-06-01 13:17:09
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-06-01 13:26:03
 */

class Pagaleve_Pix_Block_Upfront_Form extends Pagaleve_Pix_Block_Form {
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pagaleve/form.phtml');
    }
}
