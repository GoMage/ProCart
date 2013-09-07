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

class GoMage_Procart_Block_Product_View extends Mage_Core_Block_Template{

    protected $_procartproductlist = null;

    public function getProcartProductList(){
    	
        if (!$this->_procartproductlist){
            $this->_procartproductlist = array();
            $helper = Mage::helper('gomage_procart');

            if ($product = Mage::registry('current_product')){
                $this->_procartproductlist[$product->getId()] = $helper->getProcartProductData($product, $helper->isConfigureCart());
                if($product->isComposite()){
                    $ti = $product->getTypeInstance(true);
                    foreach($ti->getChildrenIds($product->getId()) as $groupIds){
                        foreach($groupIds as $id){
                            $childProduct = Mage::getModel('catalog/product')->load($id);
                            $this->_procartproductlist[$childProduct->getId()]
                                = $helper->getProcartProductData($childProduct);
                        }
                    }
                }
            }
        }
        return Mage::helper('core')->jsonEncode($this->_procartproductlist);
    }

    public function getProcartBundleSelectionHash(){
        $result = array();
        $helper = Mage::helper('gomage_procart');
        if ($product = Mage::registry('current_product')){
            $product = Mage::getModel('catalog/product')->load($product->getId());
            if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                $selectionCollection = $helper->getBundleProductSelections($product);
                foreach($selectionCollection as $selection){
                    $data = $helper->getProcartProductData($selection);
                    $data['selection_qty'] = $selection->getData('selection_qty');
                    $data['selection_can_change_qty'] = $selection->getData('selection_can_change_qty');
                    $result[$selection->getData('selection_id')] = $data;
                }
            }
        }
        return Mage::helper('core')->jsonEncode($result);
    }
}