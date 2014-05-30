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
 * @since        Class available since Release 1.1
 */

class GoMage_Procart_Block_Wishlist_Sidebar extends Mage_Wishlist_Block_Customer_Sidebar
{

    public function getItemRemoveUrl($item)
    {
        if (Mage::helper('gomage_procart')->isProCartEnable()){
            return 'javascript:GomageProcartConfig.deleteWishlistItem(\'' . $this->getUrl(
                'gomageprocart/procart/removewishlistitem',
                array(
                    'item'=>$item->getWishlistItemId()
                )
            ) . '\')';
        }else{
            return parent::getItemRemoveUrl($item);
        }
    }

    public function addPriceBlockTypeBundle($type, $block = '', $template = '')
    {
        $this->_priceBlockTypes[$type] = array(
            'block' => $block,
            'template' => $template
        );

        return $this;
    }
}
