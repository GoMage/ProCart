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
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Addeffect{

    const NO = 0;
    const AJAX_WINDOW = 1;
    const SLIDE = 2;
    
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            array('value'=>self::NO, 'label' => $helper->__('No')),
        	array('value'=>self::AJAX_WINDOW, 'label' => $helper->__('Ajax Window')),
        	array('value'=>self::SLIDE, 'label' => $helper->__('Slide')),        	        	
        );
    	
    }
        
    public function toOptionHash(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            self::NO => $helper->__('No'),
            self::AJAX_WINDOW => $helper->__('Ajax Window'),
            self::SLIDE => $helper->__('Slide'),        	
        );
    }

}