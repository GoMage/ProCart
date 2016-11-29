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
	
class GoMage_Procart_Block_Wishlist_Links extends Mage_Wishlist_Block_Links{

    public function getAParams(){
        return 'class="top-link-wishlist"';
    }
    
}