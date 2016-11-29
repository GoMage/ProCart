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
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Block_Product_Configurable_Form extends Mage_Catalog_Block_Product_View{
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/procart/product/configurable/form.phtml');                               
    }     
}