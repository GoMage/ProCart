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

class GoMage_Procart_Block_Checkout_Cart_Item_Renderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{

    public function getQty()
    {
         $rendered = $this->getRenderedBlock();
         $helper = Mage::helper('gomage_procart');
         if ($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') &&
             $helper->isProCartEnable() &&  (Mage::getStoreConfig('gomage_procart/qty_settings/'.GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_PAGE) !=
             GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO))
         {

             $template = Mage::helper('gomage_procart/qty')
                 ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR);

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
            (((Mage::getStoreConfig('gomage_procart/qty_settings/cart_sidebar') != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO) && $rendered &&
             ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar')) ||
             ((Mage::getStoreConfig('gomage_procart/qty_settings/cart_page') != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO) && $is_cart) )){
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
