
<?php

//colocar endereço da pasta onde está o mangento
require_once('/var/www/app/Mage.php');
Mage::app('admin');
//colocar endereço da pasta onde está o mangento
require_once '/var/www/shell/abstract.php';
class Mage_Shell_PagaleveCron extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        // Call the method here that was previously configured in the module's cron configuration
        $model = Mage::getSingleton('Pagaleve_Pix/observer');
        $model->execute();
    }
}
$shell = new Mage_Shell_PagaleveCron();
$shell->run();