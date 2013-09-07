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

class GoMage_Procart_Block_Product_List extends Mage_Catalog_Block_Product_List{

    protected $_procartproductlist = null;

    public function getAddToCompareUrl($product){

        $_modules = Mage::getConfig()->getNode('modules')->children();
        $_modulesArray = (array)$_modules;
        if(isset($_modulesArray['GoMage_Navigation']) && $_modulesArray['GoMage_Navigation']->is('active')){
            return $this->helper('gomage_navigation/compare')->getAddUrl($product);
        }else{
            return parent::getAddToCompareUrl($product);
        }


    }

    public function getAddToCartUrl($product, $additional = array()){

        if (Mage::helper('gomage_procart')->isProCartEnable()){
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();
        }
        return parent::getAddToCartUrl($product, $additional);

    }

    public function getProcartProductList(){

        if (!$this->_procartproductlist){
            $this->_procartproductlist = array();
            $helper = Mage::helper('gomage_procart');

            foreach ($this->getLoadedProductCollection() as $_product){
                $product = Mage::getModel('catalog/product')->load($_product->getId());
                $this->_procartproductlist[$product->getId()] = $helper->getProcartProductData($product);
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
        foreach ($this->getLoadedProductCollection() as $_product){
            $product = Mage::getModel('catalog/product')->load($_product->getId());
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
    
	public function getToolbarHtml()
    {
    	$toolbar = $this->getChild('toolbar');
        return $toolbar->toHtml(); 
    }
}