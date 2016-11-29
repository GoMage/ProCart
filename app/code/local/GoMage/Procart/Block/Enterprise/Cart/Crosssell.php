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
 * @since        Class available since Release 1.3
 */

class GoMage_Procart_Block_Enterprise_Cart_Crosssell extends Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
{
    public function getAddToCartUrl($product, $additional = array())
    {

        if (Mage::helper('gomage_procart')->isProCartEnable()) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            $additional['_query']['gpc_prod_id']   = $product->getId();
            $additional['_query']['gpc_crosssell'] = 1;

            $url = parent::getAddToCartUrl($product, $additional);

            if (strpos($url, 'gpc_prod_id') === false) {
                $url = $url . '?gpc_prod_id=' . $product->getId() . '&gpc_crosssell=1';
                return $url;
            }
        }
        return parent::getAddToCartUrl($product, $additional);

    }

    public function _toHtml()
    {
        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
        return parent::_toHtml();
    }

}