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
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Imagealign{

    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            array('value'=>'left', 'label' => $helper->__('Left')),
        	array('value'=>'right', 'label' => $helper->__('Right')),
        	array('value'=>'top', 'label' => $helper->__('Top')),
        	array('value'=>'bottom', 'label' => $helper->__('Bottom')),
        );
    	
    }
    
    public function toOptionHash(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            'left' => $helper->__('Left'),
            'right' => $helper->__('Right'),
            'top' => $helper->__('Top'),
        	'bottom' => $helper->__('Bottom'),
        );
    }

}