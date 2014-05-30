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

class GoMage_Procart_Block_Cart_Item_Renderer_Grouped extends Mage_Checkout_Block_Cart_Item_Renderer_Grouped
{

    public function getQty()
    {
        $rendered = $this->getRenderedBlock();
        $helper   = Mage::helper('gomage_procart');
        if ($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') &&
            $helper->isProCartEnable() && (Mage::getStoreConfig('gomage_procart/qty_settings/' . GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR) !=
                GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO)
        ) {
            $template = Mage::helper('gomage_procart/qty')
                ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR);
            $template->setItem($this->getItem());

            return $template->toHtml();
        } else {
            return parent::getQty();
        }
    }

    public function getDeleteUrl()
    {
        $helper   = Mage::helper('gomage_procart');
        $rendered = $this->getRenderedBlock();
        $is_cart  = ($helper->getIsCartPage() || $helper->getChangeAttributeCart() ||
            $helper->getChangeQtyCart() || $helper->isCrosssellAdd() || $helper->isWhishlistMove());
        if ($helper->isProCartEnable() &&
            (($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar')) ||
                $is_cart)
        ) {
            return 'javascript:GomageProcartConfig.deleteItem(\'' . $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id' => $this->getItem()->getId()
                )
            ) . '\', \'' . $rendered->getNameInLayout() . '\')';
        } else {
            return parent::getDeleteUrl();
        }
    }

}
