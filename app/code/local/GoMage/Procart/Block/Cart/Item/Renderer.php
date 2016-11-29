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

class GoMage_Procart_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{

    public function getQty()
    {
        $helper = $this->getProCartHelper();
        if (!$helper) {
            return parent::getQty();
        }
        $rendered = $this->getRenderedBlock();
        if ($rendered && ($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') &&
            $helper->isProCartEnable() && (Mage::getStoreConfig('gomage_procart/qty_settings/' . GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR) !=
                GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO)
        ) {
            $template = Mage::helper('gomage_procart/qty')
                ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_SIDEBAR);

            $template->setItem($this->getItem());

            return $template->toHtml();
        }

        return parent::getQty();
    }

    public function getDeleteUrl()
    {
        $helper = $this->getProCartHelper();
        if (!$helper) {
            return parent::getDeleteUrl();
        }
        $rendered = $this->getRenderedBlock();
        $is_cart  = ($helper->getIsCartPage() || $helper->getChangeAttributeCart() ||
            $helper->getChangeQtyCart() || $helper->isCrosssellAdd() || $helper->isWhishlistMove());

        if ($helper->isProCartEnable() &&
            ($rendered &&
                (($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') ||
                    $is_cart))
        ) {
            return 'javascript:GomageProcartConfig.deleteItem(\'' . $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id' => $this->getItem()->getId()
                )
            ) . '\', \'' . $rendered->getNameInLayout() . '\')';
        }

        return parent::getDeleteUrl();

    }

    protected function getProCartHelper()
    {
        $helper  = false;
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (isset($modules['GoMage_Procart']) && $modules['GoMage_Procart']->is('active')) {
            try {
                $helper = Mage::helper('gomage_procart');
            } catch (Exception $e) {
                $helper = false;
            }
        }
        return $helper;
    }

}
