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
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Confirmation_Color{
  
    public function toOptionArray()
    {
        $helper = Mage::helper('gomage_procart');
        
        return array(            
            array('value' => 'black', 'label'=>$helper->__('Black')),
            array('value' => 'blue', 'label'=>$helper->__('Blue')),
            array('value' => 'brown', 'label'=>$helper->__('Brown')),
            array('value' => 'gray', 'label'=>$helper->__('Gray')),
            array('value' => 'green', 'label'=>$helper->__('Green')),
            array('value' => 'light-blue', 'label'=>$helper->__('Light-Blue')),
            array('value' => 'light-green', 'label'=>$helper->__('Light-Green')),
            array('value' => 'orange', 'label'=>$helper->__('Orange')),
            array('value' => 'red', 'label'=>$helper->__('Red')),
            array('value' => 'pink', 'label'=>$helper->__('Pink')),
            array('value' => 'violet', 'label'=>$helper->__('Violet')),
            array('value' => 'yellow', 'label'=>$helper->__('Yellow')),
        );
    }

}