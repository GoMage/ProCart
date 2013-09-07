<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.3
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview{

    const ARROWS_NO = 6;
    const ARROWS_TOP_BOTTOM = 0;
    const ARROWS_LEFT_RIGHT = 1;
    const BUTTONS_TOP_BOTTOM = 2;
    const BUTTONS_LEFT_RIGHT = 3;
    const SMALL_BUTTONS_RIGHT_TOP_BOTTOM = 4;
    const BUTTONS_RIGHT = 5;
    
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            array('value'=>self::ARROWS_NO, 'label' => $helper->__('No')),
            array('value'=>self::ARROWS_TOP_BOTTOM, 'label' => $helper->__('Arrows (Top/Bottom)')),
        	array('value'=>self::ARROWS_LEFT_RIGHT, 'label' => $helper->__('Arrows (Left/Right)')),
        	array('value'=>self::BUTTONS_TOP_BOTTOM, 'label' => $helper->__('Buttons (Top/Bottom)')),        	        	
        	array('value'=>self::BUTTONS_LEFT_RIGHT, 'label' => $helper->__('Buttons (Left/Right)')),
        	array('value'=>self::SMALL_BUTTONS_RIGHT_TOP_BOTTOM, 'label' => $helper->__('Small Buttons (Right, Top/Bottom)')),
        	array('value'=>self::BUTTONS_RIGHT, 'label' => $helper->__('Buttons (Right)')),
        );
    	
    }
        
    public function toOptionHash(){
    	
    	$helper = Mage::helper('gomage_procart');
    	
        return array(
            self::ARROWS_NO => $helper->__('No'),
            self::ARROWS_TOP_BOTTOM => $helper->__('Arrows (Top/Bottom)'),
            self::ARROWS_LEFT_RIGHT => $helper->__('Arrows (Left/Right)'),
            self::BUTTONS_TOP_BOTTOM => $helper->__('Buttons (Top/Bottom)'),        	
            self::BUTTONS_LEFT_RIGHT => $helper->__('Buttons (Left/Right)'),
            self::SMALL_BUTTONS_RIGHT_TOP_BOTTOM => $helper->__('Small Buttons (Right, Top/Bottom)'),
            self::BUTTONS_RIGHT => $helper->__('Buttons (Right)'),
        );
    }

}