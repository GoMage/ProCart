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
class GoMage_Procart_Block_Product_List extends Mage_Catalog_Block_Product_List
{

    protected $_procartproductlist = null;

    public function getAddToCompareUrl($product)
    {
        $_modules      = Mage::getConfig()->getNode('modules')->children();
        $_modulesArray = (array)$_modules;
        if (isset($_modulesArray['GoMage_Navigation']) && $_modulesArray['GoMage_Navigation']->is('active')) {
            return $this->helper('gomage_navigation/compare')->getAddUrl($product);
        } else {
            return parent::getAddToCompareUrl($product);
        }
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if (Mage::helper('gomage_procart')->isProCartEnable()) {

            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();

            $url = parent::getAddToCartUrl($product, $additional);

            if (strpos($url, 'gpc_prod_id') === false) {
                $url = $url . '?gpc_prod_id=' . $product->getId();
                return $url;
            }
        }

        return parent::getAddToCartUrl($product, $additional);
    }

    public function getProcartProductList()
    {
        if (!$this->_procartproductlist) {
            $this->_procartproductlist = array();
            $helper                    = Mage::helper('gomage_procart');

            foreach ($this->getLoadedProductCollection() as $product) {
                if (!isset($this->_procartproductlist[$product->getId()])) {
                    $this->_procartproductlist[$product->getId()] = $helper->getProcartProductData($product, false, false);
                }
                if ($product->isComposite()) {
                    $ti = $product->getTypeInstance(true);
                    foreach ($ti->getChildrenIds($product->getId()) as $groupIds) {
                        foreach ($groupIds as $id) {
                            $childProduct = Mage::getModel('catalog/product')->load($id);
                            if (!isset($this->_procartproductlist[$childProduct->getId()])) {
                                $this->_procartproductlist[$childProduct->getId()] = $helper->getProcartProductData($childProduct, false, $product->getId());
                            } else {
                                $this->_procartproductlist[$childProduct->getId()]['parent_id'] = $product->getId();
                            }
                        }
                    }
                }
            }
        }

        return Mage::helper('core')->jsonEncode($this->_procartproductlist);
    }

    public function getProcartBundleSelectionHash()
    {
        $result = array();
        $helper = Mage::helper('gomage_procart');
        foreach ($this->getLoadedProductCollection() as $_product) {
            $product = Mage::getModel('catalog/product')->load($_product->getId());
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $selectionCollection = $helper->getBundleProductSelections($product);
                foreach ($selectionCollection as $selection) {
                    $data                                        = $helper->getProcartProductData($selection);
                    $data['selection_qty']                       = $selection->getData('selection_qty');
                    $data['selection_can_change_qty']            = $selection->getData('selection_can_change_qty');
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