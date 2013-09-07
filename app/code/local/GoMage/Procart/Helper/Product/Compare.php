<?php
 /**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.2
 * @since        Class available since Release 1.1
 */

class GoMage_Procart_Helper_Product_Compare extends Mage_Catalog_Helper_Product_Compare
{
    public function getRemoveUrl($item)
    {
        $is_compare_page = ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'catalog') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'product_compare') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'index'));
        if (Mage::helper('gomage_procart')->isProCartEnable() && !$is_compare_page){
             $params = array(
                'product'=> $item->getId(),
                'isAjax' => 1,
                'gpc_remove_compare' => 1 
            ); 
            return 'javascript:GomageProcartConfig.deleteCompareItem(\'' . 
                $this->_getUrl('catalog/product_compare/remove', $params) . '\')';
        }else{ 
            return parent::getRemoveUrl($item);
        }
    }
    
	public function getClearListUrl()
    {
    	if (Mage::helper('gomage_procart')->isProCartEnable() && 
    	    Mage::app()->getFrontController()->getRequest()->getParam('gpc_compare_add') == 1){
        	$params = array();
        	return $this->_getUrl('catalog/product_compare/clear', $params);
    	}else{
    		return parent::getClearListUrl();
    	}	
    }
    
}
