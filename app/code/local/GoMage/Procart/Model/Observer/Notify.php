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
 * @since        Class available since Release 2.0
 */

class GoMage_Procart_Model_Observer_Notify
{

    public function notify($event)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn() && Mage::getStoreConfig('gomage_notification/notification/enable')) {
            Mage::helper('gomage_procart')->notify();
        }
    }

}