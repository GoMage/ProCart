<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 2.2.0
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Enabledisable{

    public function toOptionArray()
    {
        $options = array(
            array('value' => 0, 'label'=>Mage::helper('gomage_procart')->__('No')),
        );
        
        $websites = Mage::helper('gomage_procart')->getAvailavelWebsites();
        
        if(!empty($websites)){
        	$options[] = array('value' => 1, 'label'=>Mage::helper('gomage_procart')->__('Yes'));
        }
        
        return $options;
    }
}