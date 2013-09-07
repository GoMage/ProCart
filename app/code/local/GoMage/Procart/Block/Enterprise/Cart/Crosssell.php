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
 * @since        Class available since Release 1.3
 */

class GoMage_Procart_Block_Enterprise_Cart_Crosssell extends Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
{
    public function getAddToCartUrl($product, $additional = array()){

        if (Mage::helper('gomage_procart')->isProCartEnable()){
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();
            $additional['_query']['gpc_crosssell'] = 1;
        }
        return parent::getAddToCartUrl($product, $additional);

    }

}