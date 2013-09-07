<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.2
 * @since        Class available since Release 1.1
 */
	
class GoMage_Procart_Block_Wishlist_Links extends Mage_Wishlist_Block_Links{

    public function getAParams(){
        return 'class="top-link-wishlist"';
    }
    
}