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

class GoMage_Procart_Model_Adminhtml_System_Config_Source_Slidedestination{

    const SIDEBAR_CART = 0;
    const FAST_LINKS_CART = 1;

    public function toOptionArray(){

        $helper = Mage::helper('gomage_procart');

        return array(
            array('value'=>self::SIDEBAR_CART, 'label' => $helper->__('Sidebar Cart')),
            array('value'=>self::FAST_LINKS_CART, 'label' => $helper->__('Fast Links Cart')),
        );

    }

    public function toOptionHash(){

        $helper = Mage::helper('gomage_procart');

        return array(
            self::SIDEBAR_CART => $helper->__('Sidebar Cart'),
            self::FAST_LINKS_CART => $helper->__('Fast Links Cart'),
        );
    }

}