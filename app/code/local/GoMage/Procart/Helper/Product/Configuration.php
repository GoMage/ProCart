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

class GoMage_Procart_Helper_Product_Configuration extends Mage_Catalog_Helper_Product_Configuration    
{

    public function getFormattedOptionValue($optionValue, $params = null)
    {                
        if (!$params) {
            $params = array();
        }        
        if (isset($params['max_length'])) $params['max_length'] = null;
        
        return parent::getFormattedOptionValue($optionValue, $params);
        
    }
    
}
