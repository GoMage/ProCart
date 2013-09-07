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

class GoMage_Procart_Block_Checkout_Cart_Item_Renderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
            
    public function getQty()
    {                       
         $rendered = $this->getRenderedBlock();
         $helper = Mage::helper('gomage_procart');
         if ($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') &&
             $helper->isProCartEnable() && Mage::getStoreConfig('gomage_procart/qty_settings/cart_block'))
         {                    

             $template = $this->getLayout()->createBlock('core/template', 'gomage.procart.sidebar.qty.template');
             
             switch (Mage::getStoreConfig('gomage_procart/qty_settings/qty_view'))
             {
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/sidebar/arrows/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                     $template->setTemplate('gomage/procart/sidebar/buttons/top_bottom.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/sidebar/buttons/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
                 default:    
                     $template->setTemplate('gomage/procart/sidebar/arrows/top_bottom.phtml');
                         
             } 
                 
             $template->setItem($this->getItem());
                                       
             return $template->toHtml();
         }
         else
             return parent::getQty();                  
    }
    
    public function getDeleteUrl()
    {
        $helper = Mage::helper('gomage_procart');
        $rendered = $this->getRenderedBlock();
        $is_cart = ($helper->getIsCartPage() || $helper->getChangeAttributeCart() || 
                    $helper->getChangeQtyCart() || $helper->isCrosssellAdd());
        if ($helper->isProCartEnable() &&
            ((Mage::getStoreConfig('gomage_procart/qty_settings/cart_block') && $rendered && 
             ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar')) ||
             (Mage::getStoreConfig('gomage_procart/qty_settings/cart_page') && $is_cart) )){        
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
    
}
