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
        $itemLinks = array();
        if ($linkIds = $this->getItem()->getOptionByCode('downloadable_link_ids')) {
            $productLinks = $this->getProduct()->getTypeInstance(true)
                ->getLinks($this->getProduct());
            foreach (explode(',', $linkIds->getValue()) as $linkId) {
                if (isset($productLinks[$linkId])) {
                    $itemLinks[] = $productLinks[$linkId];
                }
            }
        }
        return $itemLinks;
    }

    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        if ($this->getProduct()->getLinksTitle()) {
            return $this->getProduct()->getLinksTitle();
        }
        return Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
    }
}