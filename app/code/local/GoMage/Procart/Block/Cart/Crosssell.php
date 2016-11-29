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
 * @since        Class available since Release 1.1
 */

class GoMage_Procart_Block_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
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
