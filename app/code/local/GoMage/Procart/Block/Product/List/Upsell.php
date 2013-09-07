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

class GoMage_Procart_Block_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell
{
    public function getReviewsSummaryHtml(Mage_Catalog_Model_Product $product, $templateType = false,
                                          $displayIfNoReviews = false)
    {
        if (Mage::helper('gomage_procart')->isProCartEnable()){
            if ($this->_initReviewsHelperBlock()) {

                $html = $this->_reviewsHelperBlock->getSummaryHtml($product, $templateType, $displayIfNoReviews);

                if ( Mage::getStoreConfig('gomage_procart/qty_settings/upsell_prods') != 6 )
                {
                    $html = $html . $this->_addButton($product);
                }

                return $html;
            }

            return '';
        }

        return parent::getReviewsSummaryHtml($product, $templateType = false, $displayIfNoReviews = false);
    }

    protected function _addButton($product)
    {
        return '<div class="add-to-cart"><button type="button" title="' . $this->__('Add to Cart') . '" class="button btn-cart" onclick="setLocation(\'' . $this->getAddToCartUrl($product) .'\')"><span><span>' . $this->__('Add to Cart') . '</span></span></button></div>';
    }

    public function getAddToCartUrl($product, $additional = array()){

        if (Mage::helper('gomage_procart')->isProCartEnable()){
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();
        }
        return parent::getAddToCartUrl($product, $additional);
    }

}
