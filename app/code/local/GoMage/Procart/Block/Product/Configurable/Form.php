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
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Block_Product_Configurable_Form extends Mage_Catalog_Block_Product_View{
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/procart/product/configurable/form.phtml');                               
    }     
}