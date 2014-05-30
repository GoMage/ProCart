<?php
/**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Procart_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    public function getProCartAttributeActiveOptions($product, $attribute, $filter = array())
    {
        $result     = array();
        $collection = $this->getUsedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();

        if (is_array($filter)) {
            foreach ($filter as $attribute_id => $value) {
                $filter_attribute = $this->getAttributeById($attribute_id, $product);
                if (!is_null($filter_attribute)) {
                    $collection->addAttributeToFilter($filter_attribute->getAttributeCode(), $value);
                }
            }
        }

        foreach ($collection as $_product) {
            if ($_product->isSaleable()) {
                $result[] = $_product->getData($attribute->getProductAttribute()->getAttributeCode());
            }
        }
        return $result;
    }

    public function getSelectedAttributesInfo($product = null)
    {
        if (!Mage::registry('gomage_procart_render_attributes')) {
            return parent::getSelectedAttributesInfo($product);
        }

        $attributes = array();
        if ($attributesOption = $this->getProduct($product)->getCustomOption('attributes')) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $this->getProduct($product)->getData($this->_usedAttributes);
            $filter         = array();

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    $label     = $attribute->getLabel();
                    $value     = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                        $attribute_values = $attribute->getPrices() ? $attribute->getPrices() : array();
                        foreach ($attribute_values as $_k => $_v) {
                            if (in_array($_v['value_index'], $this->getProCartAttributeActiveOptions($product, $attribute, $filter))) {
                                $attribute_values[$_k]['value'] = $_v['value_index'];
                            } else {
                                unset($attribute_values[$_k]);
                            }
                        }
                        $select               = Mage::getSingleton('core/layout')->createBlock('core/html_select')
                            ->setClass('glg_cart_attribute_' . $attributeId)
                            ->setId('glg_cart_attribute_' . $product->getId() . '_' . $attributeId . '_' . $attributeValue)
                            ->setName('glg_cart_attribute_' . $product->getId() . '_' . $attributeId . '_' . $attributeValue)
                            ->setTitle($label)
                            ->setExtraParams('onchange="GomageProcartConfig.attributeCartChange(this,' . $product->getId() . ')"')
                            ->setValue($attributeValue)
                            ->setOptions($attribute_values);
                        $value                = $select->getHtml();
                        $filter[$attributeId] = $attributeValue;

                    } else {
                        $value = '';
                    }
                    $attributes[] = array('label' => $label, 'value' => $value);
                }
            }
        }
        return $attributes;
    }

}
