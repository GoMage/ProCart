<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.1
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Confirmation_Redirect{

    
    const NONE = 0;
    const SHOPPING_CART = 1;
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            array('value' => self::NONE, 'label'=>$helper->__('None')),
            array('value' => self::SHOPPING_CART, 'label'=>$helper->__('Shopping Cart')),            
        );
    }

}
