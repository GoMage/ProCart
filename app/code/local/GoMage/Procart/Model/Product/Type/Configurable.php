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

class GoMage_Procart_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    public function getProCartAttributeActiveOptions($product, $attribute){
        $result = array();
        $allProducts = $this->getUsedProducts(null, $product);
        foreach ($allProducts as $_product){
            if ($_product->isSaleable()) {
                 $result[] = $_product->getData($attribute->getProductAttribute()->getAttributeCode());
            }     
        }
        return $result;
    } 
        
    public function getSelectedAttributesInfo($product = null)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if($request->getControllerName() == 'onepage')
        {
            return parent::getSelectedAttributesInfo($product);
        }
        $attributes = array();
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if ($attributesOption = $this->getProduct($product)->getCustomOption('attributes')) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $this->getProduct($product)->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    $label = $attribute->getLabel();
                    $value = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                        if (Mage::helper('gomage_procart')->isProCartEnable() &&
                            ((Mage::getStoreConfig('gomage_procart/qty_settings/cart_page')
                                != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO)
                                ||
                             (Mage::getStoreConfig('gomage_procart/qty_settings/cart_sidebar')
                                 != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO)))
                        {                        
                            $attribute_values = $attribute->getPrices() ? $attribute->getPrices() : array();
                            foreach ($attribute_values as $_k => $_v){
                                if (in_array($_v['value_index'], $this->getProCartAttributeActiveOptions($product, $attribute))){
                                    $attribute_values[$_k]['value'] = $_v['value_index'];
                                }else{
                                    unset($attribute_values[$_k]);
                                }
                            } 
                            $select = Mage::getSingleton('core/layout')->createBlock('core/html_select')
                                        ->setClass('glg_cart_attribute_' . $attributeId)
                                        ->setId('glg_cart_attribute_' . $product->getId() .'_'. $attributeId .'_'. $attributeValue)
                                        ->setName('glg_cart_attribute_' . $product->getId() .'_'. $attributeId .'_'. $attributeValue)
                                        ->setTitle($label)
                                        ->setExtraParams('onchange="GomageProcartConfig.attributeCartChange(this,'.$product->getId().')"')
                                        ->setValue($attributeValue)
                                        ->setOptions($attribute_values);
                            $value = $select->getHtml();
                        }else 
                            $value = $value->getSource()->getOptionText($attributeValue);                                
                    }
                    else {
                        $value = '';
                    }

                    $attributes[] = array('label'=>$label, 'value'=>$value);
                }
            }
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $attributes;
    }

}
