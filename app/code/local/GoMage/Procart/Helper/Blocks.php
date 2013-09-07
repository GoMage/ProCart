<?php

/**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.3
 * @since        Class available since Release 1.3
 */

/**
 * Retrieving of view blocks by handle for ajax responses
 */
class GoMage_Procart_Helper_Blocks extends Mage_Core_Helper_Abstract{

    private $_layouts = array();

    private function _setLayoutByHandle($handle,$layout)
    {
        $this->_layouts[$handle] = $layout;
    }

    private function _getLayoutByHandle($handle)
    {
        return $this->_layouts[$handle];
    }

    /**
     * @param string $handle
     * @return Mage_Core_Model_Layout
     */
    private function _loadLayout($handle = 'default')
    {
        if(!$this->_getLayoutByHandle($handle)){
            /* @var $layout Mage_Core_Model_Layout */
            $layout = Mage::getModel('core/layout');
            $_handles = explode(',',$handle);
            $layout->getUpdate()->load($_handles);
            $layout->generateXml()->generateBlocks();
            $this->_setLayoutByHandle($handle, $layout);
        }
        return $this->_getLayoutByHandle($handle);
    }

    /**
     * Retrieve shopping cart page checkout methods
     * (buttons before ($type='top_methods') or after ($type='methods') products list)
     *
     * @param $type 'top_methods' or 'methods'
     * @return string block html
     */
    public function  getCartCheckoutMethods($type){
        $layout = $this->_loadLayout('checkout_cart_index');
        $html = '';
        foreach($layout->getBlock('checkout.cart.'.$type)->getSortedChildBlocks() as $child){
            $tmp = $child->toHtml();
            if(!empty($tmp)){
                $html .= $layout->createBlock('core/text_tag')
                    ->setTagName('li')
                    ->setContents($tmp)
                    ->toHtml();
            }
        }
        return $layout->createBlock('core/text_tag')
            ->setTagName('ul')
            ->setTagParam('class','checkout-types')
            ->setContents($html)
            ->toHtml();
    }

    /**
     * @return bool|string
     */
    public function getCrosssell(){
        $layout = $this->_loadLayout('checkout_cart_index');
        return $layout->getBlock('checkout.cart.crosssell')->toHtml();
    }

    /**
     * @return bool|string
     */
    public function getWishlistTopLink()
    {
        $layout = $this->_loadLayout();
        return $layout->getBlock('wishlist_link')->toHtml();
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    public function getShoppingCartBlock()
    {
        $layout = $this->_loadLayout('checkout_cart_index');
        return $layout->getBlock('checkout.cart');
    }
}