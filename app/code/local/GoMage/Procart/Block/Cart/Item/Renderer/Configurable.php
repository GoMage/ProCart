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

class GoMage_Procart_Block_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable
{
    
    public function getQty()
    {
         $rendered = $this->getRenderedBlock();
         $helper = Mage::helper('gomage_procart');
         if ($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') &&
             $helper->isProCartEnable() &&  (Mage::getStoreConfig('gomage_procart/qty_settings/'.GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR) !=
             GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO))
         {
             $template = Mage::helper('gomage_procart/qty')
                 ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR);

             $template->setItem($this->getItem());
                                       
             return $template->toHtml();
         }
         else {
             return parent::getQty();
         }
    }
    
    public function getDeleteUrl()
    {
        $helper = Mage::helper('gomage_procart');
        $rendered = $this->getRenderedBlock();
        $is_cart = ($helper->getIsCartPage() || $helper->getChangeAttributeCart() || 
                    $helper->getChangeQtyCart() || $helper->isCrosssellAdd());
        if ($helper->isProCartEnable() &&
            (($rendered &&
             ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar')) ||
              $is_cart) ){
            return 'javascript:GomageProcartConfig.deleteItem(\'' . $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id'=>$this->getItem()->getId()
                )
            ) . '\')';
        }    
        else{
            return parent::getDeleteUrl();
        }    
    }
    
    public function getFormatedOptionValue($optionValue)
    {
        $helper = Mage::helper('gomage_procart');

        if ($helper->isProCartEnable()){
            if (!$helper->getIsAnymoreVersion(1, 5) &&
                 (Mage::getStoreConfig('gomage_procart/qty_settings/cart_page')
                     != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO) &&
                 (Mage::helper('gomage_procart')->getIsCartPage() || 
                  Mage::helper('gomage_procart')->getChangeAttributeCart() ||
                  Mage::helper('gomage_procart')->getChangeQtyCart())){
                $optionInfo = array();

                // define input data format
                if (is_array($optionValue)) {
                    if (isset($optionValue['option_id'])) {
                        $optionInfo = $optionValue;
                        if (isset($optionInfo['value'])) {
                            $optionValue = $optionInfo['value'];
                        }
                    } elseif (isset($optionValue['value'])) {
                        $optionValue = $optionValue['value'];
                    }
                }
        
                // render customized option view
                if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
                    $_default = array('value' => $optionValue);
                    if (isset($optionInfo['option_type'])) {
                        try {
                            $group = Mage::getModel('catalog/product_option')->groupFactory($optionInfo['option_type']);
                            return array('value' => $group->getCustomizedView($optionInfo));
                        } catch (Exception $e) {
                            return $_default;
                        }
                    }
                    return $_default;
                }
        
                // truncate standard view
                $result = array();
                if (is_array($optionValue)) {
                    $_truncatedValue = implode("\n", $optionValue);
                    $_truncatedValue = nl2br($_truncatedValue);
                    return array('value' => $_truncatedValue);
                } else {
                    $_truncatedValue = nl2br($optionValue);
                }
        
                $result = array('value' => $_truncatedValue);

                return $result;
                                
            }
            else {
                return parent::getFormatedOptionValue($optionValue);
            }             
        }
        else {
            return parent::getFormatedOptionValue($optionValue);
        }
        
    }
    
}
