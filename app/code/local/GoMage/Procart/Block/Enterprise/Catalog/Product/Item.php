<?php
/**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.0
 * @since        Class available since Release 1.0
 */

class GoMage_Procart_Block_Enterprise_Catalog_Product_Item extends Enterprise_TargetRule_Block_Catalog_Product_Item
{
    public function getAddToCartUrl($product, $additional = array()){

        if (Mage::helper('gomage_procart')->isProCartEnable() && Mage::getStoreConfigFlag('gomage_procart/qty_settings/related_prods') ){
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();

            $url = parent::getAddToCartUrl($product, $additional);

            if ( strpos($url, 'gpc_prod_id') === false )
            {
                $url = $url . '?gpc_prod_id=' . $product->getId();
                return $url;
            }
        }
        return parent::getAddToCartUrl($product, $additional);
    }
}
