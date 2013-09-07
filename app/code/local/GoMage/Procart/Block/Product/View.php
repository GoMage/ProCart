<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.2
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Block_Product_View extends Mage_Core_Block_Template{
	
    protected $_procartproductlist = null; 
    	
    public function getProcartProductList(){
        
        if (!$this->_procartproductlist){             
             $this->_procartproductlist = array();
             $helper = Mage::helper('gomage_procart');
             if ($product = Mage::registry('current_product')){
                 $this->_procartproductlist[$product->getId()] = $helper->getProcartProductData($product);
             }
            
        }
        
        return Mage::helper('core')->jsonEncode($this->_procartproductlist);          
    }
        
}