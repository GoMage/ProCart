<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Reports Recently Viewed Products Block
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class GoMage_Procart_Block_Product_Viewed extends Mage_Reports_Block_Product_Viewed
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/procart/product/home_product_viewed.phtml');
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if (Mage::helper('gomage_procart')->isProCartEnable()) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['gpc_prod_id'] = $product->getId();

            $url = parent::getAddToCartUrl($product, $additional);

            if (strpos($url, 'gpc_prod_id') === false) {
                $url = $url . '?gpc_prod_id=' . $product->getId();
                return $url;
            }

        }
        return parent::getAddToCartUrl($product, $additional);
    }
}
