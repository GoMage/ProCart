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

class GoMage_Procart_Block_Cart_Item_Renderer_Downloadable extends GoMage_Procart_Block_Cart_Item_Renderer
{
    /**
     * Retrieves item links options
     *
     * @return array
     */
    public function getLinks()
    {
        return Mage::helper('downloadable/catalog_product_configuration')->getLinks($this->getItem());
    }

    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return Mage::helper('downloadable/catalog_product_configuration')->getLinksTitle($this->getProduct());
    }
}